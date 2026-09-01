(function () {
    'use strict';

    function initialiseNavigation() {
        var toggle = document.querySelector('[data-toggle="offcanvas"]');
        var panel = document.getElementById('primary-navigation-panel');
        var header = document.querySelector('.kreativ-header');

        if (!toggle || !panel) {
            return;
        }

        function isMobileNavigation() {
            return window.matchMedia('(max-width: 991.98px)').matches;
        }

        function setMenuState(isOpen, returnFocus) {
            if (!isMobileNavigation()) {
                panel.classList.remove('open');
                panel.setAttribute('aria-hidden', 'false');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Open navigation');
                toggle.textContent = 'Menu';
                document.body.classList.remove('kreativ-nav-open');
                return;
            }

            panel.classList.toggle('open', isOpen);
            panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
            toggle.textContent = isOpen ? 'Close' : 'Menu';
            document.body.classList.toggle('kreativ-nav-open', isOpen);

            if (isOpen) {
                window.setTimeout(function () {
                    var searchInput = panel.querySelector('.kreativ-search-input');

                    if (searchInput) {
                        searchInput.focus();
                    }
                }, 50);
            } else if (returnFocus) {
                toggle.focus();
            }
        }

        function updateHeaderShadow() {
            if (header) {
                header.classList.toggle('header-shadow', window.scrollY >= 57);
            }
        }

        setMenuState(false, false);
        updateHeaderShadow();

        toggle.addEventListener('click', function () {
            setMenuState(!panel.classList.contains('open'), false);
        });

        panel.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                setMenuState(false, false);
            });
        });

        document.addEventListener('keydown', function (event) {
            if ('Escape' === event.key && panel.classList.contains('open')) {
                setMenuState(false, true);
            }
        });

        window.addEventListener('resize', function () {
            setMenuState(false, false);
        });
        window.addEventListener('scroll', updateHeaderShadow, { passive: true });
    }

    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', initialiseNavigation, { once: true });
    } else {
        initialiseNavigation();
    }
})();
