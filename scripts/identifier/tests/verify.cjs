const assert = require('node:assert/strict');
const {spawn} = require('node:child_process');
const {once} = require('node:events');
const net = require('node:net');
const path = require('node:path');
const zlib = require('node:zlib');

function png(width=240,height=80) {
  function crc(bytes) { let n=0xffffffff; for(const b of bytes){n^=b;for(let i=0;i<8;i++)n=(n>>>1)^((n&1)?0xedb88320:0);} return (n^0xffffffff)>>>0; }
  function chunk(type,bytes){const tag=Buffer.from(type);const length=Buffer.alloc(4);length.writeUInt32BE(bytes.length);const sum=Buffer.alloc(4);sum.writeUInt32BE(crc(Buffer.concat([tag,bytes])));return Buffer.concat([length,tag,bytes,sum]);}
  const header=Buffer.alloc(13);header.writeUInt32BE(width);header.writeUInt32BE(height,4);header[8]=8;header[9]=2;
  const rows=Buffer.alloc((width*3+1)*height,255);for(let y=0;y<height;y++)rows[y*(width*3+1)]=0;
  return Buffer.concat([Buffer.from([137,80,78,71,13,10,26,10]),chunk('IHDR',header),chunk('IDAT',zlib.deflateSync(rows)),chunk('IEND',Buffer.alloc(0))]);
}

