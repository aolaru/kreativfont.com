(function () {
    var storageKey = 'kreativ-font-research-board-v1';
    var maxItems = 6;

    function readItems() {
        try {
            var value = window.localStorage.getItem(storageKey);
            var items = value ? JSON.parse(value) : [];

            return Array.isArray(items) ? items.filter(function (item) {
                return item && Number.isInteger(item.id) && item.title && item.url;
            }).slice(0, maxItems) : [];
        } catch (error) {
            return [];
        }
    }

    function writeItems(items) {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(items.slice(0, maxItems)));
        } catch (error) {
            return;
        }
    }

    function createIcon(className) {
        var icon = document.createElement('i');
        icon.className = className;
        icon.setAttribute('aria-hidden', 'true');
        return icon;
    }

    function updateSaveButtons(items) {
        document.querySelectorAll('[data-kreativ-save-font]').forEach(function (button) {
            var item;

            try {
                item = JSON.parse(button.getAttribute('data-kreativ-save-font'));
            } catch (error) {
                return;
            }

            var saved = items.some(function (savedItem) {
                return savedItem.id === item.id;
            });
            var label = button.querySelector('span');
            var icon = button.querySelector('i');

            button.classList.toggle('is-saved', saved);
            button.setAttribute('aria-pressed', saved ? 'true' : 'false');

            if (label) {
                label.textContent = saved ? 'Saved to board' : 'Save to board';
            }

            if (icon) {
                icon.className = saved ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark';
            }
        });
    }

    function renderBoards() {
        var items = readItems();

        document.querySelectorAll('[data-kreativ-research-board]').forEach(function (board) {
            var list = board.querySelector('[data-kreativ-research-list]');
            var empty = board.querySelector('[data-kreativ-research-empty]');
            var clear = board.querySelector('[data-kreativ-research-clear]');
            var compare = board.querySelector('[data-kreativ-research-compare]');

            if (!list || !empty || !clear || !compare) {
                return;
            }

            list.innerHTML = '';

            items.forEach(function (item) {
                var row = document.createElement('li');
                var link = document.createElement('a');
                var details = document.createElement('span');
                var title = document.createElement('strong');
                var facts = document.createElement('small');
                var remove = document.createElement('button');

                link.href = item.url;
                title.textContent = item.title;
                details.appendChild(title);

                if (Array.isArray(item.facts) && item.facts.length) {
                    facts.textContent = item.facts.slice(0, 2).join(' · ');
                    details.appendChild(facts);
                }

                if (item.image) {
                    var image = document.createElement('img');
                    image.src = item.image;
                    image.alt = '';
                    image.width = 56;
                    image.height = 42;
                    image.loading = 'lazy';
                    link.appendChild(image);
                }

                link.appendChild(details);
                remove.type = 'button';
                remove.className = 'kreativ-research-remove';
                remove.setAttribute('aria-label', 'Remove ' + item.title + ' from research board');
                remove.appendChild(createIcon('fa-solid fa-xmark'));
                remove.addEventListener('click', function () {
                    writeItems(readItems().filter(function (savedItem) {
                        return savedItem.id !== item.id;
                    }));
                    renderBoards();
                });
                row.appendChild(link);
                row.appendChild(remove);
                list.appendChild(row);
            });

            empty.hidden = items.length > 0;
            clear.hidden = items.length === 0;
            compare.hidden = items.length < 2;

            if (items.length >= 2) {
                var pairingUrl = new URL(board.getAttribute('data-pairing-url'), window.location.origin);
                pairingUrl.searchParams.set('font_a', items[0].id);
                pairingUrl.searchParams.set('font_b', items[1].id);
                compare.href = pairingUrl.toString();
            }
        });

        updateSaveButtons(items);
    }

    function init() {
        document.querySelectorAll('[data-kreativ-save-font]').forEach(function (button) {
            button.addEventListener('click', function () {
                var item;

                try {
                    item = JSON.parse(button.getAttribute('data-kreativ-save-font'));
                } catch (error) {
                    return;
                }

                var items = readItems();
                var existingIndex = items.findIndex(function (savedItem) {
                    return savedItem.id === item.id;
                });

                if (existingIndex >= 0) {
                    items.splice(existingIndex, 1);
                } else {
                    items.unshift(item);
                }

                writeItems(items);
                renderBoards();
            });
        });

        document.querySelectorAll('[data-kreativ-research-clear]').forEach(function (button) {
            button.addEventListener('click', function () {
                writeItems([]);
                renderBoards();
            });
        });

        renderBoards();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
