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

    // Geolocation "In meiner Nähe" Button-Handler
    document.querySelectorAll('.fw-btn-near-me').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            if (!navigator.geolocation) {
                alert(translate('Standortabfrage wird von Ihrem Browser leider nicht unterstützt.'));
                return;
            }

            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span aria-hidden="true">⏳</span> <span>' + translate('Standort wird ermittelt...') + '</span>';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);
                    const baseUrl = button.dataset.archiveUrl || window.location.pathname;
                    const url = new URL(baseUrl, window.location.origin);
                    url.searchParams.set('fw_lat', lat);
                    url.searchParams.set('fw_lng', lng);
                    window.location.href = url.toString();
                },
                function (error) {
                    button.disabled = false;
                    button.innerHTML = originalText;
                    let msg = translate('Standort konnte nicht ermittelt werden.');
                    if (error.code === error.PERMISSION_DENIED) {
                        msg = translate('Standortfreigabe wurde verweigert. Bitte erlauben Sie den Standortzugriff in Ihrem Browser oder wählen Sie Ihre Stadt manuell.');
                    }
                    alert(msg);
                },
                { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
            );
        });
    });

    // --- Bookmarks ("Merken" / "Gemerkt") Feature (Section 34) ---
    const BOOKMARK_KEY = 'fw_saved_workshops';
    const getBookmarks = function () {
        try {
            const data = localStorage.getItem(BOOKMARK_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    };
    const saveBookmarks = function (list) {
        try {
            localStorage.setItem(BOOKMARK_KEY, JSON.stringify(list));
        } catch (e) {}
    };
    const updateBookmarkBtn = function (btn, isBookmarked) {
        const icon = btn.querySelector('.fw-bookmark-icon');
        const text = btn.querySelector('span:not(.fw-bookmark-icon)');
        if (isBookmarked) {
            btn.classList.add('is-bookmarked');
            btn.setAttribute('aria-pressed', 'true');
            if (icon) icon.textContent = '📌';
            if (text) text.textContent = translate('Gemerkt');
            btn.title = translate('Aus Merkliste entfernen');
        } else {
            btn.classList.remove('is-bookmarked');
            btn.setAttribute('aria-pressed', 'false');
            if (icon) icon.textContent = '🔖';
            if (text) text.textContent = translate('Merken');
            btn.title = translate('Merken');
        }
    };

    const initialBookmarks = getBookmarks();
    document.querySelectorAll('.fw-card-bookmark-btn').forEach(function (btn) {
        const id = btn.dataset.id;
        if (!id) return;
        updateBookmarkBtn(btn, initialBookmarks.includes(id));

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let bookmarks = getBookmarks();
            const exists = bookmarks.includes(id);
            if (exists) {
                bookmarks = bookmarks.filter(function (item) { return item !== id; });
            } else {
                bookmarks.push(id);
            }
            saveBookmarks(bookmarks);

            // Update all buttons with this ID on page
            document.querySelectorAll('.fw-card-bookmark-btn[data-id="' + id + '"]').forEach(function (b) {
                updateBookmarkBtn(b, !exists);
            });
        });
    });

    // --- Event Tracking Dispatcher (Section 32) ---
    const trackEvent = function (eventName, eventParams) {
        eventParams = eventParams || {};
        try {
            // 1. Dispatch DOM CustomEvent
            window.dispatchEvent(new CustomEvent('findewerkstatt:event', {
                detail: Object.assign({ event: eventName }, eventParams)
            }));

            // 2. Google Analytics 4 (gtag)
            if (typeof window.gtag === 'function') {
                window.gtag('event', eventName, eventParams);
            }

            // 3. Google Tag Manager dataLayer
            if (Array.isArray(window.dataLayer)) {
                window.dataLayer.push(Object.assign({ event: eventName }, eventParams));
            }
        } catch (err) {}
    };

    // Global listener for elements with data-event attribute
    document.addEventListener('click', function (e) {
        const target = e.target.closest('[data-event]');
        if (!target) return;
        const eventName = target.getAttribute('data-event');
        const plan = target.getAttribute('data-plan') || '';
        if (eventName) {
            trackEvent(eventName, {
                element_text: target.textContent ? target.textContent.trim().substring(0, 50) : '',
                element_href: target.getAttribute('href') || '',
                plan: plan
            });
        }
    });

    // --- Smooth Scroll for In-Page Anchors (Viewability Optimization) ---
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = anchor.getAttribute('href');
            if (!targetId || targetId === '#' || targetId.length <= 1) return;
            const targetElem = document.querySelector(targetId);
            if (targetElem) {
                e.preventDefault();
                targetElem.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (window.history && window.history.pushState) {
                    window.history.pushState(null, '', targetId);
                }
            }
        });
    });

    // --- Facebook Tabs Active Switcher ---
    const fbTabs = document.querySelectorAll('.fw-fb-tab');
    if (fbTabs.length > 0) {
        fbTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                fbTabs.forEach(function (t) { t.classList.remove('is-active'); });
                tab.classList.add('is-active');
            });
        });
    }

    // --- Recently Viewed Workshops History (localStorage) ---
    const RECENT_KEY = 'fw_recent_workshops';
    const recentBox = document.getElementById('fw-recently-viewed-box');
    const recentList = document.getElementById('fw-recently-viewed-list');
    const heroElem = document.querySelector('.fw-fb-profile-card, .fw-single-hero');

    if (recentBox && recentList && heroElem) {
        let recents = [];
        try {
            const stored = localStorage.getItem(RECENT_KEY);
            recents = stored ? JSON.parse(stored) : [];
            if (!Array.isArray(recents)) recents = [];
        } catch (e) {
            recents = [];
        }

        const currentId = heroElem.querySelector('.fw-card-bookmark-btn') ? heroElem.querySelector('.fw-card-bookmark-btn').dataset.id : '';
        const currentTitle = (heroElem.querySelector('h1') ? heroElem.querySelector('h1').textContent : '').trim();
        const currentCityElem = heroElem.querySelector('.fw-card-address span');
        const currentCity = currentCityElem ? currentCityElem.textContent.trim() : '';
        const currentRating = heroElem.querySelector('.fw-rating-number') ? heroElem.querySelector('.fw-rating-number').textContent.trim() : '';

        // Render previous workshops if available
        const itemsToRender = recents.filter(function (item) { return item.id && item.id !== currentId; }).slice(0, 4);
        if (itemsToRender.length > 0) {
            recentBox.style.display = 'block';
            recentList.innerHTML = itemsToRender.map(function (item) {
                return '<div class="fw-recent-item" style="background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius); padding:12px; display:flex; flex-direction:column; gap:6px;">' +
                    '<a href="' + item.url + '" style="font-weight:700; color:var(--fw-primary); font-size:14px; text-decoration:none; line-height:1.3;">' + item.title + '</a>' +
                    (item.city ? '<span style="font-size:12px; color:var(--fw-text-muted);">📍 ' + item.city + '</span>' : '') +
                    (item.rating ? '<span style="font-size:12px; color:#f59e0b; font-weight:700;">★ ' + item.rating + '</span>' : '') +
                    '<a href="' + item.url + '" style="margin-top:auto; font-size:12px; font-weight:600; color:var(--fw-accent); text-decoration:none;">' + translate('Details ansehen') + ' &rarr;</a>' +
                '</div>';
            }).join('');
        }

        // Save current workshop to history
        if (currentId && currentTitle) {
            const newEntry = {
                id: currentId,
                title: currentTitle,
                url: window.location.pathname,
                city: currentCity,
                rating: currentRating
            };
            const updated = [newEntry].concat(recents.filter(function (item) { return item.id !== currentId; })).slice(0, 8);
            try {
                localStorage.setItem(RECENT_KEY, JSON.stringify(updated));
            } catch (e) {}
        }
    }
});

