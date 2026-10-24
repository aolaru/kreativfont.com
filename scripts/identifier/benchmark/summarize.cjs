// Analyse the exported admin-lab JSON without making any API requests.
const fs = require('node:fs');
const path = require('node:path');
const {summary} = require('../plugin/assets/admin.js');
const report = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
const output = path.resolve(process.argv[3] || path.dirname(process.argv[2]));
fs.mkdirSync(output, {recursive:true});
const models = summary(report); // Recalculate from immutable per-request data, including conventional even-N medians.
fs.writeFileSync(path.join(output,'summary.json'),JSON.stringify(models,null,2)+'\n');
const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const csv = value => '"' + String(value ?? '').replaceAll('"','""') + '"';
const rate = (n,d) => d ? `${n}/${d} (${(100*n/d).toFixed(1)}%)` : 'not measured';
const money = value => value == null ? 'unknown' : '$'+value.toFixed(6);
const rows = report.rows.map(row=>[row.sample.id,row.sample.font || '(no text)',row.sample.variant,row.model,row.outcome.ok,
  row.score.top1,row.score.top3,row.score.abstained,row.outcome.metrics?.elapsed_ms,row.outcome.metrics?.estimated_cost_usd,
  row.outcome.candidates.map(item=>item.name).join(' | '),row.outcome.note]);
fs.writeFileSync(path.join(output,'samples.csv'),[
  ['sample','expected_family','variant','model','success','top1','top3','abstained','milliseconds','estimated_usd','suggestions','note'],...rows,
].map(row=>row.map(csv).join(',')).join('\n')+'\n');
const active = [...new Set(report.rows.map(row=>row.outcome.active_model))];
const sections = models.map(model=>`<tr><td>${escape(model.model)}</td><td>${escape(model.access?.available?'confirmed':`not confirmed (HTTP ${model.access?.http_status ?? '?'})`)}</td><td>${rate(model.top1,model.known_samples)}</td><td>${rate(model.top3,model.known_samples)}</td><td>${model.errors}</td><td>${model.control_abstentions}/${model.controls}</td><td>${model.median_ms == null?'not measured':(model.median_ms/1000).toFixed(2)+'s'}</td><td>${money(model.estimated_usd)}${model.unknown_cost_calls?' + unknown calls':''}</td></tr>`).join('');
const html=`<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Kreativ Font Identifier benchmark</title><style>body{font:16px/1.6 system-ui;max-width:1150px;margin:40px auto;padding:0 24px;color:#18243b}h1{line-height:1.2}table{border-collapse:collapse;width:100%;font-size:14px}td,th{padding:12px;text-align:left;border-bottom:1px solid #d8dfeb}th{background:#eff2fa}.scroll{overflow:auto}.note{padding:18px;background:#f3f5fb;border-radius:12px}pre{white-space:pre-wrap;overflow-wrap:anywhere}</style><h1>Kreativ Font Identifier: model benchmark</h1><p>${escape(report.started_at)} · Plugin ${escape(report.plugin_version)}</p><p class="note"><strong>Live model before: ${escape(report.active_model_before)}. Active model returned by benchmark calls: ${escape(active.join(', ') || 'no calls')}.</strong> This run did not change the live setting. ${report.all_models_accessible?'All selected models passed the access check.':'Some model access checks failed; no accuracy comparison can be claimed for those models.'}</p><p>${escape(report.dataset.limitations || '')}</p><p>Run ${report.completed?'finished for accessible models':'is partial or stopped'}. ${escape(report.error || '')}</p><div class="scroll"><table><thead><tr><th>Model</th><th>Access</th><th>Top 1</th><th>Top 3</th><th>Errors</th><th>No-text abstentions</th><th>Median latency</th><th>Estimated token cost</th></tr></thead><tbody>${sections}</tbody></table></div><h2>How to read this</h2><p>Exact font-family names and declared aliases only; stylistic similarity is not a correct identification. Provider errors count as misses and are not abstentions. Clean and compressed samples of the same font are correlated. No confidence intervals or general commercial-font accuracy are claimed. Costs use standard published token rates, include reported reasoning tokens, and are estimates rather than billed totals. Access failures have no accuracy/cost score.</p><h2>Profiles used</h2><pre>${escape(JSON.stringify(report.profiles,null,2))}</pre><p>See samples.csv for every prediction and the original JSON for checksums, provenance, usage and access results. No visitor uploads were used.</p></html>`;
fs.writeFileSync(path.join(output,'report.html'),html);
console.log(JSON.stringify({report:path.join(output,'report.html'),models,live_models:active,complete:report.completed,all_models_accessible:report.all_models_accessible},null,2));
