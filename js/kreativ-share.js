(function () {
    function initShareCopy() {
        document.querySelectorAll('.kreativ-share-copy').forEach(function (button) {
            button.addEventListener('click', async function () {
                var url = button.getAttribute('data-share-url');
                var label = button.querySelector('span');

                if (!url || !label) {
                    return;
                }

                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        var tempInput = document.createElement('input');
                        tempInput.value = url;
                        document.body.appendChild(tempInput);
                        tempInput.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempInput);
                    }

                    var originalText = label.textContent;
                    label.textContent = 'Copied';
                    window.setTimeout(function () {
                        label.textContent = originalText;
                    }, 1500);
                } catch (error) {
                    window.open(url, '_blank', 'noopener');
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initShareCopy);
    } else {
        initShareCopy();
    }
})();
