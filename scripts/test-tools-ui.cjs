// Run the original plugin clients with controlled responses and the new theme UI.
const assert = require('node:assert/strict');
const path = require('node:path');
const fs = require('node:fs/promises');
const { chromium, webkit } = require(path.join(process.env.KREATIV_TOOLS_NODE_MODULES || path.join(__dirname, 'tools/node_modules'), '@playwright/test'));
const base = process.env.KREATIV_TOOLS_PREVIEW_URL || 'http://127.0.0.1:8767';
const evidence = path.join(__dirname, '../outputs/tools-improvements-2026-10-29');
const { expect } = require(path.join(process.env.KREATIV_TOOLS_NODE_MODULES || path.join(__dirname, 'tools/node_modules'), '@playwright/test'));

async function run(engine, name, device) {
    const browser = await engine.launch();
    const context = await browser.newContext({ ...device, acceptDownloads: true });
    if (name.startsWith('chromium')) await context.grantPermissions(['clipboard-read', 'clipboard-write']);
    const page = await context.newPage();
    page.setDefaultTimeout(10000);
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const report = { browser: name, checks: [], layouts: [], errors };
    const dir = path.join(evidence, name);
    await fs.mkdir(dir, { recursive: true });
    try {
        // Each width uses the actual PHP partials and original plugin markup/CSS.
        for (const tool of ['tools', 'naming', 'pairing']) {
            for (const width of [320, 390, 768, 1024, 1440]) {
                await page.setViewportSize({ width, height: width < 600 ? 844 : 900 });
                await page.goto(base + '/' + tool);
                await page.evaluate(() => document.fonts.ready);
                await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);
                const layout = await page.evaluate(() => ({
                    width: innerWidth, pageWidth: document.documentElement.scrollWidth,
                    inputBottom: document.querySelector('.kfng-input, .kfm-field input')?.getBoundingClientRect().bottom,
                    smallButtons: [...document.querySelectorAll('.kreativ-creative-tool button')].filter(e => {
                        const r = e.getBoundingClientRect();
                        return r.width > 0 && r.height > 0 && (r.width < 44 || r.height < 43.9);
                    }).map(e => e.textContent),
                }));
                assert.ok(layout.pageWidth <= layout.width, `${tool} overflows at ${width}`);
                assert.deepEqual(layout.smallButtons, [], `${tool} has undersized controls`);
                if (layout.inputBottom) assert.ok(layout.inputBottom < (width < 600 ? 844 : 900), `${tool} input below first screen at ${width}`);
                report.layouts.push({ tool, ...layout });
                if ([390, 1440].includes(width)) await page.screenshot({ path: path.join(dir, tool + '-' + width + '-light.png') });
                await page.getByRole('button', { name: 'Switch to dark mode', exact: true }).click();
                await expect(page.locator('body')).toHaveCSS('background-color', 'rgb(2, 6, 23)');
                const field = page.locator('.kfng-input, .kfm-tab-panel.is-active .kfm-field input').first();
                if (await field.count()) {
                    await expect(field).toHaveCSS('background-color', 'rgb(11, 17, 32)');
                    await expect(field).toHaveCSS('color', 'rgb(241, 245, 251)');
                }
                if ([390, 1440].includes(width)) await page.screenshot({ path: path.join(dir, tool + '-' + width + '-dark.png') });
            }
        }
        report.checks.push('Three pages at five widths; light/dark, first-screen inputs, 44px controls, and no horizontal overflow');

        await page.goto(base + '/tools');
        await expect(page.locator('.kreativ-tool-card')).toHaveCount(4);
        await expect(page.getByText('4 free tools', { exact: true })).toBeVisible();
        await expect(page.getByRole('region', { name: 'Guides and tutorials' })).toBeVisible();
        assert.ok((await page.locator('.kreativ-tool-guides a').getAttribute('href')).includes('/blog/'));
        assert.ok(!(await page.locator('.kreativ-tools-catalogue').innerText()).includes('Android'));
        report.checks.push('Four interactive tools, with the Android tutorial under guides');

        await page.goto(base + '/naming');
        await expect(page.getByRole('button', { name: 'Copy all', exact: true })).toBeDisabled();
        await expect(page.getByRole('button', { name: 'Download .txt', exact: true })).toBeDisabled();
        await page.getByRole('button', { name: 'Luxury Serif', exact: true }).click();
        await expect(page.locator('#kfng-input')).toHaveValue('luxury serif logo font');
        await page.locator('.kreativ-tool-filters summary').click();
        await page.getByRole('checkbox', { name: 'Serif', exact: true }).check();
        await expect(page.locator('.kreativ-tool-filters summary')).toHaveText('Style filters (1 selected)');
        await page.getByRole('combobox', { name: 'Name format:', exact: true }).selectOption('two');
        const pendingNames = page.waitForRequest(request => request.url().endsWith('/_tool-fixture/name'));
        await page.getByRole('button', { name: 'Generate Font Names', exact: true }).click();
        const request = await pendingNames;
        assert.ok(request.postDataJSON().prompt.includes('exactly TWO words'));
        assert.ok(request.postDataJSON().prompt.includes('serif'));
        await expect(page.locator('#kfng-results .kfng-item')).toHaveCount(6);
        await expect(page.getByRole('button', { name: 'Generate Font Names', exact: true })).toBeEnabled();
        await expect(page.getByRole('button', { name: 'Copy all', exact: true })).toBeEnabled();
        await page.getByRole('button', { name: 'Copy Velora Serif', exact: true }).click();
        await expect(page.locator('.kreativ-tool-feedback')).toHaveText('Copied to clipboard.');
        if (name.startsWith('chromium')) assert.equal(await page.evaluate(() => navigator.clipboard.readText()), 'Velora Serif');
        await page.getByRole('button', { name: 'Copy Velora Serif', exact: true }).click();
        await expect(page.getByRole('button', { name: 'Copy Velora Serif', exact: true })).toHaveText('Copy');
        const namesDownload = page.waitForEvent('download');
        await page.getByRole('button', { name: 'Download .txt', exact: true }).click();
        const file = await namesDownload;
        assert.equal(file.suggestedFilename(), 'kreativ-font-names.txt');
        const namesText = await fs.readFile(await file.path(), 'utf8');
        assert.equal(namesText.split('\n').length, 6);
        assert.ok(namesText.includes('Velora Serif'));
        await page.getByRole('button', { name: 'Save Velora Serif to favorites', exact: true }).click();
        await expect(page.getByRole('button', { name: 'Remove Velora Serif from favorites', exact: true })).toHaveAttribute('aria-pressed', 'true');
        await expect(page.locator('#kfng-favorites .kfng-name')).toHaveText(['Velora Serif']);
        const variants = page.getByRole('button', { name: 'Family variants', exact: true }).first();
        await variants.click();
        await expect(variants).toHaveAttribute('aria-expanded', 'true');
        await expect(page.locator('.kfng-extra').first()).toBeVisible();
        await variants.click();
        await expect(page.locator('.kfng-extra').first()).toBeHidden();
        await expect(variants).toHaveAttribute('aria-expanded', 'false');
        await expect(page.locator('.kreativ-tool-history')).not.toHaveAttribute('open', '');
        await page.reload();
        await expect(page.locator('#kfng-favorites .kfng-name')).toHaveText(['Velora Serif']);
        report.checks.push('Naming: presets, optional filters, format request, accessible favorites, persistence, variants, copy and real TXT download');

        await page.route('**/_tool-fixture/name', route => route.fulfill({ status: 503, contentType: 'application/json', body: JSON.stringify({ error: 'Try again shortly.' }) }));
        await page.locator('#kfng-input').fill('Error test');
        await page.getByRole('button', { name: 'Generate Font Names', exact: true }).click();
        await expect(page.locator('#kfng-status')).toHaveText('Try again shortly.');
        await expect(page.getByRole('button', { name: 'Generate Font Names', exact: true })).toBeEnabled();
        await expect(page.getByRole('button', { name: 'Copy all', exact: true })).toBeDisabled();
        await page.evaluate(() => {
            Object.defineProperty(navigator, 'clipboard', { configurable: true, value: { writeText: async () => { throw Error('Denied'); } } });
            document.execCommand = () => false;
        });
        await page.locator('#kfng-favorites .kfng-copy').click();
        await expect(page.locator('.kreativ-tool-feedback')).toHaveText('Could not copy. Select the result and copy it manually.');
        await expect(page.locator('#kfng-favorites .kfng-copy')).toHaveText('Copy');
        report.checks.push('Naming: service failure recovers; empty exports disabled; denied clipboard never claims success');

        await page.goto(base + '/pairing');
        const pairing = page.getByRole('tab', { name: 'Pairing', exact: true });
        const similar = page.getByRole('tab', { name: 'Similar Fonts', exact: true });
        const compare = page.getByRole('tab', { name: 'Compare', exact: true });
        await expect(pairing).toHaveAttribute('aria-selected', 'true');
        await pairing.press('ArrowRight');
        await expect(similar).toBeFocused();
        await expect(similar).toHaveAttribute('aria-selected', 'true');
        await similar.press('End');
        await expect(compare).toBeFocused();
        await expect(page.getByRole('tabpanel', { name: 'Pairing', exact: true })).toBeHidden();
        await expect(page.getByRole('button', { name: 'Copy results', exact: true })).toHaveCount(0);
        const comparePanel = page.getByRole('tabpanel', { name: 'Compare', exact: true });
        await page.getByRole('textbox', { name: 'Font A', exact: true }).fill('Helvetica');
        await page.getByRole('textbox', { name: 'Font B', exact: true }).fill('Arial');
        let requests = 0;
        await page.route('**/_tool-fixture/pairing', async route => {
            requests++;
            await new Promise(resolve => setTimeout(resolve, 300));
            await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ success: true, data: { html: '<h3>Helvetica and Arial</h3><p>A comparison for your project.</p>' } }) });
        });
        await page.getByRole('button', { name: 'Compare fonts', exact: true }).click();
        await expect(page.getByRole('button', { name: 'Compare fonts', exact: true })).toBeDisabled();
        await comparePanel.locator('form').evaluate(form => form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })));
        await expect(comparePanel.locator('.kfm-results')).toContainText('Helvetica and Arial');
        await expect(page.getByRole('button', { name: 'Compare fonts', exact: true })).toBeEnabled();
        assert.equal(requests, 1, 'Duplicate submit should be prevented');
        await page.getByRole('button', { name: 'Copy results', exact: true }).click();
        await expect(comparePanel.locator('.kreativ-tool-feedback')).toHaveText('Copied to clipboard.');
        if (name.startsWith('chromium')) assert.ok((await page.evaluate(() => navigator.clipboard.readText())).includes('Helvetica and Arial'));
        const pairingDownload = page.waitForEvent('download');
        await page.getByRole('button', { name: 'Download .txt', exact: true }).click();
        const pairFile = await pairingDownload;
        assert.equal(pairFile.suggestedFilename(), 'kreativ-font-compare.txt');
        assert.ok((await fs.readFile(await pairFile.path(), 'utf8')).includes('Helvetica and Arial'));
        report.checks.push('Pairing: keyboard tabs and hidden panels, single request while busy, copy and real TXT export');

        await page.unroute('**/_tool-fixture/pairing');
        await page.route('**/_tool-fixture/pairing', route => route.fulfill({ status: 503, contentType: 'application/json', body: JSON.stringify({ success: false, data: { message: 'Service unavailable. Please retry.' } }) }));
        await page.getByRole('button', { name: 'Compare fonts', exact: true }).click();
        await expect(comparePanel.locator('.kfm-status')).toHaveText('Service unavailable. Please retry.');
        await expect(page.getByRole('button', { name: 'Compare fonts', exact: true })).toBeEnabled();
        await expect(comparePanel.locator('form')).toHaveAttribute('aria-busy', 'false');
        await expect(comparePanel.locator('.kfm-results')).toBeEmpty();
        await expect(page.getByRole('button', { name: 'Copy results', exact: true })).toHaveCount(0);
        report.checks.push('Pairing: service failure restores the submit button and clears busy state');
        assert.deepEqual(errors, []);
        return report;
    } finally {
        await fs.writeFile(path.join(dir, 'report.json'), JSON.stringify(report, null, 2));
        await browser.close();
    }
}

(async () => {
    await fs.mkdir(evidence, { recursive: true });
    const reports = [];
    reports.push(await run(chromium, 'chromium-desktop', { viewport: { width: 1440, height: 900 } }));
    reports.push(await run(chromium, 'chromium-mobile', { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true }));
    if (process.env.KREATIV_TOOLS_WEBKIT === '1') reports.push(await run(webkit, 'webkit', { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true }));
    await fs.writeFile(path.join(evidence, 'ui-report.json'), JSON.stringify(reports, null, 2));
    console.log('PASS: ' + reports.length + ' browser configurations; ' + reports.reduce((n, r) => n + r.layouts.length, 0) + ' layout checks; generation, copy, export, favorites, keyboard access, and error recovery.');
})().catch(error => { console.error(error); process.exitCode = 1; });
