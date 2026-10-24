(function () {
  'use strict';
  function normalize(name) { return String(name).normalize('NFKC').toLowerCase().replace(/[^\p{L}\p{N}]/gu, ''); }
  function score(sample, outcome) {
    if (!outcome?.ok) return {top1:false, top3:false, abstained:false, falsePositive:false};
    const names = (outcome.candidates || []).slice(0,3).map(item => normalize(item.name));
    if (sample.kind === 'no_text') return {top1:null, top3:null, abstained:names.length === 0, falsePositive:names.length > 0};
    const accepted = [sample.font, ...(sample.aliases || [])].map(normalize);
    return {top1:accepted.includes(names[0]), top3:names.some(name=>accepted.includes(name)), abstained:names.length === 0, falsePositive:false};
  }
  function summary(report) {
    return report.models.map(model => {
      const rows = report.rows.filter(row => row.model === model);
      const known = rows.filter(row=>row.sample.kind === 'known_font');
      const controls = rows.filter(row=>row.sample.kind === 'no_text');
      const times = rows.map(row=>row.outcome.metrics?.elapsed_ms).filter(Number.isFinite).sort((a,b)=>a-b);
      const costs = rows.map(row=>row.outcome.metrics?.estimated_cost_usd).filter(value=>typeof value === 'number' && Number.isFinite(value));
      return {model, access:report.access[model] || null, attempted:rows.length,
        successes:rows.filter(row=>row.outcome.ok).length, errors:rows.filter(row=>!row.outcome.ok).length,
        known_samples:known.length, top1:known.filter(row=>row.score.top1).length, top3:known.filter(row=>row.score.top3).length,
        controls:controls.length, false_positives:controls.filter(row=>row.score.falsePositive).length,
        control_abstentions:controls.filter(row=>row.outcome.ok && row.score.abstained).length,
        median_ms:times.length ? (times.length%2 ? times[Math.floor(times.length/2)] : (times[times.length/2-1]+times[times.length/2])/2) : null,
        estimated_usd:costs.length ? costs.reduce((a,b)=>a+b,0) : null, unknown_cost_calls:rows.length-costs.length};
    });
  }
  const api = {normalize,score,summary};
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (typeof window === 'undefined') return;
  window.KfiBenchmark = api;
  window.jQuery(function ($) {
    if (typeof GPTFontIdAdmin === 'undefined') return;
    const cfg = GPTFontIdAdmin;
    $('#gpt-test-api').on('click', async function () {
      const button = $(this).prop('disabled',true);
      try {
        const response = await $.ajax({url:cfg.ajaxurl,type:'POST',timeout:15000,data:{action:'gpt_test_api_key',nonce:cfg.accessNonce,model:$('#gpt_font_id_model').val()}});
        $('#gpt-test-result').text(response.success ? 'Saved key can access the selected model. No live setting was changed.' : 'Could not confirm access to this model. No live setting was changed.');
      } catch { $('#gpt-test-result').text('The access check could not complete.'); }
      finally {button.prop('disabled',false);}
    });
    const run = document.querySelector('#kfi-bench-run');
    if (!run) return;
    const stop = document.querySelector('#kfi-bench-stop');
    const exportButton = document.querySelector('#kfi-bench-export');
    const status = document.querySelector('#kfi-bench-status');
    let running = false, stopped = false;
    window.kfiBenchmarkReport = null; // Synthetic test reports only; never a browser API key.
    function display(report) {
      report.summary = summary(report);
      document.querySelector('#kfi-bench-summary').textContent = JSON.stringify(report.summary,null,2);
      exportButton.disabled = false;
    }
    function addRow(row) {
      const tr = document.createElement('tr');
      [row.sample.id,row.model,row.outcome.ok?'completed':row.outcome.note,
        row.sample.kind === 'no_text' ? (row.score.abstained?'abstained':'not abstained') : `${row.score.top1?'yes':'no'} / ${row.score.top3?'yes':'no'}`,
        row.outcome.metrics ? (row.outcome.metrics.elapsed_ms/1000).toFixed(2) : 'unknown',
        row.outcome.metrics?.estimated_cost_usd == null ? 'unknown' : row.outcome.metrics.estimated_cost_usd.toFixed(6),
        (row.outcome.candidates || []).map(item=>item.name).join(', ')].forEach(value=>{const td=document.createElement('td');td.textContent=String(value);tr.appendChild(td);});
      document.querySelector('#kfi-bench-table tbody').appendChild(tr);
    }
    stop.addEventListener('click',()=>{stopped=true;status.textContent='Stopping after the current request; no automatic retries.';});
    exportButton.addEventListener('click',()=>{
      const report = window.kfiBenchmarkReport; if (!report) return;
      const url=URL.createObjectURL(new Blob([JSON.stringify(report,null,2)],{type:'application/json'}));
      const link=document.createElement('a');link.href=url;link.download='kreativ-font-benchmark-'+report.started_at.replace(/[:.]/g,'-')+'.json';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
    });
    run.addEventListener('click',async()=>{
      if (running) return;
      try {
        const manifestFile=document.querySelector('#kfi-bench-manifest').files[0];
        const files=new Map(Array.from(document.querySelector('#kfi-bench-images').files).map(file=>[file.name,file]));
        const models=Array.from(document.querySelectorAll('.kfi-bench-model:checked')).map(input=>input.value);
        if (!manifestFile || !models.length) throw new Error('Choose a sample manifest, its image files and at least one model.');
        const manifest=JSON.parse(await manifestFile.text());
        if (manifest.schema_version !== 1 || !Array.isArray(manifest.samples) || !manifest.samples.length || manifest.samples.length>50) throw new Error('Use a version-1 manifest with 1–50 samples.');
        const ids=new Set();
        for (const sample of manifest.samples) {
          if (!sample.id || ids.has(sample.id) || !['known_font','no_text'].includes(sample.kind) || (sample.kind==='known_font' && typeof sample.font!=='string') || !/^[a-f0-9]{64}$/.test(sample.sha256)) throw new Error('Invalid or duplicate labelled sample.');
          ids.add(sample.id);
          const file=files.get(sample.file); if (!file || file.size>4*1024*1024) throw new Error('Missing or oversized image: '+sample.file);
          const digest=Array.from(new Uint8Array(await crypto.subtle.digest('SHA-256',await file.arrayBuffer()))).map(n=>n.toString(16).padStart(2,'0')).join('');
          if (digest!==sample.sha256) throw new Error('Sample checksum mismatch: '+sample.file);
        }
        running=true;stopped=false;run.disabled=true;stop.disabled=false;
        document.querySelector('#kfi-bench-table tbody').replaceChildren();
        const report={schema_version:1,plugin_version:cfg.version,started_at:new Date().toISOString(),active_model_before:cfg.activeModel,models,profiles:cfg.profiles,dataset:manifest,access:{},rows:[],completed:false};
        window.kfiBenchmarkReport=report;
        for (const model of models) {
          status.textContent='Checking access: '+model;
          const response=await $.ajax({url:cfg.ajaxurl,type:'POST',timeout:15000,data:{action:'gpt_test_api_key',nonce:cfg.accessNonce,model}});
          report.access[model]=response.data;
          display(report);
        }
        const available=models.filter(model=>report.access[model]?.available);
        report.all_models_accessible=available.length===models.length;
        // Sample-major order interleaves models to reduce time/order bias.
        for (const sample of manifest.samples) {
          for (const model of available) {
            if (stopped) break;
            status.textContent=`Running ${report.rows.length+1}/${manifest.samples.length*available.length}: ${sample.id} · ${model}. Live model remains ${cfg.activeModel}.`;
            const body=new FormData();body.append('action','gpt_font_id_benchmark');body.append('nonce',cfg.benchmarkNonce);body.append('model',model);body.append('font_image',files.get(sample.file),'benchmark-sample.'+sample.file.split('.').pop());
            // Ground truth stays in this client. Do not send labels, aliases, or font filenames.
            const response=await $.ajax({url:cfg.ajaxurl,type:'POST',timeout:70000,data:body,contentType:false,processData:false});
            if (!response.success) throw new Error(typeof response.data==='string'?response.data:'Benchmark request failed.');
            const outcome=response.data;
            const row={sample,model,outcome,score:score(sample,outcome)};report.rows.push(row);addRow(row);display(report);
            if (outcome.active_model!==cfg.activeModel) throw new Error('The live model changed outside this benchmark. Stopping.');
            if (outcome.metrics?.provider_status==='incomplete') throw new Error('A request hit its output/reasoning budget. This profile needs adjustment before a fair full comparison.');
            if (!outcome.ok && [401,403,404,429].includes(outcome.metrics?.http_status)) throw new Error('Model access/quota changed during the run. Stopping without retries.');
          }
          if (stopped) break;
        }
        report.completed=!stopped && available.length>0;report.finished_at=new Date().toISOString();display(report);
        status.textContent=(stopped?'Stopped with partial results.':available.length?`Benchmark finished. Completed ${available.length}/${models.length} accessible models.`:'No selected model was accessible; no generation was attempted.')+' Live model was not changed. Export results before closing this tab.';
      } catch (error) {
        status.textContent='Stopped: '+error.message+' No automatic retry or live-model change.';
        if (window.kfiBenchmarkReport) {window.kfiBenchmarkReport.finished_at=new Date().toISOString();window.kfiBenchmarkReport.error=error.message;display(window.kfiBenchmarkReport);}
      } finally {running=false;run.disabled=false;stop.disabled=true;}
    });
    window.addEventListener('beforeunload',event=>{if(running){event.preventDefault();event.returnValue='';}});
  });
})();
