// Capture existing public typography. Font labels come from Chrome's rendered-font data.
const fs = require('node:fs/promises');
const path = require('node:path');
const crypto = require('node:crypto');
const {chromium} = require(require.resolve('@playwright/test',{paths:[process.env.KFI_NODE_MODULES || path.join(__dirname,'..')]}));
const targets = [
  ['https://kreativfont.com/fonts/bitter-font-free-download',['h1','h2','strong','em']],
  ['https://developer.mozilla.org/en-US/docs/Web/CSS/font-family',['h1','h2','strong','em']],
  ['https://wordpress.org/',['h1','h2','strong','em']],
  ['https://www.w3.org/',['h1','h2','strong','em']],
  ['https://alistapart.com/',['h1','h2','strong','em']],
  ['https://getbootstrap.com/docs/5.3/content/typography/',['h1','h2','strong','em']],
  ['https://www.smashingmagazine.com/',['h1','h2','strong','em']],
];
(async()=>{
  const italicOnly=process.env.KFI_ITALIC_ONLY==='1';
  const output=path.resolve(process.argv[2]);await fs.mkdir(output,{recursive:true});
  const browser=await chromium.launch({channel:'chrome',headless:true});
  const samples=[],failures=[];
  try {
    for(const [url,defaultSelectors] of targets) {
      const selectors=italicOnly?['em','i']:defaultSelectors;
      const page=await browser.newPage({viewport:{width:1280,height:1000},deviceScaleFactor:1});
      try {
        await page.goto(url,{waitUntil:'domcontentloaded',timeout:25000});
        await page.evaluate(()=>document.fonts.ready);
        const cdp=await page.context().newCDPSession(page);await cdp.send('DOM.enable');await cdp.send('CSS.enable');
        let count=0,wordsFromSource=0;
        for(const selector of selectors) {
          const nodes=page.locator(selector);
          for(let index=0;index<Math.min(await nodes.count(),16);index++) {
            if(count>=2)break;
            const node=nodes.nth(index);
            if(!await node.isVisible())continue;
            const info=await node.evaluate(el=>{const s=getComputedStyle(el);return {text:el.innerText?.trim(),family:s.fontFamily,weight:s.fontWeight,style:s.fontStyle,size:s.fontSize,transform:s.transform,fontSynthesis:s.fontSynthesis};});
            const words=info.text?.split(/\s+/).length||0;
            if(italicOnly&&!['italic','oblique'].includes(info.style))continue;
            if(!words||words>12||wordsFromSource+words>24||info.text.length>120||info.text.length<6)continue;
            // Avoid labels that literally disclose the expected family.
            await node.scrollIntoViewIfNeeded();
            const box=await node.boundingBox();if(!box||box.width<30||box.height<16||box.width>1250||box.height>400)continue;
            const {root}=await cdp.send('DOM.getDocument');
            const {nodeIds}=await cdp.send('DOM.querySelectorAll',{nodeId:root.nodeId,selector});
            const {fonts}=await cdp.send('CSS.getPlatformFontsForNode',{nodeId:nodeIds[index]});
            const relevant=fonts.filter(font=>font.glyphCount>0);
            const families=[...new Set(relevant.map(font=>font.familyName))];
            if(families.length!==1||/icon|awesome|symbol/i.test(families[0]))continue;
            const family=families[0];
            if(info.text.toLowerCase().replace(/[^a-z0-9]/g,'').includes(family.toLowerCase().replace(/[^a-z0-9]/g,'')))continue;
            const bytes=await node.screenshot({type:'png',animations:'disabled'});
            const id=(italicOnly?'italic-web-':'web-')+String(samples.length+1).padStart(2,'0'),file=id+'.png';await fs.writeFile(path.join(output,file),bytes);
            const style=info.style==='italic'||info.style==='oblique'?'italic':Number(info.weight)>=600?'bold':Number(info.weight)===500?'medium':'regular';
            samples.push({id,file,kind:'known_font',font:family,aliases:[],style,variant:'real_website',source_type:'real_website',
              sha256:crypto.createHash('sha256').update(bytes).digest('hex'),width:Math.round(box.width),height:Math.round(box.height),
              provenance:{url,selector,index,captured_at:new Date().toISOString(),rendered_fonts:relevant,computed_style:info,
                label_method:'Chrome CSS.getPlatformFontsForNode; one actual rendered family; style from computed CSS; no DOM style edits'},
            });count++;wordsFromSource+=words;
          }
        }
        if(!count)failures.push({url,error:'No short, visible, single-family text element met capture criteria.'});
      }catch(error){failures.push({url,error:error.message});}
      finally{await page.close();}
      await fs.writeFile(path.join(output,italicOnly?'real-world-italic.json':'real-world.json'),JSON.stringify({samples,failures},null,2));
    }
  }finally{await browser.close();}
  console.log(JSON.stringify({output,samples:samples.map(s=>({id:s.id,font:s.font,style:s.style,url:s.provenance.url})),failures},null,2));
})().catch(e=>{console.error(e);process.exitCode=1;});
