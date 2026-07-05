(function () {
    const loader = document.getElementById('global-loader');

    if (!loader) return;

    function showLoader(message, subtext) {
        const loaderText = loader.querySelector('.loader-text');
        const loaderSubtext = loader.querySelector('.loader-subtext');

        if (loaderText && message) {
            loaderText.textContent = message;
        }

        if (loaderSubtext && subtext) {
            loaderSubtext.textContent = subtext;
        }

        loader.hidden = false;
    }

    function shouldSkipLink(link) {
        if (!link.href) return true;
        if (link.target === '_blank') return true;
        if (link.hasAttribute('download')) return true;
        if (link.href.startsWith('mailto:')) return true;
        if (link.href.startsWith('tel:')) return true;
        if (link.href.includes('#')) return true;
        if (link.dataset.noLoader === 'true') return true;

        return false;
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.noLoader === 'true') return;

        const hasFileUpload = form.querySelector('input[type="file"]');

        if (hasFileUpload) {
            showLoader(
                'Uploading batch file...',
                'Please wait. Large CSV or photo ZIP files may take a while.'
            );
        } else {
            showLoader(
                'Processing...',
                'Please wait while the system saves your changes.'
            );
        }

        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

        if (submitButton) {
            setTimeout(function () {
                submitButton.disabled = true;
            }, 50);
        }
    });

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a');

        if (!link || shouldSkipLink(link)) return;

        const currentUrl = window.location.href.split('#')[0];
        const nextUrl = link.href.split('#')[0];

        if (currentUrl === nextUrl) return;

        showLoader(
            'Loading...',
            'Please wait while the page opens.'
        );
    });
})();