(function () {
    const input = document.getElementById('name');
    const warning = document.getElementById('name-warning');

    if (!input || !warning) return;

    const url = input.dataset.nameCheckUrl;
    const currentId = input.dataset.currentCardholderId || '';

    let timer = null;

    function resetState() {
        input.style.borderColor = '';
        input.style.boxShadow = '';
        warning.hidden = true;
        warning.textContent = '';
    }

    function showWarning(matches) {
        input.style.borderColor = '#B91C1C';
        input.style.boxShadow = '0 0 0 3px rgba(185, 28, 28, 0.15)';

        const names = matches
            .map(match => `${match.name} (${match.id_no})`)
            .join(', ');

        warning.textContent = `Possible duplicate/similar name found: ${names}`;
        warning.hidden = false;
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);

        const name = input.value.trim();

        if (name.length < 3) {
            resetState();
            return;
        }

        timer = setTimeout(async function () {
            const params = new URLSearchParams({
                name: name,
                exclude: currentId
            });

            try {
                const response = await fetch(`${url}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.match) {
                    showWarning(data.matches);
                } else {
                    resetState();
                }
            } catch (error) {
                resetState();
            }
        }, 350);
    });
})();