(function () {
    'use strict';

    var config = window.kreativSearchSuggest || null;

    if (!config || !config.ajaxUrl) {
        return;
    }

    var searchWrappers = document.querySelectorAll('.kreativ-search');

    searchWrappers.forEach(function (wrapper) {
        var form = wrapper.querySelector('.kreativ-search-form');
        var input = wrapper.querySelector('.kreativ-search-input');
        var panel = wrapper.querySelector('.kreativ-search-suggestions');
        var debounceTimer = null;
        var activeIndex = -1;

        if (!form || !input || !panel) {
            return;
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeRegex(value) {
            return String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function highlightMatch(value, searchValue) {
            var text = String(value || '');
            var terms = String(searchValue || '')
                .trim()
                .split(/\s+/)
                .filter(Boolean)
                .sort(function (a, b) {
                    return b.length - a.length;
                });

            if (!terms.length) {
                return escapeHtml(text);
            }

            var pattern = terms.map(escapeRegex).join('|');

            return escapeHtml(text).replace(new RegExp('(' + pattern + ')', 'ig'), '<mark>$1</mark>');
        }

        function getFocusableItems() {
            return Array.prototype.slice.call(panel.querySelectorAll('.kreativ-search-suggestion-item, .kreativ-search-suggestion-footer'));
        }

        function setActiveItem(nextIndex) {
            var items = getFocusableItems();

            items.forEach(function (item) {
                item.classList.remove('is-active');
            });

            if (!items.length) {
                activeIndex = -1;
                return;
            }

            if (nextIndex < 0) {
                activeIndex = -1;
                return;
            }

            if (nextIndex >= items.length) {
                nextIndex = 0;
            }

            activeIndex = nextIndex;
            items[activeIndex].classList.add('is-active');
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function closePanel() {
            panel.hidden = true;
            panel.innerHTML = '';
            wrapper.classList.remove('has-suggestions-open');
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function openPanel() {
            panel.hidden = false;
            wrapper.classList.add('has-suggestions-open');
            input.setAttribute('aria-expanded', 'true');
        }

        function buildFontItem(item, searchValue) {
            var thumb = item.thumb ? '<span class="kreativ-search-suggestion-thumb"><img src="' + escapeHtml(item.thumb) + '" alt="' + escapeHtml(item.label) + '"></span>' : '';
            var context = item.context ? '<span class="kreativ-search-suggestion-meta">' + highlightMatch(item.context, searchValue) + '</span>' : '';
            var label = '<span class="kreativ-search-suggestion-label">' + highlightMatch(item.label, searchValue) + '</span>';

            return '<a class="kreativ-search-suggestion-item kreativ-search-suggestion-item-font" href="' + escapeHtml(item.url) + '">' +
                thumb +
                '<span class="kreativ-search-suggestion-copy">' + label + context + '</span>' +
                '</a>';
        }

        function buildDefaultItem(item, searchValue) {
            return '<a class="kreativ-search-suggestion-item" href="' + escapeHtml(item.url) + '">' +
                '<span class="kreativ-search-suggestion-label">' + highlightMatch(item.label, searchValue) + '</span>' +
                '</a>';
        }

        function buildGroup(title, items, searchValue, groupKey) {
            if (!items || !items.length) {
                return '';
            }

            var html = '<section class="kreativ-search-suggestion-group">';
            html += '<h3 class="kreativ-search-suggestion-heading">' + title + '</h3>';
            html += '<div class="kreativ-search-suggestion-list">';

            items.forEach(function (item) {
                html += groupKey === 'fonts'
                    ? buildFontItem(item, searchValue)
                    : buildDefaultItem(item, searchValue);
            });

            html += '</div></section>';
            return html;
        }

        function renderSuggestions(groups, searchValue) {
            var parts = [];

            parts.push(buildGroup(config.labels.fonts, groups.fonts || [], searchValue, 'fonts'));
            parts.push(buildGroup(config.labels.designer, groups.designer || [], searchValue, 'designer'));
            parts.push(buildGroup(config.labels.foundry, groups.foundry || [], searchValue, 'foundry'));
            parts.push(buildGroup(config.labels.style, groups.style || [], searchValue, 'style'));
            parts.push(buildGroup(config.labels.mood, groups.mood || [], searchValue, 'mood'));
            parts.push(buildGroup(config.labels.useCase, groups.useCase || [], searchValue, 'useCase'));

            var body = parts.join('');

            if (!body) {
                body = '<div class="kreativ-search-suggestion-empty">' + config.labels.empty + '</div>';
            }

            var viewAllUrl = config.searchResultsUrl + '?s=' + encodeURIComponent(searchValue);
            body += '<a class="kreativ-search-suggestion-footer" href="' + viewAllUrl + '">' + config.labels.viewAll + '</a>';

            panel.innerHTML = body;
            openPanel();
            setActiveItem(-1);
        }

        function fetchSuggestions(searchValue) {
            var params = new URLSearchParams({
                action: 'kreativ_search_suggest',
                nonce: config.nonce,
                q: searchValue
            });

            fetch(config.ajaxUrl + '?' + params.toString(), {
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (payload) {
                    if (!payload || !payload.success || !payload.data) {
                        closePanel();
                        return;
                    }

                    renderSuggestions(payload.data.groups || {}, searchValue);
                })
                .catch(function () {
                    closePanel();
                });
        }

        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-autocomplete', 'list');

        input.addEventListener('input', function () {
            var searchValue = input.value.trim();

            window.clearTimeout(debounceTimer);

            if (searchValue.length < (config.minChars || 2)) {
                closePanel();
                return;
            }

            debounceTimer = window.setTimeout(function () {
                fetchSuggestions(searchValue);
            }, 180);
        });

        input.addEventListener('focus', function () {
            var searchValue = input.value.trim();

            if (searchValue.length >= (config.minChars || 2) && panel.innerHTML.trim() !== '') {
                openPanel();
            }
        });

        input.addEventListener('keydown', function (event) {
            var items = getFocusableItems();

            if (panel.hidden || !items.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setActiveItem(activeIndex + 1);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActiveItem(activeIndex <= 0 ? items.length - 1 : activeIndex - 1);
                return;
            }

            if (event.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
                event.preventDefault();
                window.location.href = items[activeIndex].getAttribute('href');
                return;
            }

            if (event.key === 'Escape') {
                closePanel();
            }
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                closePanel();
            }
        });
    });
})();
