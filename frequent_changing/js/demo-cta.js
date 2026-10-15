(function () {
    'use strict';

    var DEFAULT_WHATSAPP_NUMBER = '8801812391633';
    var DEFAULT_WHATSAPP_TEXT = 'Any questions?';
    var URL_PARAM_SS = 'ss';
    var URL_VALUE_DEMO = 'demo';
    var URL_VALUE_DEMOS = 'demos';  /* opposite: force WhatsApp and remember */
    /**
     * Get a URL query parameter by name.
     * @param {string} name - Parameter name (e.g. 'ss')
     * @returns {string|null} Value or null
     */
    function getUrlParam(name) {
        var search = window.location.search;
        if (!search) return null;
        if (typeof URLSearchParams !== 'undefined') {
            var params = new URLSearchParams(search);
            return params.get(name);
        }
        var pairs = search.substring(1).split('&');
        for (var i = 0; i < pairs.length; i++) {
            var parts = pairs[i].split('=');
            if (parts[0] === name) return decodeURIComponent(parts[1] || '');
        }
        return null;
    }
    var STORAGE_KEY_SS = 'ss';

    /**
     * ?ss=demo  → show CodeCanyon, save to localStorage (stays until clear).
     * ?ss=demos → opposite: show WhatsApp and save so it stays until clear.
     * No param  → use saved value (demo → CodeCanyon, demos or missing → WhatsApp).
     */
    function initDemoCTA() {
        var ssParam = getUrlParam(URL_PARAM_SS);
        try {
            if (ssParam === URL_VALUE_DEMO) {
                localStorage.setItem(STORAGE_KEY_SS, URL_VALUE_DEMO);
            } else if (ssParam === URL_VALUE_DEMOS) {
                localStorage.setItem(STORAGE_KEY_SS, URL_VALUE_DEMOS);
            }
        } catch (e) {}
        var storedSs = null;
        try {
            storedSs = localStorage.getItem(STORAGE_KEY_SS);
        } catch (e) {}
        var showDemoLink = (ssParam === URL_VALUE_DEMO) || (storedSs === URL_VALUE_DEMO);

        var demoEl = document.querySelector('.cta-demo-link');
        var whatsappEl = document.querySelector('.cta-default-whatsapp');

        if (demoEl) {
            demoEl.style.display = showDemoLink ? '' : 'none';
        }
        if (whatsappEl) {
            whatsappEl.style.display = showDemoLink ? 'none' : '';
        }
    }

    /**
     * Build and inject the default WhatsApp CTA if container exists but is empty.
     * Call after initDemoCTA if you use a placeholder container.
     */
    function ensureDefaultWhatsAppMarkup(containerSelector) {
        var container = document.querySelector(containerSelector || '.cta-default-whatsapp');
        if (!container || container.querySelector('a')) return;
        var link = document.createElement('a');
        link.setAttribute('href', 'https://wa.me/' + DEFAULT_WHATSAPP_NUMBER);
        link.setAttribute('target', '_blank');
        link.setAttribute('rel', 'noopener');
        link.className = 'btn btn-success custom_shadow cta-whatsapp-link';
        link.innerHTML = '<i class="fa fa-whatsapp" aria-hidden="true"></i> ' + DEFAULT_WHATSAPP_TEXT;
        container.appendChild(link);
    }

    // Expose for use from login/userHome (and root if needed)
    window.getUrlParam = getUrlParam;
    window.initDemoCTA = initDemoCTA;
    window.ensureDefaultWhatsAppMarkup = ensureDefaultWhatsAppMarkup;

    // Auto-run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initDemoCTA();
        });
    } else {
        initDemoCTA();
    }
})();
