// Run the existing MV3 extension checks with this plugin's actual multi-section HTML.
// All site requests remain intercepted by the extension verifier. No paid calls.
const fs=require('node:fs/promises'),path=require('node:path'),os=require('node:os'),assert=require('node:assert/strict'),{execFileSync}=require('node:child_process');
(async()=>{
 const checker=process.env.KFI_EXTENSION_CHECKER;assert.ok(checker&&process.env.KFI_EXTENSION_ROOT,'Set KFI_EXTENSION_CHECKER and KFI_EXTENSION_ROOT to the existing verifier and unpacked extension.');
 const raw=JSON.parse(execFileSync('php',['-r','$_SERVER["REQUEST_URI"]="/unit"; $_SERVER["REQUEST_METHOD"]="GET"; require $argv[1];',path.join(__dirname,'router.php')],{encoding:'utf8'}));
 const html=raw.alternative_html+'<p>Kreativ Font has not saved your image or report.</p><script>window.untrustedRan=true</script>';
 let source=await fs.readFile(checker,'utf8');
 const pattern=/html:'<h3>Possible font matches<\/h3>.*?<\/script>',/;
 assert.ok(pattern.test(source),'The upstream fixture changed; review the adapter before running it.');
 source=source.replace(pattern,'html:'+JSON.stringify(html)+',').replaceAll("page.locator('.font-result-link')","page.locator('.font-result-link').first()");
 source=source.replace("await expect(page.locator('#result-content')).toContainText('Georgia');","await expect(page.locator('#result-content')).toContainText('Georgia');\n    await expect(page.locator('#result-content')).toContainText('Similar catalogue alternative, not an identification.');\n    await expect(page.locator('.font-result-link')).toHaveCount(2);\n    await expect(page.locator('.font-result-link').nth(1)).toHaveAttribute('href','https://kreativfont.com/?s=Gelasio');");
 const temporary=await fs.mkdtemp(path.join(os.tmpdir(),'kfi-extension-contract-'));
 try{const file=path.join(temporary,'check.cjs');await fs.writeFile(file,source);execFileSync(process.execPath,[file],{env:process.env,stdio:'inherit'});}
 finally{await fs.rm(temporary,{recursive:true,force:true});}
})().catch(error=>{console.error(error);process.exitCode=1;});
