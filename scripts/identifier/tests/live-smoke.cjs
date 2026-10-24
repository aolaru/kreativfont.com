// Explicit paid smoke test against the public tool. Never runs in CI.
const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const path = require('node:path');
const os = require('node:os');
const {chromium} = require(require.resolve('@playwright/test', {paths:[process.env.KFI_NODE_MODULES || path.join(__dirname,'..')]}));

(async () => {
  assert.equal(process.env.KFI_LIVE_GENERATION, '1', 'Set KFI_LIVE_GENERATION=1 to authorize paid live analysis of synthetic samples.');
  const files = process.argv.slice(2).map(file => path.resolve(file));
  assert.ok(files.length > 0 && files.length <= 3, 'Provide one to three non-sensitive synthetic image files.');
  const output = process.env.KFI_OUTPUT || path.join(__dirname, '../dist/catalogue-smoke');
  await fs.mkdir(output, {recursive:true});
  const browser = await chromium.launch({headless:true, channel:'chrome'});
  const results = [];
  try {
    const page = await browser.newPage({viewport:{width:1280,height:1000}});
    for (const [index, file] of files.entries()) {
      await page.goto('https://kreativfont.com/tools/kreativ-font-identifier?catalogue-check=' + Date.now());
      const config = JSON.parse((await page.content()).match(/var GPTFontId\s*=\s*(\{.*?\});/s)[1]);
      assert.equal(config.version, '0.8.3'); assert.equal(config.publicStorage, false); assert.equal(config.reviewRequiresOptIn, true);
      await page.locator('input[name=font_image]').setInputFiles(file);
      await page.locator('#gpt-font-preview img').waitFor();
      await page.locator('#kfi-analysis-consent').check();
      const started = Date.now();
      const pending = page.waitForResponse(response => response.url().includes('admin-ajax.php') && response.request().method() === 'POST', {timeout:70000});
      await page.locator('#kfi-identify').click();
      const payload = await (await pending).json();
      assert.equal(payload.success, true, JSON.stringify(payload.data));
      assert.equal(payload.data.post_url, ''); assert.equal(payload.data.review_submitted, false);
      assert.ok(payload.data.notice.includes('has not saved'));
      assert.ok(['compared','local_shape_match'].includes(payload.data.catalogue.status), 'The live catalogue comparison must actually finish.');
      assert.ok(payload.data.catalogue.compared > 0 && payload.data.catalogue.compared <= (payload.data.catalogue.status === 'local_shape_match' ? 600 : 6));
      for (const candidate of payload.data.candidates) {
        assert.equal(new URL(candidate.url).hostname, 'kreativfont.com');
        if (candidate.catalogue_status === 'listed') { assert.ok(candidate.id > 0); assert.ok(!new URL(candidate.url).searchParams.has('s')); }
      }
      await page.locator('#gpt-font-id-result.success').waitFor();
      await page.locator('#gpt-font-id-result').scrollIntoViewIfNeeded();
      await page.screenshot({path:path.join(output, `website-${index + 1}.png`),fullPage:true});
      results.push({client:'website',file:path.basename(file),elapsed_ms:Date.now()-started,data:payload.data});
    }
  } finally { await browser.close(); }
  if (process.env.KFI_EXTENSION_ROOT) {
    const root = path.resolve(process.env.KFI_EXTENSION_ROOT);
    const profile = await fs.mkdtemp(path.join(os.tmpdir(),'kfi-catalogue-live-'));
    const context = await chromium.launchPersistentContext(profile, {headless:true, channel:'chromium',
      executablePath:process.env.KFI_CHROMIUM_EXECUTABLE, viewport:{width:1280,height:1000},
      args:[`--disable-extensions-except=${root}`,`--load-extension=${root}`]});
    try {
      const worker = context.serviceWorkers()[0] || await context.waitForEvent('serviceworker');
      const page = context.pages()[0] || await context.newPage();
      await page.goto(`chrome-extension://${worker.url().split('/')[2]}/popup.html`);
      await page.locator('#image-input').setInputFiles(files[0]);
      await page.locator('#publish-consent').check();
      const pending = page.waitForResponse(response => response.url().includes('admin-ajax.php') && response.request().method() === 'POST', {timeout:70000});
      await page.locator('#identify-button').click();
      const payload = await (await pending).json();
      assert.equal(payload.success,true,JSON.stringify(payload.data));
      assert.ok(['compared','local_shape_match'].includes(payload.data.catalogue.status));
      assert.equal(payload.data.post_url,''); assert.equal(payload.data.review_submitted,false);
      await page.locator('#result-section:not([hidden])').waitFor();
      assert.ok(/catalogue specimens|catalogue font files/.test(await page.locator('#result-content').innerText()));
      assert.ok((await page.locator('#result-content').innerText()).includes('has not saved'));
      assert.equal(await page.locator('#report-link').isVisible(),false);
      await page.screenshot({path:path.join(output,'extension.png')});
      const manifest = JSON.parse(await fs.readFile(path.join(root,'manifest.json'),'utf8'));
      results.push({client:'extension',version:manifest.version,file:path.basename(files[0]),data:payload.data});
    } finally { await context.close(); await fs.rm(profile,{recursive:true,force:true}); }
  }
  await fs.writeFile(path.join(output,'results.json'),JSON.stringify({tested_at:new Date().toISOString(),results},null,2));
  console.log(JSON.stringify(results.map(row=>({client:row.client,version:row.version,file:row.file,catalogue:row.data.catalogue,candidates:row.data.candidates.map(candidate=>({name:candidate.name,id:candidate.id,status:candidate.catalogue_status,evidence:candidate.evidence}))})),null,2));
})().catch(error=>{console.error(error);process.exitCode=1;});
