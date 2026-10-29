(function () {
    'use strict';
    var copyLabels = new WeakMap();

    function setText(element, value) {
        if (element.textContent !== value) element.textContent = value;
    }

    function feedback(container, text, error) {
        setText(container, text);
        container.classList.toggle('is-error', Boolean(error));
    }

    function makeFeedback() {
        var status = document.createElement('p');
        status.className = 'kreativ-tool-feedback';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        return status;
    }

    async function copyText(text, button, status) {
        if (!text.trim()) return;
        if (!copyLabels.has(button)) copyLabels.set(button, button.textContent);
        var copied = false;
        try {
            await navigator.clipboard.writeText(text);
            copied = true;
        } catch (error) {
            var focused = document.activeElement;
            var field = document.createElement('textarea');
            field.value = text;
            field.readOnly = true;
            field.style.cssText = 'position:fixed;left:-9999px;top:0';
            document.body.appendChild(field);
            try {
                field.select();
                copied = document.execCommand('copy') === true;
            } catch (fallbackError) {
                copied = false;
            } finally {
                field.remove();
                if (focused && focused.focus) focused.focus({ preventScroll: true });
            }
        }
        if (!copied) {
            feedback(status, 'Could not copy. Select the result and copy it manually.', true);
            return;
        }
        feedback(status, 'Copied to clipboard.');
        var label = copyLabels.get(button);
        setText(button, 'Copied!');
        window.setTimeout(function () { if (button.isConnected) setText(button, label); }, 1500);
    }

    function downloadText(text, filename) {
        var url = URL.createObjectURL(new Blob([text], { type: 'text/plain;charset=utf-8' }));
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
    }

    function enhancePairing(wrapper) {
        var tabs = Array.from(wrapper.querySelectorAll('.kfm-tab'));
        var panels = Array.from(wrapper.querySelectorAll('.kfm-tab-panel'));
        var tablist = wrapper.querySelector('.kfm-tabs');
        if (!tablist || !tabs.length) return;
        tablist.setAttribute('aria-label', 'Choose a font task');

        function syncTabs() {
            tabs.forEach(function (tab, index) {
                var panel = panels.find(function (item) { return item.dataset.kfmPanel === tab.dataset.kfmTab; });
                if (!panel) return;
                var active = tab.classList.contains('is-active');
                tab.id = 'kreativ-font-task-' + index;
                panel.id = 'kreativ-font-task-panel-' + index;
                tab.setAttribute('role', 'tab');
                tab.setAttribute('aria-controls', panel.id);
                tab.setAttribute('aria-selected', String(active));
                tab.tabIndex = active ? 0 : -1;
                panel.setAttribute('role', 'tabpanel');
                panel.setAttribute('aria-labelledby', tab.id);
                panel.hidden = !active;
            });
        }
        syncTabs();
        new MutationObserver(syncTabs).observe(tablist, { subtree: true, attributes: true, attributeFilter: ['class'] });
        tablist.addEventListener('keydown', function (event) {
            var tab = event.target.closest('.kfm-tab');
            if (!tab || event.altKey || event.ctrlKey || event.metaKey) return;
            var index = tabs.indexOf(tab);
            var next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (next === undefined) return;
            event.preventDefault();
            tabs[next].click();
            syncTabs();
            tabs[next].focus();
        });

        panels.forEach(function (panel) {
            var results = panel.querySelector('.kfm-results');
            var form = panel.querySelector('.kfm-form');
            if (!results || !form) return;
            var submit = form.querySelector('button[type="submit"]');
            var toolbar = document.createElement('div');
            toolbar.className = 'kreativ-tool-result-actions';
            var copy = document.createElement('button');
            copy.type = 'button';
            copy.textContent = 'Copy results';
            var download = document.createElement('button');
            download.type = 'button';
            download.textContent = 'Download .txt';
            toolbar.append(copy, download);
            var message = makeFeedback();
            results.after(toolbar, message);
            var status = form.querySelector('.kfm-status');
            if (status) status.setAttribute('role', 'status');
            results.setAttribute('role', 'region');
            results.setAttribute('aria-label', 'Font suggestions');

            function syncResults() {
                var busy = results.classList.contains('kfm-results--loading');
                var empty = !results.textContent.trim();
                form.setAttribute('aria-busy', String(busy));
                if (submit) submit.disabled = busy;
                toolbar.hidden = empty;
                copy.disabled = busy;
                download.disabled = busy;
                if (busy) feedback(message, '');
                if (!busy && !empty && status && !status.textContent && !status.classList.contains('kfm-status-error')) {
                    setText(status, 'Your font suggestions are ready.');
                }
            }
            syncResults();
            new MutationObserver(syncResults).observe(results, {
                childList: true, subtree: true, characterData: true,
                attributes: true, attributeFilter: ['class']
            });
            form.addEventListener('submit', function (event) {
                if (results.classList.contains('kfm-results--loading')) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    return;
                }
                results.replaceChildren();
                feedback(message, '');
            }, true);
            copy.addEventListener('click', function () { copyText(results.innerText, copy, message); });
            download.addEventListener('click', function () {
                downloadText(results.innerText, 'kreativ-font-' + panel.dataset.kfmPanel + '.txt');
                feedback(message, 'Text download started.');
            });
        });
    }

    function enhanceNaming(wrapper) {
        var results = wrapper.querySelector('#kfng-results');
        var copyAll = wrapper.querySelector('#kfng-copy-all');
        var exportButton = wrapper.querySelector('#kfng-export');
        if (!results) return;
        var filters = wrapper.querySelector('.kfng-filters');
        var input = wrapper.querySelector('#kfng-input');
        if (filters && input) {
            var filterDetails = document.createElement('details');
            filterDetails.className = 'kreativ-tool-filters';
            var filterSummary = document.createElement('summary');
            filterSummary.textContent = 'Style filters (optional)';
            input.after(filterDetails);
            filterDetails.append(filterSummary, filters);
            filters.addEventListener('change', function () {
                var count = filters.querySelectorAll('input:checked').length;
                setText(filterSummary, count ? 'Style filters (' + count + ' selected)' : 'Style filters (optional)');
            });
        }
        var status = wrapper.querySelector('#kfng-status');
        if (status) {
            status.setAttribute('role', 'status');
            status.setAttribute('aria-live', 'polite');
        }
        var message = makeFeedback();
        results.before(message);

        function names() {
            return Array.from(results.querySelectorAll('.kfng-item-main .kfng-name')).map(function (name) { return name.textContent.trim(); });
        }
        function rowName(button) {
            var row = button.closest('.kfng-variant-item') || button.closest('.kfng-extra-row') || button.closest('.kfng-item');
            var name = row && row.querySelector(button.classList.contains('kfng-copy-inline') || button.classList.contains('kfng-fav-inline') ? '.kfng-extra-name' : '.kfng-name');
            return name ? name.textContent.trim() : '';
        }
        function syncNames() {
            var empty = names().length === 0;
            if (copyAll) copyAll.disabled = empty;
            if (exportButton) exportButton.disabled = empty;
            var favorites = Array.from(wrapper.querySelectorAll('#kfng-favorites .kfng-name')).map(function (name) { return name.textContent.trim(); });
            wrapper.querySelectorAll('.kfng-fav, .kfng-fav-inline').forEach(function (button) {
                var name = rowName(button);
                var saved = favorites.includes(name);
                button.type = 'button';
                button.setAttribute('aria-label', (saved && button.classList.contains('kfng-fav') ? 'Remove ' : 'Save ') + name + (saved && button.classList.contains('kfng-fav') ? ' from favorites' : ' to favorites'));
                button.setAttribute('aria-pressed', String(saved));
            });
            wrapper.querySelectorAll('.kfng-copy').forEach(function (button) {
                button.type = 'button';
                button.setAttribute('aria-label', 'Copy ' + rowName(button));
            });
            wrapper.querySelectorAll('.kfng-pair, .kfng-variants').forEach(function (button) {
                var row = button.closest('.kfng-item');
                var extra = row && row.querySelector('.kfng-extra');
                var isPair = button.classList.contains('kfng-pair');
                setText(button, isPair ? 'Family pair' : 'Family variants');
                button.title = isPair ? 'Suggest a companion name for the same type family' : 'Explore names for styles in the same type family';
                if (extra) {
                    var active = extra.classList.contains('kfng-open');
                    var expected = isPair ? 'Matching pair:' : 'Variants:';
                    button.setAttribute('aria-expanded', String(active && extra.textContent.includes(expected)));
                    extra.hidden = !active;
                }
            });
        }
        syncNames();
        new MutationObserver(syncNames).observe(wrapper, {
            childList: true, subtree: true, characterData: true,
            attributes: true, attributeFilter: ['class']
        });
        // Use truthful success feedback even if both clipboard mechanisms fail.
        wrapper.addEventListener('click', function (event) {
            var button = event.target.closest('.kfng-copy, #kfng-copy-all');
            if (!button || !wrapper.contains(button)) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            copyText(button === copyAll ? names().join('\n') : rowName(button), button, message);
        }, true);

        // Keep the existing release history available without making it the intro.
        var next = wrapper.nextElementSibling;
        if (next && /^Version\s+\d/.test(next.textContent.trim())) {
            var history = document.createElement('details');
            history.className = 'kreativ-tool-history';
            var summary = document.createElement('summary');
            summary.textContent = 'Release history';
            history.appendChild(summary);
            wrapper.after(history);
            while (next && /^Version\s+\d/.test(next.textContent.trim())) {
                var following = next.nextElementSibling;
                history.appendChild(next);
                next = following;
            }
        }
    }

    function init() {
        var page = document.querySelector('.kreativ-creative-tool');
        if (!page || page.dataset.enhanced) return;
        page.dataset.enhanced = 'true';
        var pairing = page.querySelector('.kfm-wrapper');
        var naming = page.querySelector('.kfng-wrapper');
        if (pairing) enhancePairing(pairing);
        if (naming) enhanceNaming(naming);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