(async()=>{
  const portServer=net.createServer();portServer.listen(0,'127.0.0.1');await once(portServer,'listening');
  const port=portServer.address().port;await new Promise(r=>portServer.close(r));
  const base=`http://127.0.0.1:${port}`;
  const server=spawn('php',['-S',`127.0.0.1:${port}`,path.join(__dirname,'router.php')],{stdio:['ignore','ignore','pipe']});
  let errors=''; server.stderr.on('data',bytes=>{errors+=bytes.toString();});
  const image=png();
  async function submit(fixture='normal',fields={},bytes=image,name='sample.png') {
    const form=new FormData();form.append('action','gpt_font_id_identify');form.append('_ajax_nonce','fixture-nonce');form.append('font_image',new Blob([bytes],{type:'image/png'}),name);
    for(const [key,value] of Object.entries(fields))form.set(key,value);
    return (await fetch(`${base}/?fixture=${fixture}`,{method:'POST',body:form})).json();
  }
  try {
    for(let i=0;i<30;i++){try{await fetch(base);break;}catch{await new Promise(r=>setTimeout(r,100));}}
    const pageHtml=await(await fetch(base)).text();
    const config=JSON.parse(pageHtml.match(/var GPTFontId\s*=\s*(\{.*?\});/s)[1]);
    assert.equal(config.version,'0.8.1');assert.equal(config.publicStorage,false);assert.equal(config.reviewRequiresOptIn,true);
    const unit=await(await fetch(`${base}/unit`)).json();
    assert.deepEqual(unit.rate_sequence,[true,true,true,true,true,false]);
    assert.ok(unit.counter_keys.every(key=>!key.includes('127.0.0.1')));
    assert.equal(unit.publishing,'pending');assert.equal(unit.trashing,'trash');assert.equal(unit.ordinary_post,'publish');
    assert.equal(unit.model_before,'gpt-4o');assert.equal(unit.model_after,'gpt-4o');
    assert.equal(unit.without_ack,'gpt-4o');assert.equal(unit.with_ack,'gpt-6-luna');assert.equal(unit.invalid,'gpt-4o');
    assert.equal(unit.payloads[0].reasoning,null);assert.deepEqual(unit.payloads[1].reasoning,{effort:'low'});assert.deepEqual(unit.payloads[2].reasoning,{effort:'low'});
    assert.equal(unit.profiles['gpt-4o'].estimated_cost_usd,0.005375);
    assert.equal(unit.profiles['gpt-6.1-sol'].estimated_cost_usd,0.00481);
    const ignoredOverride=await submit('normal',{model:'gpt-6-luna'});assert.equal(ignoredOverride._test.calls[0].model,'gpt-4o');
    const configured=await submit('live_sol');assert.equal(configured._test.calls[0].model,'gpt-6.1-sol');
    const badModel=await submit('bad_model');assert.equal(badModel._test.calls[0].model,'gpt-4o');
    const benchFields={action:'gpt_font_id_benchmark',nonce:'fixture-nonce',model:'gpt-6-luna',share_for_review:'yes',review_consent:'yes'};
    const denied=await submit('normal',benchFields);assert.equal(denied.success,false);assert.equal(denied._test.calls.length,0);
    const expiredBench=await submit('admin',{...benchFields,nonce:'expired'});assert.equal(expiredBench.success,false);assert.equal(expiredBench._test.calls.length,0);
    const benchmark=await submit('admin',benchFields);assert.equal(benchmark.success,true);assert.equal(benchmark.data.ok,true);
    assert.equal(benchmark._test.calls[0].model,'gpt-6-luna');assert.equal(benchmark._test.posts.length,0);assert.equal(benchmark._test.metas.length,0);
    assert.equal(benchmark._test.active_model,'gpt-4o');assert.equal(benchmark._test.benchmark_locked,false);
    assert.equal(benchmark.data.metrics.reasoning_tokens,100);assert.ok(benchmark.data.metrics.estimated_cost_usd>0);
    const budgeted=await submit('admin_budget',benchFields);assert.equal(budgeted.success,false);assert.equal(budgeted._test.calls.length,0);assert.equal(budgeted._test.benchmark_locked,false);
    const unsupported=await submit('admin',{...benchFields,model:'unknown'});assert.equal(unsupported.success,false);assert.equal(unsupported._test.calls.length,0);
    const scoring=require('../plugin/assets/admin.js');
    assert.equal(scoring.score({kind:'known_font',font:'Gill Sans',aliases:['Gill Sans MT']},{ok:true,candidates:[{name:'Gill Sans MT'}]}).top1,true);
    assert.equal(scoring.score({kind:'known_font',font:'Arial',aliases:[]},{ok:true,candidates:[{name:'Arial Narrow'}]}).top1,false);
    assert.equal(scoring.score({kind:'no_text'},{ok:true,candidates:[{name:'Arial'}]}).falsePositive,true);
    assert.equal(scoring.score({kind:'no_text'},{ok:false,candidates:[]}).abstained,false);
    const counted=scoring.summary({models:['gpt-4o'],access:{},rows:[
      {model:'gpt-4o',sample:{kind:'known_font'},outcome:{ok:false,metrics:{elapsed_ms:10,estimated_cost_usd:null}},score:{top1:false,top3:false}},
      {model:'gpt-4o',sample:{kind:'no_text'},outcome:{ok:false,metrics:{elapsed_ms:20,estimated_cost_usd:null}},score:{abstained:false,falsePositive:false}},
    ]})[0];
    assert.equal(counted.known_samples,1);assert.equal(counted.top1,0);assert.equal(counted.control_abstentions,0);assert.equal(counted.unknown_cost_calls,2);assert.equal(counted.estimated_usd,null);
    assert.equal(counted.median_ms,15,'Even sample counts use the mean of the two middle timings');
    const legacy=await submit();
    assert.equal(legacy.success,true);assert.equal(legacy.data.post_url,'');assert.equal(typeof legacy.data.html,'string');
    assert.equal(legacy.data.review_submitted,false);assert.equal(legacy._test.posts.length,0);assert.equal(legacy._test.metas.length,0);
    assert.equal(legacy._test.calls[0].temporary_file_deleted,true);assert.equal(legacy._test.calls[0].store,false);
    assert.equal(legacy._test.calls[0].format,'json_schema');assert.equal(legacy.data.candidates.length,2);
    assert.equal(legacy.data.candidates[0].url,'https://kreativfont.com/fonts/georgia');
    assert.equal(legacy._test.calls.length,3);assert.ok(legacy._test.calls.every(call=>call.model==='gpt-4o'&&call.store===false&&call.temporary_file_deleted));
    assert.equal(legacy._test.calls[1].selection,true);assert.equal(legacy._test.calls[1].image_count,1);
    assert.equal(legacy._test.calls[2].comparison,true);assert.equal(legacy._test.calls[2].selection,false);assert.equal(legacy._test.calls[2].image_count,3);
    assert.equal(legacy.data.catalogue.status,'compared');assert.equal(legacy.data.candidates[0].catalogue_status,'listed');
    assert.equal(legacy.data.candidates[0].evidence,'specimen_compared');assert.ok(legacy.data.html.includes('Exact identification is unverified'));
    assert.ok(legacy.data.candidates.every(candidate=>candidate.id===301||candidate.id===302));
    for(const fixture of ['no_catalogue','empty_catalogue','invalid_rows']) {
      const response=await submit(fixture);assert.equal(response.success,true,fixture);assert.equal(response._test.calls.length,1,fixture);
      assert.ok(response.data.candidates.every(candidate=>candidate.catalogue_status==='not_found'),fixture);
      assert.equal(response._test.posts.length,0);
    }
    for(const fixture of ['no_previews','unsafe_previews','comparison_failure','selection_failure']) {
      const response=await submit(fixture);assert.equal(response.success,true,fixture);
      assert.equal(response.data.candidates[0].catalogue_status,'listed',fixture);assert.equal(response.data.candidates[0].evidence,'name_lookup',fixture);
      assert.equal(response.data.candidates[0].url,'https://kreativfont.com/fonts/georgia');
      assert.equal(response._test.calls.length,fixture==='comparison_failure'?3:2);
      assert.ok(!JSON.stringify(response.data).includes('attacker.test'));assert.equal(response._test.posts.length,0);
    }
    const invalidKey=await submit('invalid_key');assert.deepEqual(invalidKey.data.candidates,[]);
    const abstained=await submit('comparison_abstain');assert.deepEqual(abstained.data.candidates,[]);
    const unlisted=await submit('unlisted');assert.equal(unlisted.data.candidates[0].name,'Unknown Serif');
    assert.equal(unlisted.data.candidates[0].catalogue_status,'not_found');assert.equal(unlisted.data.candidates[0].evidence,'ai_only');
    assert.ok(unlisted.data.candidates[0].url.startsWith('https://kreativfont.com/?s='));
    const styleOnly=await submit('style_only');assert.equal(styleOnly._test.calls.length,3);assert.equal(styleOnly.data.candidates[0].name,'Georgia');
    const oneFlag=await submit('normal',{share_for_review:'yes'});assert.equal(oneFlag._test.posts.length,0);
    const review=await submit('normal',{share_for_review:'yes',review_consent:'yes'});
    assert.equal(review.data.review_submitted,true);assert.equal(review.data.post_url,'');assert.equal(review._test.posts.length,1);
    assert.equal(review._test.posts[0].post_type,'kfi_review');assert.equal(review._test.posts[0].post_status,'pending');
    assert.equal(review._test.posts[0].post_author,0);assert.equal(review._test.metas[0].private_image,true);
    assert.equal(review._test.type.public,false);assert.equal(review._test.type.show_in_rest,false);assert.equal(review._test.type.publicly_queryable,false);
    assert.ok(Object.values(review._test.type.capabilities).every(cap=>cap==='manage_options'));
    const off=await submit('review_off',{share_for_review:'yes',review_consent:'yes'});assert.equal(off._test.posts.length,0);
    const failedReview=await submit('review_fail',{share_for_review:'yes',review_consent:'yes'});
    assert.equal(failedReview.success,true);assert.equal(failedReview.data.review_submitted,false);assert.equal(failedReview._test.posts[0].post_status,'trash');
    for(const fixture of ['failure','network','refusal','incomplete','malformed','rate']) {
      const response=await submit(fixture,{share_for_review:'yes',review_consent:'yes'});
      assert.equal(response.success,false,fixture);assert.equal(response._test.posts.length,0,fixture);
      assert.ok(!JSON.stringify(response).includes('secret-'),fixture);
      if(fixture==='rate')assert.equal(response._test.calls.length,0);
    }
    const hostile=await submit('hostile');assert.equal(hostile.success,true);assert.equal(hostile.data.candidates.length,1,'A changed/non-allowlisted catalogue key must be discarded');
    assert.ok(!hostile.data.html.includes('<script>'));assert.ok(!hostile.data.html.includes('<img'));assert.ok(!hostile.data.html.includes('javascript:'));
    const noText=await submit('no_text');assert.deepEqual(noText.data.candidates,[]);
    const invalid=await submit('normal',{},Buffer.from('not a real png'));assert.equal(invalid.success,false);assert.equal(invalid._test.calls.length,0);
    const oversize=await submit('normal',{},Buffer.alloc(4*1024*1024+1));assert.equal(oversize.success,false);assert.equal(oversize._test.calls.length,0);
    const tiny=await submit('normal',{},png(1,1));assert.equal(tiny.success,false);
    const expired=await submit('normal',{_ajax_nonce:'expired'});assert.equal(expired.success,false);assert.equal(expired._test.calls.length,0);
    console.log('PASS: real multipart uploads; temporary cleanup before API; no default storage despite old publish settings; dual-consent admin-only review; extension response contract; schema, safety, failures, limits and file validation.');

    if(process.env.KFI_BROWSER_TESTS==='1') {
      const {chromium}=require(require.resolve('playwright',{paths:[process.env.KFI_NODE_MODULES||path.join(__dirname,'..')]}));
      const jqueryPath=require.resolve('jquery/dist/jquery.min.js',{paths:[path.join(__dirname,'..')]});
      const browser=await chromium.launch({headless:true, channel:process.env.KFI_BROWSER_CHANNEL || 'chrome'});const page=await browser.newPage({viewport:{width:1280,height:1000}});
      const pageErrors=[];page.on('pageerror',e=>pageErrors.push(e.message));
      let requests=0, lastFields='', fixture='normal';
      await page.route('https://kreativfont.com/wp-admin/admin-ajax.php',async route=>{
        requests++; lastFields=route.request().postDataBuffer().toString();
        const response=await fetch(`${base}/?fixture=${fixture}`,{method:'POST',headers:{'Content-Type':route.request().headers()['content-type']},body:route.request().postDataBuffer()});
        await route.fulfill({contentType:'application/json',headers:{'Access-Control-Allow-Origin':'*'},body:await response.text()});
      });
      async function open(){await page.goto(base);await page.addScriptTag({path:jqueryPath});await page.addScriptTag({path:path.join(__dirname,'../plugin/assets/script.js')});await page.waitForFunction(()=>document.querySelector('#kfi-identify').disabled);}
      async function select(){await page.locator('input[name=font_image]').setInputFiles({name:'sample.png',mimeType:'image/png',buffer:image});await page.locator('#gpt-font-preview img').waitFor();await page.waitForFunction(()=>!document.querySelector('input[name=font_image]').disabled);}
      await open();await select();assert.equal(requests,0);assert.equal(await page.locator('#kfi-identify').isDisabled(),true);
      await page.locator('#kfi-analysis-consent').check();await page.locator('#kfi-identify').click();
      await page.locator('#gpt-font-id-result.success').waitFor();assert.equal(requests,1);assert.ok(!lastFields.includes('share_for_review'));
      assert.equal(await page.locator('.kfi-match').count(),2);assert.equal(await page.locator('.kfi-match a').first().getAttribute('href'),'https://kreativfont.com/fonts/georgia');
      assert.ok((await page.locator('.kfi-catalogue-evidence').first().innerText()).includes('specimen compared by AI'));
      assert.equal(await page.locator('.kfi-match a').first().innerText(),'View Georgia in Kreativ →');
      assert.ok((await page.locator('#gpt-font-id-result').innerText()).includes('has not saved'));
      await page.screenshot({path:'/tmp/kreativ-identifier-local-results.png'});
      await page.locator('#kfi-remove').click();await select();await page.locator('#kfi-analysis-consent').check();await page.locator('#kfi-share-review').check();
      await page.locator('#kfi-rotate').click();await page.waitForFunction(()=>document.querySelector('#gpt-font-preview p')?.textContent.includes('rotated'));
      assert.equal(await page.locator('#kfi-analysis-consent').isChecked(),false);assert.equal(await page.locator('#kfi-share-review').isChecked(),false);
      await page.locator('#kfi-analysis-consent').check();await page.locator('#kfi-share-review').check();await page.locator('#kfi-identify').click();
      await page.waitForFunction(()=>document.querySelector('#gpt-font-id-result').textContent.includes('Shared with'));
      assert.ok(lastFields.includes('share_for_review'));assert.ok(lastFields.includes('review_consent'));
      await page.setViewportSize({width:390,height:844});await open();await select();
      assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true);
      await page.screenshot({path:'/tmp/kreativ-identifier-local-mobile.png'});
      await page.setViewportSize({width:1280,height:1000});await open();await select();
      fixture='failure';await page.locator('#kfi-analysis-consent').check();await page.locator('#kfi-identify').click();await page.locator('#gpt-font-id-result.error').waitFor();
      assert.equal(await page.locator('#kfi-identify').isEnabled(),true);
      fixture='admin';await page.goto(base+'/admin?fixture=admin');await page.addScriptTag({path:jqueryPath});await page.addScriptTag({path:path.join(__dirname,'../plugin/assets/admin.js')});
      const crypto=require('node:crypto');
      const manifest={schema_version:1,name:'controlled benchmark test',samples:[{id:'test-1',file:'test.png',kind:'known_font',font:'Georgia',aliases:[],variant:'clean',sha256:crypto.createHash('sha256').update(image).digest('hex')}]};
      await page.locator('#kfi-bench-manifest').setInputFiles({name:'manifest.json',mimeType:'application/json',buffer:Buffer.from(JSON.stringify(manifest))});
      await page.locator('#kfi-bench-images').setInputFiles({name:'test.png',mimeType:'image/png',buffer:image});
      await page.locator('#kfi-bench-run').click();
      await page.waitForFunction(()=>document.querySelector('#kfi-bench-status').textContent.startsWith('Benchmark finished'));
      const bench=await page.evaluate(()=>window.kfiBenchmarkReport);
      assert.equal(bench.rows.length,3);assert.ok(bench.summary.every(row=>row.top1===1&&row.top3===1));
      assert.equal(await page.locator('#gpt_font_id_model').inputValue(),'gpt-4o');
      assert.ok(!lastFields.includes('Georgia'),'Ground truth must not be sent to the model');
      await page.screenshot({path:'/tmp/kreativ-identifier-benchmark-local.png',fullPage:true});
      assert.deepEqual(pageErrors,[]);
      await browser.close();console.log('PASS: browser upload/consent gating; safe results and links; optional review; rotation consent reset; mobile layout; provider failure recovery.');
    }
    assert.ok(!errors.includes('Fatal error'),errors);
  } finally {server.kill();await once(server,'exit');}
})().catch(error=>{console.error(error);process.exitCode=1;});
