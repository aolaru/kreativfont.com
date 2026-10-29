// Frozen public plugin client, 2026-10-29. Test fixture only.
jQuery(document).ready(function ($) {

    const $input      = $('#kfng-input');
    const $button     = $('#kfng-generate');
    const $status     = $('#kfng-status');
    const $results    = $('#kfng-results');
    const $copyAll    = $('#kfng-copy-all');
    const $exportBtn  = $('#kfng-export');
    const $favoritesC = $('#kfng-favorites');

    let lastResults = [];
    let favorites   = [];

    /* -------------------------------------------------------
       FAVORITES — Load / Save
    ------------------------------------------------------- */
    function loadFavorites() {
        try {
            favorites = JSON.parse(localStorage.getItem('kfng_favorites') || '[]');
        } catch (e) {
            favorites = [];
        }
        renderFavorites();
    }

    function saveFavorites() {
        localStorage.setItem('kfng_favorites', JSON.stringify(favorites));
    }

    function addFavorite(name) {
        if (!favorites.includes(name)) {
            favorites.push(name);
            saveFavorites();
            renderFavorites();
        }
    }

    function removeFavorite(name) {
        favorites = favorites.filter(n => n !== name);
        saveFavorites();
        renderFavorites();
    }

    function renderFavorites() {
        $favoritesC.empty();

        if (!favorites.length) {
            $favoritesC.append('<p class="kfng-favorites-empty">No favorites yet. Click the ★ icon to save names.</p>');
            return;
        }

        const list = $('<ul class="kfng-list kfng-list-favorites"></ul>');

        favorites.forEach(name => {
            const item   = $('<li class="kfng-item"></li>');
            const text   = $('<span class="kfng-name"></span>').text(name);
            const copy   = $('<button type="button" class="kfng-copy">Copy</button>');
            const remove = $('<button type="button" class="kfng-remove-fav">Remove</button>');

            copy.on('click', () => copyToClipboard(name, copy));
            remove.on('click', () => removeFavorite(name));

            item.append(text, copy, remove);
            list.append(item);
        });

        $favoritesC.append(list);
    }

    /* -------------------------------------------------------
       Clipboard with fallback
    ------------------------------------------------------- */
    async function copyToClipboard(text, button) {
        try {
            await navigator.clipboard.writeText(text);
        } catch (e) {
            // iOS / older browser fallback
            const temp = $('<textarea>')
                .val(text)
                .css({ position: 'fixed', top: '-9999px' })
                .appendTo('body');

            temp[0].select();
            document.execCommand('copy');
            temp.remove();
        }

        if (button) {
            const original = button.text();
            button.text('Copied!');
            setTimeout(() => button.text(original), 1500);
        }
    }

    /* -------------------------------------------------------
       Filters
    ------------------------------------------------------- */
    function getSelectedFilters() {
        return $('.kfng-filter:checked')
            .map(function () { return $(this).data('tag'); })
            .get();
    }

    /* -------------------------------------------------------
       Name helpers (pair / variants)
    ------------------------------------------------------- */
    function getBaseAndStyle(name) {
        const parts = name.trim().split(/\s+/);
        if (parts.length <= 1) {
            return { base: name.trim(), style: '' };
        }
        return {
            base: parts.slice(0, -1).join(' '),
            style: parts[parts.length - 1]
        };
    }

    function getPairStyle(style) {
        const s = style.toLowerCase();
        if (s.includes('serif') && !s.includes('sans')) return 'Sans';
        if (s.includes('sans')) return 'Serif';
        if (s.includes('script') || s.includes('hand')) return 'Sans';
        if (s.includes('display')) return 'Text';
        return 'Sans';
    }

    function buildPairName(name) {
        const { base, style } = getBaseAndStyle(name);
        return `${base} ${getPairStyle(style)}`;
    }

    function buildVariants(base) {
        return ['Serif', 'Sans', 'Text', 'Script', 'Display'].map(s => `${base} ${s}`);
    }

    /* -------------------------------------------------------
       Render results
    ------------------------------------------------------- */
    function renderResults(text) {
        $results.empty();
        lastResults = [];

        const lines = text.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
        if (!lines.length) {
            $results.html('<p>No names returned. Try a different description.</p>');
            return;
        }

        lastResults = [...lines];

        const ul = $('<ul class="kfng-list"></ul>');

        lines.forEach(name => {
            const item  = $('<li class="kfng-item"></li>');
            const left  = $('<div class="kfng-item-main"></div>');
            const acts  = $('<div class="kfng-item-actions"></div>');
            const extra = $('<div class="kfng-extra"></div>'); // collapsed

            const nameSpan = $('<span class="kfng-name"></span>').text(name);
            const copyBtn  = $('<button class="kfng-copy">Copy</button>');
            const favBtn   = $('<button class="kfng-fav">☆</button>');
            const pairBtn  = $('<button class="kfng-small kfng-pair">Pair</button>');
            const varBtn   = $('<button class="kfng-small kfng-variants">Variants</button>');

            if (favorites.includes(name)) favBtn.text('★');

            // Copy
            copyBtn.on('click', () => copyToClipboard(name, copyBtn));

            // Favorite toggle
            favBtn.on('click', () => {
                if (favorites.includes(name)) {
                    favBtn.text('☆');
                    removeFavorite(name);
                } else {
                    favBtn.text('★');
                    addFavorite(name);
                }
            });

            // Pair (toggle + animation)
            pairBtn.on('click', () => {
                if (extra.hasClass('kfng-open') && extra.data('mode') === 'pair') {
                    extra.removeClass('kfng-open').data('mode', '');
                    return;
                }

                extra.removeClass('kfng-open').data('mode', 'pair').empty();

                const pairName = buildPairName(name);
                const row      = $('<div class="kfng-extra-row"></div>');
                const label    = $('<span class="kfng-extra-label">Matching pair:</span>');
                const nameSpan = $('<span class="kfng-extra-name"></span>').text(pairName);
                const copyPair = $('<button class="kfng-copy kfng-copy-inline">Copy</button>');

                copyPair.on('click', () => copyToClipboard(pairName, copyPair));

                row.append(label, nameSpan, copyPair);
                extra.append(row);

                // reflow to trigger animation
                void extra[0].offsetWidth;
                extra.addClass('kfng-open');
            });

            // Variants (toggle + animation)
            varBtn.on('click', () => {
                if (extra.hasClass('kfng-open') && extra.data('mode') === 'variants') {
                    extra.removeClass('kfng-open').data('mode', '');
                    return;
                }

                extra.removeClass('kfng-open').data('mode', 'variants').empty();

                const { base } = getBaseAndStyle(name);
                const variants = buildVariants(base);

                const row   = $('<div class="kfng-extra-row kfng-extra-variants"></div>');
                const label = $('<span class="kfng-extra-label">Variants:</span>');
                const list  = $('<div class="kfng-variant-list"></div>');

                variants.forEach(v => {
                    const vItem = $('<div class="kfng-variant-item"></div>');
                    const vTxt  = $('<span class="kfng-extra-name"></span>').text(v);
                    const vCopy = $('<button class="kfng-copy kfng-copy-inline">Copy</button>');
                    const vFav  = $('<button class="kfng-small kfng-fav-inline">★</button>');

                    vCopy.on('click', () => copyToClipboard(v, vCopy));
                    vFav.on('click', () => addFavorite(v));

                    vItem.append(vTxt, vCopy, vFav);
                    list.append(vItem);
                });

                row.append(label, list);
                extra.append(row);

                // reflow
                void extra[0].offsetWidth;
                extra.addClass('kfng-open');
            });

            left.append(nameSpan);
            acts.append(copyBtn, favBtn, pairBtn, varBtn);
            item.append(left, acts, extra);
            ul.append(item);
        });

        $results.append(ul);
    }

    /* -------------------------------------------------------
       Generate (REST)
    ------------------------------------------------------- */
    $button.on('click', async function () {
        const prompt = $input.val().trim();

        if (!prompt) {
            $status.text('Please describe the font style you want first.');
            return;
        }

        const filters = getSelectedFilters();
        const mode    = $('#kfng-name-mode').val();

        let finalPrompt = prompt;

        if (filters.length) {
            finalPrompt += `\n\nImportant visual attributes: ${filters.join(', ')}.\nReflect these qualities naturally in the naming style.`;
        }

        if (mode === 'one') {
            finalPrompt += "\n\nConstraint: ALL names must be exactly ONE word.";
        } else if (mode === 'two') {
            finalPrompt += "\n\nConstraint: ALL names must be exactly TWO words.";
        } else {
            finalPrompt += "\n\nNames can be one or two words, whichever feels best.";
        }

        $status.text('Generating font names…');
        $results.empty();
        $button.prop('disabled', true);

        try {
            const response = await fetch(kfngConfig.restUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': kfngConfig.nonce
                },
                body: JSON.stringify({ prompt: finalPrompt })
            });

            const data = await response.json();

            if (!response.ok || data.error) {
                $status.text(data.error || 'Error generating names.');
                return;
            }

            renderResults(data.result);
            $status.text('Here are your font name ideas:');

        } catch (e) {
            console.error(e);
            $status.text('Unexpected error. Please try again.');
        } finally {
            $button.prop('disabled', false);
        }
    });

    /* -------------------------------------------------------
       Enter = Generate
    ------------------------------------------------------- */
    $input.on('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $button.click();
        }
    });

    /* -------------------------------------------------------
       Quick presets (delegated, so always works)
    ------------------------------------------------------- */
    $(document).on('click', '.kfng-preset', function () {
        const presetText = $(this).data('prompt') || '';
        $input.val(presetText).focus();
    });

    /* -------------------------------------------------------
       Copy all
    ------------------------------------------------------- */
    $copyAll.on('click', () => {
        if (!lastResults.length) {
            $status.text('Nothing to copy.');
            return;
        }
        copyToClipboard(lastResults.join('\n'), $copyAll);
    });

    /* -------------------------------------------------------
       Export .txt
    ------------------------------------------------------- */
    $exportBtn.on('click', () => {
        if (!lastResults.length) {
            $status.text('Nothing to export.');
            return;
        }

        const text = lastResults.join('\n');
        const blob = new Blob([text], { type: 'text/plain' });
        const url  = URL.createObjectURL(blob);

        const a = $('<a>')
            .attr('href', url)
            .attr('download', 'kreativ-font-names.txt')
            .appendTo('body');

        a[0].click();
        a.remove();
        URL.revokeObjectURL(url);
    });

    /* -------------------------------------------------------
       Init
    ------------------------------------------------------- */
    loadFavorites();
});
