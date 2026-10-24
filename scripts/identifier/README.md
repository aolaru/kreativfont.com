# Kreativ Font Identifier 0.8.3

Authoritative source for the existing WordPress plugin installed in
`/htdocs/wp-content/plugins/kreativ-gpt-font-dentifier/` (the historical directory
spelling is intentionally preserved). The theme deployment excludes `scripts/`.
The dedicated plugin workflow backs up the installed plugin and uploads only the
reviewed PHP/frontend/admin assets.
No credentials or saved API key are included in this source.

## Catalogue-assisted identification

For clean, single-line ASCII/Latin crops, the first GPT-4o pass transcribes the
sample, then GD/FreeType renders that text using existing local catalogue font
files under `uploads/kreativ-fonts`. Normalized ink shapes and text proportions
are compared in memory. Only a low shape distance with a clear margin over the
next family, across at least three comparable families and a completed comparison
within seven seconds, can return a single local shape-match candidate. Distances
are retrieval gates, not calibrated confidence scores. This avoids asking a model
to recognize a regular font from a thin-weight marketing image.

No uploaded/generated sample is written to disk. The renderer reads only existing
regular/400 TTF assets within each validated font folder; it never downloads fonts
or follows catalogue paths outside that folder. Decoding is capped at four million
pixels independently of the upload limit. Missing GD/font files, unsupported
scripts, coloured/noisy backgrounds, bad OCR, multiple lines, ambiguous distances
or a time-limited scan fall back to the AI/specimen flow below. Bold/italic styles
and commercial fonts without local files are not covered by the strong local
gate. This is still an uncertain family suggestion, not a license or identity
certificate.

The live website and existing Chrome extensions still use GPT-4o. The first pass
suggests names and controlled style/features. Local WordPress queries retrieve
published, unprotected font posts from Fonts/Free Fonts, excluding tools, blog,
identified reports and non-font categories. Title searches include a distinctive
word for close family names; a style query can retrieve references even when no
name is suggested. Feature labels boost retrieval without excluding the broader
style pool. At most 600 distinct catalogue families enter a constrained name
selection pass; up to six proceed to the specimen comparison. Attachment metadata
is resolved only for these selected references and exact-name fallbacks.
Only canonical same-site permalinks and the site's own public upload specimen
images (including its WordPress image CDN) are accepted. No visitor image or
result is added to an index, transient, log or public upload.

A second request to the same saved model sees the query image and the broad
family-name pool, selecting up to six promising references. This gives real
catalogue families omitted by the initial guesses a chance to be compared. A
third request compares the crop with the selected public catalogue specimens.
Both schemas restrict selections to server-supplied keys;
the server independently rejects unknown/changed keys, ignores model URLs/names,
and attaches canonical catalogue names, IDs, previews and direct font-page links.
Initial suggestions outside the shortlist may remain, explicitly unverified,
with generated search links. Empty results remain empty. A limited retrieval
does not establish absence from the entire catalogue, exact visual identity,
licensing or current pricing. Artwork containing multiple fonts can be ambiguous.
This is specimen-assisted retrieval, not a trained visual index of every font.

If there are no safe specimens, or selection/comparison fails, initial suggestions survive
with exact-name catalogue links where available and a clear comparison-unavailable
notice. Fuzzy retrieval alone never claims visual evidence. Initial/selection/
comparison provider timeouts are 25/15/20 seconds; two extra paid API requests are
possible per visitor upload when local shape matching is inconclusive, with up
to six public reference images. A strong local shape match needs only the first
AI transcription request. All requests use
`store:false`. No default storage, optional dual-consent private review, shortcode,
AJAX fields and the legacy extension HTML response shape are preserved.

The first live smoke run exposed that six references retrieved directly from the
initial guesses missed the known families in three synthetic samples. Version
0.8.1 adds the broad family selection pass before visual comparison. Smoke checks
exercise the complete pipeline and existing MV3 extension against the real site:

```
KFI_LIVE_GENERATION=1 KFI_NODE_MODULES=/absolute/node_modules \
  node tests/live-smoke.cjs /absolute/synthetic-1.png /absolute/synthetic-2.png
```

