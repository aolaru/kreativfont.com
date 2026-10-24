// Recompute paired scores from raw predictions; never trust exported score/summary fields.
const fs=require('node:fs/promises'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {score}=require('../plugin/assets/admin.js');
const pipelines=['ai_legacy','catalogue_082'];
const labels={ai_legacy:'Original AI-only',catalogue_082:'Catalogue matching'};
const escape=value=>String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
const median=values=>{values=[...values].sort((a,b)=>a-b);return values.length?(values.length%2?values[Math.floor(values.length/2)]:(values[values.length/2-1]+values[values.length/2])/2):null;};
const percent=(n,d)=>d?(100*n/d).toFixed(1)+'%':'—';
function aggregate(rows){
 const known=rows.filter(row=>row.sample.kind==='known_font'),controls=rows.filter(row=>row.sample.kind==='no_text');
 const times=rows.map(row=>row.outcome.metrics?.elapsed_ms).filter(Number.isFinite),costs=rows.map(row=>row.outcome.metrics?.estimated_cost_usd).filter(Number.isFinite);
 return {attempted:rows.length,known:known.length,top1:known.filter(row=>row.score.top1).length,top3:known.filter(row=>row.score.top3).length,
   macro_family_top1:known.length?[...new Set(known.map(row=>row.sample.font))].map(font=>{const group=known.filter(row=>row.sample.font===font);return group.filter(row=>row.score.top1).length/group.length;}).reduce((a,b)=>a+b,0)/new Set(known.map(row=>row.sample.font)).size:null,
   errors:rows.filter(row=>!row.outcome.ok).length,controls:controls.length,control_abstentions:controls.filter(row=>row.outcome.ok&&row.score.abstained).length,
   false_positives:controls.filter(row=>row.score.falsePositive).length,median_ms:median(times),
   estimated_usd:costs.length?costs.reduce((a,b)=>a+b,0):null,unknown_cost_rows:rows.length-costs.length,
   generation_calls:rows.reduce((sum,row)=>sum+(row.outcome.metrics?.call_count??0),0),
   fallback_failures:rows.filter(row=>['comparison_unavailable','selection_unavailable'].includes(row.outcome.catalogue?.status)).length};
}
function pairedStats(pairs){
 const known=pairs.filter(pair=>pair.sample.kind==='known_font');let wins=0,losses=0;
 for(const pair of known){if(pair.catalogue_082.score.top1&&!pair.ai_legacy.score.top1)wins++;if(!pair.catalogue_082.score.top1&&pair.ai_legacy.score.top1)losses++;}
 const families=[...new Set(known.map(pair=>pair.sample.font))],clusters=families.map(font=>known.filter(pair=>pair.sample.font===font));
 let seed=20261024;const random=()=>{seed=(Math.imul(seed,1664525)+1013904223)>>>0;return seed/4294967296;};
 const bootstrap=[];
 for(let run=0;run<2000&&clusters.length;run++){let delta=0,count=0;for(let k=0;k<clusters.length;k++){for(const pair of clusters[Math.floor(random()*clusters.length)]){delta+=Number(pair.catalogue_082.score.top1)-Number(pair.ai_legacy.score.top1);count++;}}bootstrap.push(delta/count);}
 bootstrap.sort((a,b)=>a-b);
 const discordant=wins+losses;let probability=Math.pow(.5,discordant),tail=probability;
 for(let k=0;k<Math.min(wins,losses);k++){probability*=((discordant-k)/(k+1));tail+=probability;}
 return {known_pairs:known.length,wins,losses,ties:known.length-wins-losses,top1_delta:known.length?(wins-losses)/known.length:null,
   family_cluster_bootstrap_95:bootstrap.length?[bootstrap[Math.floor(.025*bootstrap.length)],bootstrap[Math.floor(.975*bootstrap.length)]]:null,
   mcnemar_exact_two_sided:discordant?Math.min(1,2*tail):1};
}
function comparisonTable(groups){return '<table><thead><tr><th>Group</th><th>Images</th><th>AI top 1</th><th>Catalogue top 1</th><th>Change</th><th>AI top 3</th><th>Catalogue top 3</th></tr></thead><tbody>'+groups.map(group=>{
 const a=aggregate(group.rows.filter(row=>row.model==='ai_legacy')),b=aggregate(group.rows.filter(row=>row.model==='catalogue_082'));
 return `<tr><td>${escape(group.label)}</td><td>${a.known}</td><td>${percent(a.top1,a.known)}</td><td>${percent(b.top1,b.known)}</td><td>${a.known?((b.top1-a.top1)*100/a.known).toFixed(1)+' pp':'—'}</td><td>${percent(a.top3,a.known)}</td><td>${percent(b.top3,b.known)}</td></tr>`;
 }).join('')+'</tbody></table>';}
async function main(){
 const [input,outputArg,historical]=process.argv.slice(2),output=path.resolve(outputArg);const report=JSON.parse(await fs.readFile(input,'utf8'));
 assert.equal(report.mode,'paired');assert.equal(report.fixed_model,'gpt-4o');
 const frozenFile=await fs.readFile(path.resolve(output,'../dataset/manifest.json'));
 assert.deepEqual(report.dataset,JSON.parse(frozenFile),'Exported ground truth differs from the frozen manifest');
 let notes=null;try{notes=JSON.parse(await fs.readFile(path.resolve(output,'../evaluation-notes.json'),'utf8'));}catch(error){if(error.code!=='ENOENT')throw error;}
 const ids=new Map(report.dataset.samples.map(sample=>[sample.id,sample]));const seen=new Set();
 const rows=report.rows.map(row=>{
   assert.ok(pipelines.includes(row.model));const sample=ids.get(row.sample.id);assert.ok(sample&&sample.sha256===row.sample.sha256,'Changed sample or label dataset');
   const key=sample.id+':'+row.model;assert.ok(!seen.has(key),'Duplicate prediction would bias scores');seen.add(key);
   assert.equal(row.outcome.active_model,report.active_model_before,'The live model changed during evaluation');
   assert.ok((row.outcome.metrics?.calls||[]).every(call=>call.model==='gpt-4o'));
   return {...row,sample,score:score(sample,row.outcome)};
 });
 const pairs=[];
 for(const sample of report.dataset.samples){const pair={sample};for(const model of pipelines)pair[model]=rows.find(row=>row.sample.id===sample.id&&row.model===model);if(pipelines.every(model=>pair[model]))pairs.push(pair);}
 const pairedRows=pairs.flatMap(pair=>pipelines.map(model=>pair[model]));
 const summaries=Object.fromEntries(pipelines.map(model=>[model,aggregate(pairedRows.filter(row=>row.model===model))]));
 const execution=Object.fromEntries(pipelines.map(model=>[model,aggregate(rows.filter(row=>row.model===model))]));
 const groups=field=>[...new Set(pairedRows.filter(row=>row.sample.kind==='known_font').map(row=>field(row.sample)))].sort().map(label=>({label,rows:pairedRows.filter(row=>field(row.sample)===label)}));
 const coverage=sample=>sample.catalogue_coverage?.listed===true?'Catalogue entry found':sample.catalogue_coverage?.listed===false?'No entry found in bounded lookup':'Coverage unknown';
 const source=sample=>sample.source_type==='real_website'?'Actual website screenshots':sample.variant==='clean'?'Generated clean specimens':'Generated degraded specimens';
 const confident=pairedRows.filter(row=>row.model==='catalogue_082'&&row.outcome.catalogue?.status==='local_shape_match');
 const incorrectLocal=confident.filter(row=>!row.score.top1);
 const stats=pairedStats(pairs);
 const pathGroups=[...new Set(pairs.filter(p=>p.sample.kind==='known_font').map(p=>p.catalogue_082.outcome.catalogue?.status||'unknown'))].sort().map(label=>({label,rows:pairs.filter(p=>(p.catalogue_082.outcome.catalogue?.status||'unknown')===label).flatMap(p=>pipelines.map(model=>p[model]))}));
 let archive=null;
 if(historical){const old=JSON.parse(await fs.readFile(historical,'utf8'));const oldRows=old.rows.filter(row=>row.model==='gpt-4o').map(row=>({...row,score:score(row.sample,row.outcome)}));archive={source:path.resolve(historical),...aggregate(oldRows)};}
 const summary={dataset:report.dataset.name,dataset_sha256:crypto.createHash('sha256').update(JSON.stringify(report.dataset)).digest('hex'),
   frozen_manifest_file_sha256:crypto.createHash('sha256').update(frozenFile).digest('hex'),
   expected_images:report.dataset.samples.length,complete:report.completed&&pairs.length===report.dataset.samples.length,paired_images:pairs.length,
   families:new Set(report.dataset.samples.filter(s=>s.kind==='known_font').map(s=>s.font)).size,started_at:report.started_at,finished_at:report.finished_at,
   sessions:report.sessions||[],
   blocker:notes?.blocker||null,evaluated_families:new Set(pairs.filter(p=>p.sample.font).map(p=>p.sample.font)).size,
   live_model:report.active_model_before,pipelines:summaries,execution,unpaired_outcomes:rows.length-pairedRows.length,paired:stats,local_shape_matches:confident.length,incorrect_local_shape_matches:incorrectLocal.length,
   resolved_models:[...new Set(rows.flatMap(row=>(row.outcome.metrics?.calls||[]).map(call=>call.resolved_model||'unknown')))],
   historical_archive:archive,path_groups:pathGroups.map(g=>({path:g.label,...Object.fromEntries(pipelines.map(model=>[model,aggregate(g.rows.filter(row=>row.model===model))]))})),style_groups:groups(sample=>sample.style).map(g=>({style:g.label,...Object.fromEntries(pipelines.map(model=>[model,aggregate(g.rows.filter(row=>row.model===model))]))}))};
 await fs.mkdir(output,{recursive:true});await fs.writeFile(path.join(output,'summary.json'),JSON.stringify(summary,null,2));
 const csv=[['sample','font','style','source','coverage','pipeline','ok','top1','top3','predictions','path','elapsed_ms','estimated_usd','generation_calls']];
 for(const row of rows)csv.push([row.sample.id,row.sample.font,row.sample.style,source(row.sample),coverage(row.sample),row.model,row.outcome.ok,row.score.top1,row.score.top3,(row.outcome.candidates||[]).map(c=>c.name).join(' | '),row.outcome.catalogue?.status||'ai_only',row.outcome.metrics?.elapsed_ms,row.outcome.metrics?.estimated_cost_usd,row.outcome.metrics?.call_count]);
 await fs.writeFile(path.join(output,'predictions.csv'),csv.map(row=>row.map(value=>'"'+String(value??'').replace(/"/g,'""')+'"').join(',')).join('\n')+'\n');
 const cards=(items,title)=>`<h2>${title}</h2>`+(items.length?items.slice(0,12).map(pair=>`<article><img loading="lazy" src="../dataset/${escape(path.basename(pair.sample.file))}" alt="${escape(pair.sample.id)}"><p><strong>${escape(pair.sample.font)} · ${escape(pair.sample.style)} · ${escape(pair.sample.id)}</strong></p><p>AI: ${escape((pair.ai_legacy.outcome.candidates||[]).map(c=>c.name).join(', ')||'abstained')}<br>Catalogue: ${escape((pair.catalogue_082.outcome.candidates||[]).map(c=>c.name).join(', ')||'abstained')}</p><p>${escape(pair.catalogue_082.outcome.catalogue?.status||'')}</p></article>`).join(''):'<p>None in this run.</p>');
 const a=summaries.ai_legacy,b=summaries.catalogue_082;
 const html=`<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Kreativ catalogue evaluation</title><style>
 body{font:16px/1.6 system-ui,sans-serif;max-width:1150px;margin:36px auto;padding:0 24px;color:#182238;background:#fafbfe}h1{font-size:2.2rem}h2{margin-top:42px}table{border-collapse:collapse;width:100%;background:white;font-size:14px}th,td{text-align:left;padding:11px;border:1px solid #dce2ec}th{background:#eef1fa}article{border:1px solid #dce2ec;border-radius:12px;background:white;padding:18px;margin:16px 0;break-inside:avoid}article img{max-width:100%;max-height:160px;object-fit:contain}code{overflow-wrap:anywhere}.muted{color:#526078}.status{padding:20px;background:#eeedf9;border-radius:12px}a{color:#5143d9}</style>
 <h1>Catalogue matching versus the original AI-only identifier</h1><p class="muted">GPT-4o · ${escape(report.started_at)} · plugin ${escape(report.plugin_version)} · matching algorithm frozen at 0.8.2</p>
 <p class="status"><strong>${summary.complete?'Complete':'PARTIAL'}: ${pairs.length}/${report.dataset.samples.length} paired images, ${summary.evaluated_families}/${summary.families} declared font families evaluated.</strong><br>Top 1: AI ${percent(a.top1,a.known)} → catalogue ${percent(b.top1,b.known)}. Top 3: ${percent(a.top3,a.known)} → ${percent(b.top3,b.known)}.</p>
 ${!summary.complete&&notes?.blocker?`<p><strong>Blocked by exhausted API credits:</strong> ${escape(notes.blocker.code)} / ${escape(notes.blocker.type)}, confirmed ${escape(notes.blocker.confirmed_at)}. This is shared with live website/extension identification. Fund the API account, then resume the saved checkpoint; do not rerun completed trials. ${report.dataset.samples.length-pairs.length} cases still lack complete pairs.</p>`:''}
 <p>Each completed image was run once through each pipeline. Family labels and aliases were declared before inference and stayed client-side. Errors count as misses. No tuning was performed during this evaluation. These are family-level scores stratified by style, not exact bold/italic-face identification.</p>
 ${report.sessions?.length>1?'<p>The run was interrupted by a provider limit and resumed from its checkpoint with two-second pacing. Failed trials were retained, and completed outcomes were not rerun. Matching algorithms and the dataset remained unchanged.</p>':''}
 <h2>Paired result</h2>${comparisonTable([{label:'All known-font images',rows:pairedRows}])}<p>Catalogue-only wins: ${stats.wins}; AI-only wins: ${stats.losses}; ties: ${stats.ties}. Family-cluster bootstrap 95% interval for the top-1 change: ${stats.family_cluster_bootstrap_95?.map(v=>(100*v).toFixed(1)+' pp').join(' to ')}. With this small, correlated sample, the interval describes uncertainty in this run rather than future traffic. Unpaired outcomes excluded from comparative accuracy: ${summary.unpaired_outcomes}.</p>
 <p>Macro average across font families: AI ${a.macro_family_top1===null?'—':(100*a.macro_family_top1).toFixed(1)+'%'}; catalogue ${b.macro_family_top1===null?'—':(100*b.macro_family_top1).toFixed(1)+'%'}.</p>
 <h2>By style</h2>${comparisonTable(groups(sample=>sample.style))}<h2>By image source</h2>${comparisonTable(groups(source))}<h2>By catalogue coverage</h2>${comparisonTable(groups(coverage))}
 <h2>By catalogue execution path (diagnostic)</h2>${comparisonTable(pathGroups)}<p>These subsets are selected by the catalogue algorithm after processing, so their scores do not estimate general traffic accuracy. Both columns use the same images in each subset.</p>
 <h2>Controls and local-match errors</h2><p>No-text controls: AI abstained ${a.control_abstentions}/${a.controls}; catalogue ${b.control_abstentions}/${b.controls}. Errors: AI ${a.errors}; catalogue ${b.errors}. Successful local-shape path: ${confident.length} images, ${incorrectLocal.length} wrong first choices. Catalogue-stage failures falling back to initial guesses: ${b.fallback_failures}.</p>
 <h2>Latency and estimated cost</h2><table><tr><th>Pipeline</th><th>Median full processing</th><th>Generation calls</th><th>Estimated USD</th><th>Unknown-cost outcomes</th></tr>${pipelines.map(model=>{const s=execution[model];return `<tr><td>${labels[model]}</td><td>${s.median_ms===null?'—':(s.median_ms/1000).toFixed(2)+' s'}</td><td>${s.generation_calls}</td><td>${s.estimated_usd===null?'unknown':'$'+s.estimated_usd.toFixed(4)}</td><td>${s.unknown_cost_rows}</td></tr>`;}).join('')}</table><p>All provider calls, retrieval and local rendering are included in new timings, including unpaired outcomes when a run is partial. Costs are token estimates, not invoices. Unknown usage is never treated as free. Resolved provider snapshots: ${escape(summary.resolved_models.join(', '))}.</p>
 ${archive?`<h2>Historical AI-only reference (separate dataset)</h2><p>The earlier 44-image run had ${archive.known} known-font images: top 1 ${percent(archive.top1,archive.known)}, top 3 ${percent(archive.top3,archive.known)}, ${archive.control_abstentions}/${archive.controls} control abstentions. Its 20 installed families and two-line inputs differ from this set; its percentages are not pooled into the paired comparison. Its latency measured the API call only.</p>`:''}
 ${cards(pairs.filter(p=>p.sample.kind==='known_font'&&p.catalogue_082.score.top1&&!p.ai_legacy.score.top1),'Examples improved by catalogue matching')}
 ${cards(pairs.filter(p=>p.sample.kind==='known_font'&&!p.catalogue_082.score.top1&&p.ai_legacy.score.top1),'Regressions to investigate')}
 ${cards(pairs.filter(p=>p.catalogue_082.outcome.catalogue?.status==='local_shape_match'&&!p.catalogue_082.score.top1),'Incorrect local shape matches')}
 <h2>Limits and provenance</h2><p>${escape(report.dataset.limitations)}</p><p>Real-world images are existing website text crops, including browser-rendered italic/bold. Generated compression/rotation cases are synthetic; no camera photographs, physical signage or scanned print are represented. Ambiguous font mappings were excluded before inference and preserved in the manifest. Catalogue absence means no entry found in a bounded title lookup; it is not proof of absence. The live catalogue was queried during this run, rather than a frozen database snapshot. Font binaries are not included in this report.</p>
 <p>Dataset embedded in exported results: <code>${summary.dataset_sha256}</code>. <a href="../results.json">Raw results and dataset</a> · <a href="predictions.csv">Per-image CSV</a> · <a href="summary.json">Summary JSON</a>.</p></html>`;
 await fs.writeFile(path.join(output,'report.html'),html);console.log(JSON.stringify(summary,null,2));
}
if(require.main===module)main().catch(error=>{console.error(error);process.exitCode=1;});
module.exports={aggregate,pairedStats};
