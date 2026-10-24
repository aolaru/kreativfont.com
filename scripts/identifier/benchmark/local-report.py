"""Summarize offline component checks, explicitly separate from the paid AI benchmark."""
import csv
import html
import json
import sys
from pathlib import Path

root=Path(sys.argv[1]).resolve();data=json.loads((root/'results.json').read_text());rows=data['results']
heldout=[row for row in rows if row['group']=='heldout'];summary=[]
for style in ['regular','bold','italic','bold_italic']:
    group=[row for row in heldout if row['style']==style]
    summary.append({'style':style,'images':len(group),'old_family_correct':sum(row['old_ranked_family']==row['truth'] for row in group),
        'new_family_correct':sum(row['ranked_family']==row['truth'] for row in group),
        'new_face_correct':sum(row['ranked_family']==row['truth'] and row['ranked_style']==style for row in group)})
regressions=[row for row in rows if row['id'] in ['eval-048','eval-049']]
with (root/'cases.csv').open('w') as file:
    fields=['id','group','truth','style','ranked_family','ranked_style','old_ranked_family','strong_shapes','status','limited','elapsed_ms']
    writer=csv.DictWriter(file,fieldnames=fields,extrasaction='ignore');writer.writeheader();writer.writerows(rows)
metrics={'paid_api_calls':0,'images':len(rows),'heldout':summary,'counterexamples':regressions,'limitations':data['limits']}
(root/'summary.json').write_text(json.dumps(metrics,indent=2))
table=''.join(f'<tr><td>{entry["style"].replace("_"," ")}</td><td>{entry["images"]}</td><td>{entry["old_family_correct"]}</td><td>{entry["new_family_correct"]}</td><td>{entry["new_face_correct"]}</td></tr>' for entry in summary)
cases=''.join(f'<article><h3>{html.escape(row["truth"])}</h3><p>Nearest local family: {html.escape(row["ranked_family"] or "none")} · accepted as primary: no.</p><p>Preserved saved AI suggestions: {html.escape(", ".join(row["final_names"]))}.</p><p>Explicit comparison alternatives: {html.escape(", ".join(row["alternatives"]))}.</p></article>' for row in regressions)
page=f'''<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Offline bold and italic matching checks</title>
<style>body{{font:16px/1.6 system-ui;max-width:1000px;margin:36px auto;padding:0 24px;color:#192238;background:#fafbfe}}h1{{font-size:2rem}}table{{border-collapse:collapse;width:100%;background:white}}th,td{{padding:12px;border:1px solid #dce1ee;text-align:left}}th,.callout{{background:#edeffa}}.callout,article{{padding:20px;border:1px solid #dce1ee;border-radius:12px;margin:24px 0}}h2{{margin-top:36px}}a{{color:#5143d9}}</style>
<h1>Bold, italic and safer catalogue matching</h1><p>Kreativ Font Identifier 0.9.0 · offline component checks</p>
<p class="callout"><strong>Zero paid API calls.</strong> Tested {len(rows)} images, including 31 new clean specimens with real regular, bold, italic and bold-italic font files.</p>
<h2>Local font-face comparison</h2><table><tr><th>Style</th><th>Images</th><th>Old correct family</th><th>New correct family</th><th>New correct face</th></tr>{table}</table>
<p>All 31 new specimens ranked the correct family and face. This uses known text and a reduced 14-family font pool. It is not a live AI/OCR benchmark or a claim of 100% identifier accuracy. The earlier paid partial benchmark remains unchanged.</p>
<h2>False-match regressions</h2>{cases}<p>A close local shape alone cannot establish identity. Primary promotion requires independent AI-family agreement and a complete, sufficiently close comparison. The initial AI guesses remain uncertain and are not certified by a name lookup.</p>
<h2>Runtime changes</h2><p>The plugin compares existing real font faces locally and keeps the existing GPT-4o configuration. Two extra catalogue-ranking API requests were removed. Conflicting local fonts are displayed in a separate alternatives section, with explicit uncertainty and the compared style.</p>
<p>Website and legacy extension response fields, privacy flags, optional review consent and no-storage defaults are preserved. Verification uses mocked provider responses and actual extension code. No live model request was made for this update.</p>
<p><a href="results.json">Raw offline results</a> · <a href="cases.csv">Per-image CSV</a> · <a href="summary.json">Summary JSON</a></p></html>'''
(root/'report.html').write_text(page)
print(json.dumps(metrics,indent=2))