Optionally set `KFI_EXTENSION_ROOT` to the unpacked existing extension and
`KFI_CHROMIUM_EXECUTABLE` to an extension-capable Chromium binary. This test makes
paid calls through the normal endpoint and rate limits, checks that comparisons
finish, and writes local results/screenshots to ignored `dist/catalogue-smoke`.
It does not assert a general accuracy uplift or create public reports. The
three-sample smoke set includes Caveat, which was absent from the live catalogue
when checked; do not count it as an in-catalogue identification success/failure.
Local independent Pillow-versus-FreeType checks recovered the catalogue Bitter
and Montserrat samples, with Caveat correctly outside that tested font pool.

## Configurable model and benchmark lab

Paired evaluation mode compares the exact original 0.7.0 AI-only prompt/schema
with the frozen 0.8.2 catalogue algorithm, always on GPT-4o. Live matching behaviour,
model settings and extension contracts are unchanged. Up to 100 hash-verified
samples can be compared; pipeline order alternates per image. Ground-truth family,
style, aliases, source and coverage remain in the browser. Only a neutral image
filename, pipeline key and nonce are sent. There is no public paired endpoint.

Per-call telemetry records usage, resolved provider model, failures and estimated
costs for all catalogue requests. Report latency includes retrieval and local
rendering. A separate persistent daily guard reserves a maximum of three calls
and $0.30 for catalogue processing, or one call and $0.05 for the old baseline,
then reconciles known usage. Unknown usage keeps its reservation. Limits are 320
generation calls / $5 estimated; deployment cache clears cannot reset this guard.
No images/results are retained server-side, and no reviews or visitor counters
are created/used by evaluation. Existing model-only benchmarks keep their limits.

Create real public website crops with verified Chrome rendered-font metadata,
then build independent Pillow specimens (including bold/italic), degradations and
no-text controls. Freeze the manifest before any generation; keep ambiguous
ground-truth exclusions and capture failures in its provenance. Font binaries
stay in ignored local artifacts and are never included in the plugin package:

```
node benchmark/capture-real-world.cjs /absolute/dataset
KFI_ITALIC_ONLY=1 node benchmark/capture-real-world.cjs /absolute/dataset
python benchmark/build-catalogue-dataset.py /absolute/dataset
```

Real website crops are distinct from generated images. Scores are family-level,
stratified by declared style, source and independently checked catalogue coverage;
they do not certify exact weight/style identification. Archived 44-sample results
remain a separate historical reference, never pooled into fresh paired scores.

The default and existing live model remain **GPT-4o**. Settings → Font Identifier
now has a fixed allowlist of GPT-4o, GPT-6.1 Sol and GPT-6 Luna. Changing the saved
live model requires a separate confirmation checkbox. Public AJAX fields cannot
override it; no automatic model promotion or fallback is performed. The existing
shortcode, extension nonce/config fields and HTML/JSON response remain compatible.

The administrator-only benchmark lab checks exact model access before generation.
It measures the first AI pass only, using its prompt, high-detail input, response
schema, parser and model profiles. It does not measure the complete catalogue flow.
GPT-4o keeps its 1600-token cap; the two
reasoning candidates use low effort and a 4096-token cap, explicitly recorded in
each result. An incomplete response stops a full run for profile adjustment.
No benchmark changes `gpt_font_id_model`, creates review records, or consumes the
visitor counters. Images go through the same temporary-file removal path.

Benchmark generation is serialized, with a 160-call / $5 estimated UTC-day budget.
A $0.10 reservation is committed before each call; known token costs reconcile it,
and failures with unknown usage keep the reservation. Usage estimates include
reported reasoning output and discounted cached input, using standard rates
verified 2026-10-24. Requests explicitly select standard/default service. Estimates
are not invoices and do not include taxes, cache-write fees or account-specific
terms. Access checks do not generate content. Stop on access/quota changes rather
than silently substitute a different model or retry.

Generate a reproducible synthetic dataset with Pillow on a Mac that has the named
fonts installed (font files themselves are never copied):

```
python benchmark/generate-fixtures.py /absolute/new/output-directory
```

It makes 40 labelled specimens (20 families × clean/compressed) plus four no-glyph
controls, each with a checksum and exact rendering provenance. Import the manifest
and images into the benchmark lab. Labels/aliases stay client-side, never in the
model request. The lab exports per-sample predictions, strict top-1/top-3 family
scores, abstentions, errors, latency, usage, estimated costs and access failures.
Errors count as misses, not successful abstentions. Unavailable models have no
accuracy score. Family variants are correlated; this is a small Latin/system-font
baseline, not proof of general commercial-font or logo identification quality.
Results stay in the admin browser until explicitly exported.

