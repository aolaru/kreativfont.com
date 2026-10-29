# Tool UI checks and local preview

These fixtures preserve the public naming and pairing plugin markup, CSS, and clients inspected on 29 October 2026. They are test snapshots, not deployed plugin sources. The theme owns the workspace layout and progressive UI enhancements; the installed plugins retain their generation endpoints and settings.

Install dependencies with `npm install` in this directory and install Chromium with `npx playwright install chromium`. Run `npm run preview`, then `npm test` in a second terminal. The preview is at `http://127.0.0.1:8767/tools`, `/naming`, and `/pairing`.

To reuse an existing Node runtime, set `KREATIV_TOOLS_NODE_MODULES` to its `node_modules` directory when starting both the PHP preview and `node scripts/test-tools-ui.cjs`. Set `KREATIV_TOOLS_WEBKIT=1` to add WebKit when its matching browser is installed.

The preview uses sample responses and never calls the live AI endpoints. The checks exercise the original plugin clients, real clipboard round trips in Chromium, real TXT downloads, favorite persistence, closed-panel accessibility, duplicate submission prevention, service and clipboard failure recovery, dark mode, and five responsive widths per page. Evidence is written under `outputs/tools-improvements-2026-10-29/`.

`php scripts/test-tools-layout.php` checks the PHP renderer and catalogue registry without browser dependencies.
