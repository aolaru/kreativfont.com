(function () {
    'use strict';

    if (navigator.webdriver || /headless|phantomjs/i.test(navigator.userAgent || '')) {
        return;
    }

    var config = window.kreativAnalyticsConfig || {};

    function track(eventName, parameters) {
        if (typeof window.gtag !== 'function') {
            return;
        }

        window.gtag('event', eventName, parameters || {});
    }

    function destinationType(link) {
        if (!link || !link.href) {
            return 'unknown';
        }

        try {
            var destination = new URL(link.href, window.location.origin);

            return destination.hostname === window.location.hostname ? 'internal' : destination.hostname;
        } catch (error) {
            return 'unknown';
        }
    }

    document.addEventListener('submit', function (event) {
        if (event.target.matches('.kreativ-search-form')) {
            track('font_search', { content_type: config.contentType || 'site' });
        }
    });

    document.addEventListener('click', function (event) {
        var target = event.target.closest('a, button');

        if (!target) {
            return;
        }

        if (target.matches('.kreativ-font-cta-button')) {
            track('font_cta_click', {
                content_type: config.contentType || 'font',
                destination_type: destinationType(target)
            });
            return;
        }

        if (target.matches('.kreativ-font-cta-secondary')) {
            track('font_secondary_cta_click', {
                content_type: config.contentType || 'font',
                destination_type: destinationType(target)
            });
            return;
        }

        if (target.matches('.kreativ-font-filter')) {
            track('font_filter_click', { content_type: config.contentType || 'archive' });
            return;
        }

        if (target.matches('[data-kreativ-save-font]')) {
            track('font_save', { content_type: config.contentType || 'font' });
            return;
        }

        if (target.matches('[data-kreativ-research-compare]')) {
            track('font_compare', { content_type: config.contentType || 'font' });
            return;
        }

        if (target.matches('.kreativ-share-copy')) {
            track('font_share_copy', { content_type: config.contentType || 'font' });
        }
    });

    if (config.isNotFound) {
        track('not_found_view', { content_type: 'not_found' });
    }
})();
