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
    const unit=await(await fetch(`${base}/unit`)).json();
    assert.deepEqual(unit.rate_sequence,[true,true,true,true,true,false]);
    assert.ok(unit.counter_keys.every(key=>!key.includes('127.0.0.1')));
    assert.equal(unit.publishing,'pending');assert.equal(unit.trashing,'trash');assert.equal(unit.ordinary_post,'publish');
    const legacy=await submit();
    assert.equal(legacy.success,true);assert.equal(legacy.data.post_url,'');assert.equal(typeof legacy.data.html,'string');
    assert.equal(legacy.data.review_submitted,false);assert.equal(legacy._test.posts.length,0);assert.equal(legacy._test.metas.length,0);
    assert.equal(legacy._test.calls[0].temporary_file_deleted,true);assert.equal(legacy._test.calls[0].store,false);
    assert.equal(legacy._test.calls[0].format,'json_schema');assert.equal(legacy.data.candidates.length,2);
    assert.ok(legacy.data.candidates[0].url.startsWith('https://kreativfont.com/?s='));
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
    const hostile=await submit('hostile');assert.equal(hostile.success,true);assert.equal(hostile.data.candidates.length,2);
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
      assert.equal(await page.locator('.kfi-match').count(),2);assert.equal(await page.locator('.kfi-match a').first().getAttribute('href'),'https://kreativfont.com/?s=Georgia');
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
      assert.deepEqual(pageErrors,[]);
      await browser.close();console.log('PASS: browser upload/consent gating; safe results and links; optional review; rotation consent reset; mobile layout; provider failure recovery.');
    }
    assert.ok(!errors.includes('Fatal error'),errors);
  } finally {server.kill();await once(server,'exit');}
})().catch(error=>{console.error(error);process.exitCode=1;});
