/** Navigation, state/city search fields and accessible FAQ controls. */
document.addEventListener('DOMContentLoaded', function () {
    const translate = function (text) { return (window.fwI18n && window.fwI18n[text]) || text; };
    const formNotice = document.querySelector('.fw-form-notice[tabindex="-1"]');
    if (formNotice) formNotice.focus();
    document.querySelectorAll('[data-fw-location-state]').forEach(function (state) {
        const prefix = state.dataset.fwLocationState;
        const city = document.getElementById(prefix + '-city');
        const source = document.getElementById(prefix + '-locations');
        const status = document.getElementById(prefix + '-location-status');
        if (!city || !source) return;
        let locations;
        try { locations = JSON.parse(source.textContent); }
        catch (error) { return; }
        const updateCities = function (preserveSelection, announce) {
            const selected = preserveSelection ? city.value : '';
            const choices = locations[state.value] || [];
            city.replaceChildren(new Option(translate(state.value ? 'Alle Städte und Orte' : 'Erst Bundesland wählen'), ''));
            choices.forEach(function (choice) { city.add(new Option(choice.label, choice.value)); });
            city.value = choices.some(function (choice) { return choice.value === selected; }) ? selected : '';
            city.disabled = !state.value;
            if (status && announce) status.textContent = state.value
                ? translate('Die Städteauswahl für %s ist verfügbar.').replace('%s', state.selectedOptions[0].textContent)
                : translate('Wählen Sie zuerst ein Bundesland.');
        };
        // Keep the two dependent values together when the browser restores this page.
        const saveSelection = function () {
            try {
                const current = window.history.state || {};
                const pickers = Object.assign({}, current.fwLocationPickers);
                pickers[prefix] = { state: state.value, city: city.value };
                window.history.replaceState(Object.assign({}, current, { fwLocationPickers: pickers }), '');
            } catch (error) { /* Searches remain usable if browser history is unavailable. */ }
        };
        const restoreSelection = function () {
            const saved = window.history.state && window.history.state.fwLocationPickers
                && window.history.state.fwLocationPickers[prefix];
            if (saved && (saved.state === '' || Array.isArray(locations[saved.state]))) {
                state.value = saved.state;
                updateCities(false, false);
                if (Array.from(city.options).some(function (option) { return option.value === saved.city; })) city.value = saved.city;
            } else updateCities(true, false);
        };
        state.addEventListener('change', function () { updateCities(false, true); saveSelection(); });
        city.addEventListener('change', saveSelection);
        restoreSelection();
        window.addEventListener('pageshow', restoreSelection);
    });
    const toggle = document.querySelector('.fw-menu-toggle');
    const nav = document.querySelector('.fw-nav');
    const accountMenu = document.querySelector('.fw-account-menu');
    const accountToggle = accountMenu && accountMenu.querySelector('.fw-account-menu-toggle');
    const mobile = window.matchMedia('(max-width: 991px)');
    const languageMenu = document.querySelector('.fw-language-menu');
    const languageToggle = languageMenu && languageMenu.querySelector('summary');
    const setMenu = function (open) {
        if (!toggle || !nav) return;
        if (open && accountMenu) accountMenu.open = false;
        if (open && languageMenu) languageMenu.open = false;
        nav.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', translate(open ? 'Menü schließen' : 'Menü öffnen'));
    };
    if (toggle && nav) {
        toggle.addEventListener('click', function () { setMenu(toggle.getAttribute('aria-expanded') !== 'true'); });
        nav.addEventListener('click', function (event) { if (event.target.closest('a')) setMenu(false); });
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') { setMenu(false); toggle.focus(); } });
        document.addEventListener('click', function (event) { if (!event.target.closest('.fw-header')) setMenu(false); });
        const resetMenu = function () { if (!mobile.matches) setMenu(false); };
        if (mobile.addEventListener) mobile.addEventListener('change', resetMenu);
        else mobile.addListener(resetMenu);
    }
    if (accountMenu && accountToggle) {
        // Native details provides the click, Enter and Space interaction without JavaScript.
        accountToggle.addEventListener('click', function () { setMenu(false); if (languageMenu) languageMenu.open = false; });
        accountMenu.addEventListener('toggle', function () { if (accountMenu.open) setMenu(false); });
        accountMenu.addEventListener('click', function (event) {
            if (event.target.closest('a')) accountMenu.open = false;
        });
        accountMenu.addEventListener('focusout', function (event) {
            if (!accountMenu.contains(event.relatedTarget)) accountMenu.open = false;
        });
        document.addEventListener('click', function (event) {
            if (!accountMenu.contains(event.target)) accountMenu.open = false;
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && accountMenu.open) {
                accountMenu.open = false;
                accountToggle.focus();
            }
        });
        if (accountMenu.open) setMenu(false);
    }
    if (languageMenu && languageToggle) {
        languageToggle.addEventListener('click', function () { setMenu(false); if (accountMenu) accountMenu.open = false; });
        languageMenu.addEventListener('focusout', function (event) { if (!languageMenu.contains(event.relatedTarget)) languageMenu.open = false; });
        document.addEventListener('click', function (event) { if (!languageMenu.contains(event.target)) languageMenu.open = false; });
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && languageMenu.open) { languageMenu.open = false; languageToggle.focus(); } });
        languageMenu.querySelectorAll('a[href]').forEach(function (link) {
            if (window.location.hash) link.href = link.href.split('#')[0] + window.location.hash;
        });
    }
    document.querySelectorAll('.fw-faq-question').forEach(function (button, index) {
        const answer = button.nextElementSibling;
        if (!answer) return;
        answer.id = answer.id || 'fw-faq-answer-' + index;
        button.setAttribute('aria-controls', answer.id);
        button.setAttribute('aria-expanded', 'false');
        answer.hidden = true;
        answer.style.removeProperty('display');
        button.addEventListener('click', function () {
            const open = button.getAttribute('aria-expanded') !== 'true';
            document.querySelectorAll('.fw-faq-question').forEach(function (other) {
                other.setAttribute('aria-expanded', 'false');
                if (other.nextElementSibling) other.nextElementSibling.hidden = true;
                const icon = other.querySelector('.fw-faq-icon');
                if (icon) icon.textContent = '+';
            });
            answer.hidden = !open;
            button.setAttribute('aria-expanded', String(open));
            const icon = button.querySelector('.fw-faq-icon');
            if (icon) icon.textContent = open ? '−' : '+';
        });
    });
});
