(function () {
    'use strict';

    if (window.kreativCjImpressionsConfigured || !window.XMLHttpRequest || !window.WeakSet) {
        return;
    }

    // CJ's legacy pageImpression request enables credentials, but its endpoint
    // responds with Access-Control-Allow-Origin: *. Browsers reject that pair.
    // Send only this exact POST without cookies; preserve CJ's payload, response
    // handlers, and link conversion. Remove when CJ fixes its legacy client.
    var requests = new WeakSet();
    var prototype = window.XMLHttpRequest.prototype;
    var originalOpen = prototype.open;
    var originalSend = prototype.send;

    prototype.open = function (method, url) {
        var result = originalOpen.apply(this, arguments);
        var isImpression = false;

        try {
            var destination = new URL(url, document.baseURI);
            isImpression = String(method).toUpperCase() === 'POST'
                && destination.origin === 'https://origin.cjtrack.me'
                && destination.pathname === '/pageImpression'
                && destination.search === '';
        } catch (error) {
            // Preserve native behavior for unusual URL arguments.
        }

        if (isImpression) {
            requests.add(this);
        } else {
            requests.delete(this);
        }

        return result;
    };

    prototype.send = function () {
        if (requests.has(this)) {
            this.withCredentials = false;
        }

        return originalSend.apply(this, arguments);
    };

    window.kreativCjImpressionsConfigured = true;
})();
