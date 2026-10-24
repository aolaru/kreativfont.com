const fs=require('node:fs/promises'),os=require('node:os'),path=require('node:path'),assert=require('node:assert/strict'),{execFileSync}=require('node:child_process');
(async()=>{
 const root=await fs.mkdtemp(path.join(os.tmpdir(),'kfi-paired-report-'));
 try{
  await fs.mkdir(path.join(root,'dataset'));
  const samples=[{id:'a',font:'Georgia',kind:'known_font',style:'regular'},{id:'b',font:'Bitter',kind:'known_font',style:'bold'},{id:'c',font:null,kind:'no_text',style:'none'},{id:'d',font:'Futura',kind:'known_font',style:'medium'}].map(s=>({...s,file:s.id+'.png',sha256:s.id.repeat(64),aliases:[],source_type:'synthetic',variant:'clean'}));
  const dataset={schema_version:1,name:'controlled report test',samples};
  await fs.writeFile(path.join(root,'dataset/manifest.json'),JSON.stringify(dataset));
  const rows=[];
  function row(index,model,ok,names,cost=.01){rows.push({sample:samples[index],model,outcome:{ok,active_model:'gpt-4o',candidates:names.map(name=>({name})),metrics:{elapsed_ms:100,estimated_cost_usd:cost,call_count:1,calls:[{model:'gpt-4o',resolved_model:'gpt-4o-fixture'}]}}});}
  row(0,'ai_legacy',true,['Arial']);row(0,'catalogue_082',true,['Georgia']);
  row(1,'ai_legacy',true,['Bitter']);row(1,'catalogue_082',false,[],null);
  row(2,'ai_legacy',true,[]);row(2,'catalogue_082',true,[]);
  row(3,'ai_legacy',true,['Futura']); // Unpaired success must not inflate comparative accuracy.
  const report={mode:'paired',fixed_model:'gpt-4o',active_model_before:'gpt-4o',dataset,rows,completed:false};
  const input=path.join(root,'results.json'),output=path.join(root,'report');
  await fs.writeFile(input,JSON.stringify(report));
  execFileSync(process.execPath,[path.join(__dirname,'../benchmark/compare-report.cjs'),input,output],{stdio:'pipe'});
  const summary=JSON.parse(await fs.readFile(path.join(output,'summary.json'),'utf8'));
  assert.equal(summary.complete,false);assert.equal(summary.paired_images,3);assert.equal(summary.unpaired_outcomes,1);
  assert.equal(summary.pipelines.ai_legacy.known,2);assert.equal(summary.pipelines.ai_legacy.top1,1);
  assert.equal(summary.pipelines.catalogue_082.top1,1);assert.equal(summary.pipelines.catalogue_082.errors,1);
  assert.equal(summary.pipelines.catalogue_082.control_abstentions,1);assert.equal(summary.pipelines.catalogue_082.unknown_cost_rows,1);
  assert.equal(summary.paired.wins,1);assert.equal(summary.paired.losses,1);assert.equal(summary.paired.top1_delta,0);
  assert.equal(summary.execution.ai_legacy.known,3);assert.equal(summary.execution.ai_legacy.top1,2);
  report.dataset.samples[0].font='Changed label';await fs.writeFile(input,JSON.stringify(report));
  assert.throws(()=>execFileSync(process.execPath,[path.join(__dirname,'../benchmark/compare-report.cjs'),input,output],{stdio:'pipe'}));
  console.log('PASS: paired accuracy excludes unpaired outcomes; failures count as misses; controls, cost uncertainty and frozen-label validation are retained.');
 }finally{await fs.rm(root,{recursive:true,force:true});}
})().catch(error=>{console.error(error);process.exitCode=1;});
