(function () {
    'use strict';

    function normal(value) { return value.trim().toLocaleLowerCase(); }

    document.querySelectorAll('[data-credit-admin-parents]').forEach(function (box) {
        var finder = box.querySelector('[data-credit-admin-finder]');
        var input = box.querySelector('[data-credit-admin-search]');
        var results = box.querySelector('[data-credit-admin-results]');
        var checklist = document.getElementById('categorychecklist');
        if (!finder || !input || !results || !checklist) return;

        var entries = [];
        box.dataset.creditAdminParents.split(',').filter(Boolean).forEach(function (id) {
            var parent = document.getElementById('in-category-' + id + '-1');
            if (!parent) return;
            parent.querySelectorAll('ul input[type="checkbox"]').forEach(function (checkbox) {
                var label = checkbox.closest('label');
                if (label) entries.push({ checkbox: checkbox, name: label.textContent.trim() });
            });
        });
        box.dataset.creditAdminAssigned.split(',').filter(Boolean).forEach(function (id) {
            var checkbox = checklist.querySelector('input[type="checkbox"][value="' + CSS.escape(id) + '"]');
            if (!checkbox || entries.some(function (entry) { return entry.checkbox.value === checkbox.value; })) return;
            var label = checkbox.closest('label');
            if (label) entries.push({ checkbox: checkbox, name: label.textContent.trim() });
        });
        if (!entries.length) return;
        finder.hidden = false;

        function render() {
            results.replaceChildren();
            var query = normal(input.value);
            if (!query) return;
            var matches = entries.filter(function (entry) { return normal(entry.name).includes(query); }).slice(0, 15);
            if (!matches.length) {
                results.textContent = 'No matching existing terms.';
                return;
            }
            matches.forEach(function (entry) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'button';
                button.textContent = entry.name + (entry.checkbox.checked ? ' (selected)' : '');
                button.setAttribute('aria-pressed', entry.checkbox.checked ? 'true' : 'false');
                button.addEventListener('click', function () {
                    entry.checkbox.checked = !entry.checkbox.checked;
                    entry.checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                    button.setAttribute('aria-pressed', entry.checkbox.checked ? 'true' : 'false');
                    button.textContent = entry.name + (entry.checkbox.checked ? ' (selected)' : '');
                });
                results.appendChild(button);
            });
        }
        input.addEventListener('input', render);
        checklist.addEventListener('change', function (event) {
            if (entries.some(function (entry) { return entry.checkbox === event.target; })) {
                var status = box.querySelector('select[name="kreativ_credit_status[' + box.dataset.creditAdminBranch + ']"]');
                if (status) status.value = 'unreviewed';
            }
            render();
        });
    });

    var name = document.getElementById('tag-name');
    var parentSelect = document.getElementById('parent');
    if (name && parentSelect) {
        var warning = document.createElement('p');
        warning.setAttribute('role', 'status');
        name.insertAdjacentElement('afterend', warning);
        function checkDuplicate() {
            var selected = parentSelect.selectedOptions[0];
            var branch = selected ? normal(selected.textContent) : '';
            var value = normal(name.value);
            var duplicate = value && (branch === 'designer' || branch === 'foundry') && Array.from(parentSelect.options).some(function (option) {
                return option !== selected && normal(option.textContent) === value;
            });
            warning.textContent = duplicate ? 'A category with this name already exists. Check its branch before creating another.' : '';
        }
        name.addEventListener('input', checkDuplicate);
        parentSelect.addEventListener('change', checkDuplicate);
    }
}());
