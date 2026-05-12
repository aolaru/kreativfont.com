(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggles = document.querySelectorAll('.kreativ-theme-toggle');
        var storageKey = 'kreativ-dark';
        var storageAvailable = true;

        function readStoredPreference() {
            try {
                return window.localStorage.getItem(storageKey) === 'true';
            } catch (error) {
                storageAvailable = false;
                return document.body.classList.contains('dark-mode');
            }
        }

        function writeStoredPreference(value) {
            if (!storageAvailable) {
                return;
            }

            try {
                window.localStorage.setItem(storageKey, value ? 'true' : 'false');
            } catch (error) {
                storageAvailable = false;
            }
        }

        function syncThemeToggleState() {
            var darkEnabled = document.body.classList.contains('dark-mode');

            toggles.forEach(function (toggle) {
                var icon = toggle.querySelector('i');
                toggle.setAttribute('aria-pressed', darkEnabled ? 'true' : 'false');
                toggle.setAttribute('title', darkEnabled ? 'Switch to light mode' : 'Switch to dark mode');
                toggle.setAttribute('aria-label', darkEnabled ? 'Switch to light mode' : 'Switch to dark mode');

                if (icon) {
                    icon.classList.toggle('fa-moon', !darkEnabled);
                    icon.classList.toggle('fa-sun', darkEnabled);
                }
            });
        }

        function setTheme(darkEnabled) {
            document.body.classList.toggle('dark-mode', darkEnabled);
            writeStoredPreference(darkEnabled);
            syncThemeToggleState();
        }

        setTheme(readStoredPreference());

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                setTheme(!document.body.classList.contains('dark-mode'));
            });
        });
    });
})();
