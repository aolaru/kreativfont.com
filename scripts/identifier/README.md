# Kreativ Font Identifier 0.7.0

Authoritative source for the existing WordPress plugin installed in
`/htdocs/wp-content/plugins/kreativ-gpt-font-dentifier/` (the historical directory
spelling is intentionally preserved). The theme deployment excludes `scripts/`.
The dedicated plugin workflow backs up the installed plugin and uploads only the
reviewed PHP/frontend/admin assets.
No credentials or saved API key are included in this source.

## Configurable model and benchmark lab

The default and existing live model remain **GPT-4o**. Settings → Font Identifier
now has a fixed allowlist of GPT-4o, GPT-6.1 Sol and GPT-6 Luna. Changing the saved
live model requires a separate confirmation checkbox. Public AJAX fields cannot
override it; no automatic model promotion or fallback is performed. The existing
shortcode, extension nonce/config fields and HTML/JSON response remain compatible.

The administrator-only benchmark lab checks exact model access before generation.
It uses the same image prompt, high-detail inputs, response schema, parser and
model profiles as the public identifier. GPT-4o keeps its 1600-token cap; the two
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
