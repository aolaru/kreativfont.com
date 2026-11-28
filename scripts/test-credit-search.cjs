const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const assert = require('assert');

(async () => {
    const browser = await chromium.launch({ headless: true, ...(process.env.CHROME_PATH ? { executablePath: process.env.CHROME_PATH } : {}) });
    const page = await browser.newPage();
    try {
        await page.setContent(`
            <div data-credit-facet><label hidden>Search<input data-credit-facet-search></label>
                <select><option value="">Any designer</option><option value="ada">Ada</option><option value="zoe" selected>Zoe</option></select>
            </div>
            <section data-credit-directory><label hidden>Search<input data-credit-directory-search></label><div data-credit-directory-letters hidden><button data-credit-initial="">All</button><button data-credit-initial="A">A</button></div>
                <p data-credit-directory-empty hidden>No matching names.</p>
                <ul><li data-credit-directory-item>Ada</li><li data-credit-directory-item>Zoe</li></ul>
            </section>`);
        await page.addScriptTag({ content: fs.readFileSync(path.join(__dirname, '../js/kreativ-credit-search.js'), 'utf8') });
        await page.locator('[data-credit-facet-search]').fill('ada');
        assert.deepStrictEqual(await page.locator('select option').allTextContents(), ['Any designer', 'Ada', 'Zoe']);
        assert.strictEqual(await page.locator('select').inputValue(), 'zoe', 'Filtering must preserve the selected value');
        await page.locator('[data-credit-facet-search]').fill('');
        assert.strictEqual(await page.locator('select option').count(), 3, 'Clearing search restores all choices');
        await page.locator('[data-credit-directory-search]').fill('missing');
        assert.strictEqual(await page.locator('[data-credit-directory-item]:visible').count(), 0);
        assert.strictEqual(await page.locator('[data-credit-directory-empty]').isVisible(), true);
        await page.locator('[data-credit-directory-search]').fill('');
        await page.locator('[data-credit-initial="A"]').click();
        assert.deepStrictEqual(await page.locator('[data-credit-directory-item]:visible').allTextContents(), ['Ada']);

        await page.setContent(`
            <ul id="categorychecklist"><li id="in-category-1-1"><label><input type="checkbox" value="1">Designer</label>
                <ul><li><label><input type="checkbox" value="10">Ada</label></li></ul></li>
                <li><label><input type="checkbox" value="11" checked>Zoe</label></li></ul>
            <fieldset data-credit-admin-branch="designer" data-credit-admin-parents="1" data-credit-admin-assigned="11"><div data-credit-admin-finder hidden>
                <input data-credit-admin-search><div data-credit-admin-results></div></div><select name="kreativ_credit_status[designer]"><option value="unreviewed">Unreviewed</option><option value="verified" selected>Verified</option></select></fieldset>`);
        await page.addScriptTag({ content: fs.readFileSync(path.join(__dirname, '../js/kreativ-credit-admin.js'), 'utf8') });
        await page.locator('[data-credit-admin-search]').fill('zoe');
        assert.strictEqual(await page.getByRole('button', { name: 'Zoe (selected)' }).count(), 1, 'Promoted checked terms remain searchable');
        await page.locator('[data-credit-admin-search]').fill('ada');
        await page.getByRole('button', { name: 'Ada', exact: true }).click();
        assert.strictEqual(await page.locator('input[value="10"]').isChecked(), true, 'Finder updates the native category checkbox');
        assert.strictEqual(await page.locator('select[name="kreativ_credit_status[designer]"]').inputValue(), 'unreviewed', 'Changing a credit resets review status');
        console.log('PASS: archive search and editor finder preserve native category selection.');
    } finally {
        await browser.close();
    }
})().catch((error) => { console.error(error); process.exitCode = 1; });
