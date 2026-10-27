# Kreativ Font Identifier 0.9.1

Authoritative source for the existing WordPress plugin installed in
`/htdocs/wp-content/plugins/kreativ-gpt-font-dentifier/` (the historical directory
spelling is intentionally preserved). The theme deployment excludes `scripts/`.
The dedicated plugin workflow backs up the installed plugin and uploads only the
reviewed PHP/frontend/admin assets.
No credentials or saved API key are included in this source.

## Catalogue-assisted identification

The website and existing Chrome extensions use the saved model configuration,
shortcode, nonce, upload fields, privacy flags and legacy HTML response contract.

Version 0.9.1 adds `gpt-6.1-terra` for the requested live-model switch. Its profile
uses low reasoning effort, a 4096-token output cap, high-detail images and the
existing strict JSON schema with `store:false`. Standard rates are $1 input,
$0.05 cached input and $5 output per million tokens, verified in the official
[Terra documentation](https://developers.openai.com/api/docs/models/gpt-6.1-terra)
on 27 October 2026. This configuration change has no font-accuracy benchmark or
paid API validation; it makes no claim of improved identification accuracy.
Saved model settings must be changed explicitly; deployments do not overwrite
an administrator's choice. Historical GPT-4o evaluation profiles remain pinned.

Each visitor analysis now makes one existing AI transcription/suggestion request.
Local WordPress queries retrieve up to 600 published, unprotected font families.
GD/FreeType compares their actual regular, bold, italic and bold-italic TTF files
in memory. It never synthesizes weight/slant, downloads fonts, stores generated
samples, or follows filesystem paths outside a validated family folder. Existing
aliases regular/400, 700/bold, italic/400italic and 700italic/bolditalic are supported.
Missing faces are skipped rather than silently replaced by regular.

Small renders shortlist twelve distinct families; every available face of those
families receives a higher-resolution comparison. The acceptance margin is between
families, not sibling faces. Decoding is capped at four million pixels. A six-second
shortlist/seven-second total comparison limit protects visitor latency. Partial
scans cannot promote a match. Sparse text, unsupported scripts, noisy backgrounds,
bad OCR and ambiguous comparisons retain the initial AI suggestions.

A nearest local font cannot establish identity when the actual font is absent.
A candidate is promoted only when a strict shape/aspect check agrees with an
existing AI possible-match family. Exact family/explicit face suffixes are accepted
for corroboration; fuzzy title matches and distinct families such as Arial Narrow
versus Arial do not qualify. An AI-labelled alternative never becomes an identity.

Conflicting nearest fonts are returned separately as `catalogue_alternatives`,
always labelled similar alternatives, with the compared file style. All original
AI suggestions retain their order unless a corroborated candidate is promoted.
This prevents Georgia → Gelasio and Times New Roman → Jomolhari substitutions from
being presented as identifications. It does not guarantee all font IDs are correct.

The previous two extra AI selection/specimen-ranking requests are removed. Catalogue
gaps cannot erase plausible initial suggestions or force an invented match. Direct
font-page links and preview images come only from public same-site catalogue rows,
not model-provided URLs. Legacy extensions still receive paragraphs in the form
"name — reason", with a separate alternatives heading. No default image/report
storage and optional explicit private review remain unchanged.

## Offline checks (no paid API calls)

`benchmark/build-offline-fixtures.py` prepares existing cached font assets and
independent Pillow specimens. `benchmark/local-eval.php` throws if a model request
is attempted. It replays saved images with their known transcription, and uses
saved AI-only outputs only for a separate policy check. The font pool is reduced;
these are component tests, not a new end-to-end identifier accuracy score.

The 0.9.0 local check covered 101 images: 70 saved cases plus 31 new specimens.
All 31 held-out clean specimens ranked the correct family and face (12 regular,
8 bold, 8 italic, 3 bold italic). Both saved false-match cases were rejected as
primary catalogue matches and retained only as explicitly labelled alternatives.
No paid calls were made. The historical partial report/data remain unchanged.

## Configurable model and benchmark lab

Paired evaluation mode compares the original 0.7.0 AI-only prompt/schema with the
versioned 0.9.0 catalogue flow, always on GPT-4o. Historic 0.8.2 checkpoints are
rejected before generation; they cannot be mixed into this algorithm. Existing
reports still support both pipeline versions. Model settings and extension
contracts remain unchanged. Up to 100 hash-verified
samples can be compared; pipeline order alternates per image. Ground-truth family,
style, aliases, source and coverage remain in the browser. Only a neutral image
filename, pipeline key and nonce are sent. There is no public paired endpoint.

Per-call telemetry records usage, resolved provider model, failures and estimated
costs for all catalogue requests. Report latency includes retrieval and local
rendering. A separate persistent daily guard reserves one call and $0.05 for either pipeline,
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

An interrupted paired evaluation can import its exported checkpoint alongside the
identical manifest/images. Profiles, model, pipelines and the complete dataset
must match. Existing outcomes, including failed trials, are retained and skipped;
no completed image is rerun. Resumed requests are paced two seconds apart. Safe
provider error categories can distinguish throttling from insufficient quota;
raw provider messages, keys and images are never recorded in telemetry.

Generate the local HTML/CSV/JSON comparison report (including the archived run as
a separate reference) with:

```
node benchmark/compare-report.cjs /absolute/results.json /absolute/report-directory /absolute/old-results.json
```

The frozen manifest must be at `../dataset/manifest.json` relative to the report
directory. Comparative scores use complete pairs only, with errors as misses;
cost/error totals also retain any unpaired outcomes. Family-cluster bootstrap
intervals account for repeated variants, without claiming representative traffic.

Installations without a saved model still default to **GPT-4o**. Settings → Font Identifier
has a fixed allowlist of GPT-4o, GPT-6.1 Sol, GPT-6 Luna and GPT-6.1 Terra. Changing the saved
live model requires a separate confirmation checkbox. Public AJAX fields cannot
override it; no automatic model promotion or fallback is performed. The existing
shortcode, extension nonce/config fields and HTML/JSON response remain compatible.

The administrator-only benchmark lab checks exact model access before generation.
It measures the first AI pass only, using its prompt, high-detail input, response
schema, parser and model profiles. It does not measure the complete catalogue flow.
GPT-4o keeps its 1600-token cap; the three
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