After exporting the JSON, make a local HTML report and per-sample CSV without
any more API calls:

```
node benchmark/summarize.cjs /absolute/export.json /absolute/report-directory
```

The report retains access failures, error-inclusive denominators, unknown usage
and the live-model checks. It does not turn missing measurements into zero cost
or a successful abstention. Keep the manifest/rendered specimens with the export
if you need to audit the scores later.

Sources: official [Sol](https://developers.openai.com/api/docs/models/gpt-6.1-sol),
[Luna](https://developers.openai.com/api/docs/models/gpt-6-luna),
[GPT-4o](https://developers.openai.com/api/docs/models/gpt-4o), and
[reasoning/output budgets](https://developers.openai.com/api/docs/guides/reasoning).

## Privacy and review

- Genuine PHP multipart uploads only; validated using actual file bytes, MIME,
  dimensions and size (4 MB / 25 MP maximum).
- Temporary upload removed before the provider request, also on validation errors.
  Default identifications never use `wp_handle_upload`, Media Library, posts,
  cached response data, image URLs or saved reports.
- OpenAI Responses requests use `store:false`; this is not a claim of zero provider
  retention. No image/content is logged by this plugin. Hosting and provider
  infrastructure have their own policies.
- `share_for_review=yes` AND `review_consent=yes` are required to retain anything.
  Processing consent alone is never interpreted as sharing consent.
- Opt-in creates a non-public `kfi_review` pending record. The image is protected
  database metadata, NOT an uploads file. The type has no public query route,
  rewrite, REST exposure or normal publishing capability. Only `manage_options`
  users can access the review UI or inline preview. Trashing is still supported.
- There is no automatic publication from the review queue and no automatic purge.
  Admins should trash reviews when finished. Existing public reports and uploads
  are not modified or deleted by this upgrade.
- Legacy `gpt_font_id_auto_post` / post-status options are retained for rollback
  but never read by the new identifier.

## API compatibility

Preserved shortcode `[gpt_font_identifier]`, global `GPTFontId.ajaxurl` + `nonce`,
AJAX action `gpt_font_id_identify`, nonce action `gpt_font_id_nonce`, and upload
field `font_image`. Response remains `{success,data:{html,post_url}}` with safe,
paragraph-based result HTML. `post_url` is always empty. Optional structured fields
are additive. Installed Chrome extension 1.0.3 submits no sharing fields, continues
to display results, and now receives the no-storage default automatically.

The provider returns a strict schema, zero to three candidates, visual rationale,
and uncertainty. No model-generated URLs, prices, licenses, confidence percentages
or paid-link recommendations are trusted. Kreativ search links are generated by
the server; they do not imply the named family exists in the local catalogue.

Default limits are 5 analyses per network address per 15-minute UTC bucket and 200
site-wide per UTC day, counting provider failures too. The short-lived per-address
keys are HMAC-derived; raw IPs and image contents are not saved in rate counters.
Site limits are editable in Settings → Font Identifier. A database option mutex
serializes counter updates; stale locks recover after 15 seconds. Upstream host
firewall/rate protection is still recommended for volumetric abuse.

## Tests

`node scripts/identifier/tests/verify.cjs` starts an isolated PHP harness and
exercises real multipart uploads with a mocked provider, never production.

Browser checks additionally need `npm ci --prefix scripts/identifier`, Playwright
available through `KFI_NODE_MODULES`, and installed Chrome (or
`KFI_BROWSER_CHANNEL=chromium`):

```
KFI_BROWSER_TESTS=1 KFI_NODE_MODULES=/absolute/node_modules node scripts/identifier/tests/verify.cjs
```

The original extension package must also pass its MV3 tests. Do not upload
confidential samples for production checks or use fabricated confidence scores.

## Deployment and rollback

Pushes affecting this folder trigger `.github/workflows/deploy-identifier.yml`.
The workflow saves the installed plugin as a private GitHub Actions artifact
`identifier-before-<sha>` (90-day retention) before changing production. It does
not mirror/delete the plugin directory or alter unrelated plugins/settings.
For rollback, restore the snapshot files to the same exact plugin paths; a new
admin.js asset left by an older rollback is inert because the old PHP never loads it.
Do not restore the insecure public-storage behavior without a visitor notice.
Clear WordPress edge/object caches after deployment and check live default and
explicit-review requests. Never store rollback PHP in the public plugin folder.
