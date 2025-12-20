(() => {
    let token = appConfig.token;
    const baseUrl = appConfig.baseUrl;

    const statusEl = document.getElementById('status');
    const linkDisplay = document.getElementById('link-display');
    const luckyResult = document.getElementById('lucky-result');
    const historyEl = document.getElementById('history');
    const tokenText = document.getElementById('token-text');

    const btnRegen = document.getElementById('btn-regen');
    const btnDeactivate = document.getElementById('btn-deactivate');
    const btnLucky = document.getElementById('btn-lucky');
    const btnHistory = document.getElementById('btn-history');

    function setStatus(message, type = '') {
        statusEl.textContent = message;
        statusEl.className = 'alert ' + type;
    }

    async function api(path, options = {}) {
        const response = await fetch(path, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            ...options,
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.error || 'Unexpected error');
        }
        return data;
    }

    async function handleRegen() {
        try {
            setStatus('Regenerating link...', '');
            const data = await api(`/link/${token}/regen`);
            token = data.token;
            const newUrl = `/link/${token}`;
            linkDisplay.textContent = `New link: ${data.link}`;
            tokenText.textContent = token;
            history.replaceState({}, '', newUrl);
            setStatus('Link regenerated successfully', 'success');
        } catch (e) {
            setStatus(e.message, 'danger');
        }
    }

    async function handleDeactivate() {
        try {
            setStatus('Deactivating link...', '');
            await api(`/link/${token}/deactivate`);
            setStatus('Link deactivated. This page will no longer be accessible.', 'danger');
            btnRegen.disabled = true;
            btnDeactivate.disabled = true;
            btnLucky.disabled = true;
            btnHistory.disabled = true;
        } catch (e) {
            setStatus(e.message, 'danger');
        }
    }

    async function handleLucky() {
        try {
            setStatus('Rolling...', '');
            const data = await api(`/link/${token}/lucky`);
            luckyResult.innerHTML = `Number: <strong>${data.number}</strong> | Result: <strong>${data.result.toUpperCase()}</strong> | Amount: <strong>${data.amount}</strong>`;
            setStatus('Done', 'success');
            await loadHistory();
        } catch (e) {
            setStatus(e.message, 'danger');
        }
    }

    async function loadHistory() {
        try {
            const response = await fetch(`/link/${token}/history`, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (response.ok) {
                renderHistory(data.attempts || []);
            } else {
                throw new Error(data.error || 'Failed to load history');
            }
        } catch (e) {
            historyEl.textContent = e.message;
        }
    }

    function renderHistory(attempts) {
        if (!attempts.length) {
            historyEl.textContent = 'No attempts yet.';
            return;
        }

        let rows = attempts.map(attempt => `<tr><td>${attempt.created_at}</td><td>${attempt.number}</td><td>${attempt.result}</td><td>${attempt.amount}</td></tr>`).join('');
        historyEl.innerHTML = `<table><thead><tr><th>Date</th><th>Number</th><th>Result</th><th>Amount</th></tr></thead><tbody>${rows}</tbody></table>`;
    }

    btnRegen.addEventListener('click', handleRegen);
    btnDeactivate.addEventListener('click', handleDeactivate);
    btnLucky.addEventListener('click', handleLucky);
    btnHistory.addEventListener('click', loadHistory);

    loadHistory();
})();
