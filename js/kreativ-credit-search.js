(function () {
    'use strict';

    document.querySelectorAll('[data-credit-facet]').forEach(function (field) {
        var input = field.querySelector('[data-credit-facet-search]');
        var select = field.querySelector('select');
        if (!input || !select) return;

        var options = Array.from(select.options);
        input.parentElement.hidden = false;
        input.addEventListener('input', function () {
            var query = input.value.trim().toLocaleLowerCase();
            var selected = select.value;
            var matching = options.filter(function (option) {
                return !option.value || option.value === selected || option.textContent.toLocaleLowerCase().includes(query);
            });
            select.replaceChildren.apply(select, matching);
            select.value = selected;
        });
    });

    document.querySelectorAll('[data-credit-directory]').forEach(function (directory) {
        var input = directory.querySelector('[data-credit-directory-search]');
        var items = Array.from(directory.querySelectorAll('[data-credit-directory-item]'));
        var empty = directory.querySelector('[data-credit-directory-empty]');
        var letters = directory.querySelector('[data-credit-directory-letters]');
        var initial = '';
        if (!input) return;
        input.parentElement.hidden = false;
        if (letters) letters.hidden = false;
        function filter() {
            var query = input.value.trim().toLocaleLowerCase();
            var count = 0;
            items.forEach(function (item) {
                var name = item.textContent.trim().toLocaleLowerCase();
                item.hidden = !name.includes(query) || (initial && !name.startsWith(initial));
                if (!item.hidden) count++;
            });
            if (empty) empty.hidden = count !== 0;
        }
        input.addEventListener('input', filter);
        if (letters) letters.addEventListener('click', function (event) {
            var button = event.target.closest('[data-credit-initial]');
            if (!button || !letters.contains(button)) return;
            initial = button.dataset.creditInitial.toLocaleLowerCase();
            letters.querySelectorAll('[data-credit-initial]').forEach(function (item) {
                item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
            });
            filter();
        });
    });
}());
