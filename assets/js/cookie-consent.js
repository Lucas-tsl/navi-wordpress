document.addEventListener("DOMContentLoaded", function() {
    const banner = document.getElementById("navi-cookie-banner");
    const modal = document.getElementById("navi-cookie-modal-overlay");
    const modalBox = modal ? modal.querySelector(".navi-cookie-modal") : null;

    let lastFocusedElement = null;

    function getFocusableModalElements() {
        return modalBox.querySelectorAll('button, input:not([disabled]), a[href]');
    }

    function handleModalKeydown(event) {
        if (event.key === "Escape") {
            closeModal();
            return;
        }
        if (event.key !== "Tab") return;
        const focusable = getFocusableModalElements();
        if (focusable.length === 0) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function openModal(trigger) {
        if (!modal || !modalBox) return;
        lastFocusedElement = trigger || document.activeElement;
        function apply() {
            modal.classList.add("navi-cookie-modal-overlay-open");
            document.addEventListener("keydown", handleModalKeydown);
        }
        if (window.naviHub) {
            // Le focus est déposé par le noyau (assets/js/core.js,
            // naviHub.showDetail) sur #navi-cookie-modal-overlay lui-même
            // (tabindex="-1"), pour un comportement identique aux autres
            // panneaux (panier, accessibilité).
            window.naviHub.showDetail("cookie-consent", apply);
        } else {
            apply();
            modalBox.focus();
        }
    }

    // Fermeture manuelle (croix, "Annuler") : revient au choix des icônes
    // (état 2), voir assets/js/core.js — comportement confirmé plutôt qu'une
    // fermeture totale directe depuis le contenu détaillé.
    function closeModal() {
        if (!modal) return;
        function apply() {
            modal.classList.remove("navi-cookie-modal-overlay-open");
            document.removeEventListener("keydown", handleModalKeydown);
            if (lastFocusedElement) lastFocusedElement.focus();
        }
        if (window.naviHub) {
            window.naviHub.backToMenu("cookie-consent", apply);
        } else {
            apply();
        }
    }

    function setConsent(stats, mkt) {
        const expires = new Date(new Date().getTime() + 365 * 24 * 60 * 60 * 1000).toUTCString();
        // "secure" est ignoré silencieusement par le navigateur en HTTP : ne l'ajouter
        // qu'en HTTPS, sinon le cookie n'est jamais posé (boucle infinie en dev/staging).
        const secureFlag = window.location.protocol === "https:" ? "; secure" : "";
        const consentVersion = (typeof naviCookieConfig !== "undefined" && naviCookieConfig.consentVersion) ? naviCookieConfig.consentVersion : "1";
        document.cookie = `navi_consent_stats=${stats}; expires=${expires}; path=/; samesite=strict${secureFlag}`;
        document.cookie = `navi_consent_mkt=${mkt}; expires=${expires}; path=/; samesite=strict${secureFlag}`;
        document.cookie = `navi_consent_version=${consentVersion}; expires=${expires}; path=/; samesite=strict${secureFlag}`;
        document.cookie = `navi_consent_all=1; expires=${expires}; path=/; samesite=strict${secureFlag}`;

        let statsStatus = stats === 1 ? 'granted' : 'denied';
        let mktStatus = mkt === 1 ? 'granted' : 'denied';

        if(typeof gtag === 'function') {
            gtag('consent', 'update', {
                'ad_storage': mktStatus,
                'ad_user_data': mktStatus,
                'ad_personalization': mktStatus,
                'analytics_storage': statsStatus
            });
        }

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ 'event': 'navi_cookie_consent_updated' });

        if(banner) banner.style.display = "none";
        closeModal();
        showSavedToast();
    }

    // Confirmation brève après un choix enregistré (accepter/refuser/personnaliser) :
    // sans elle, la bannière/modale se contentait de se fermer, sans aucun
    // signal explicite que le choix avait bien été pris en compte.
    function showSavedToast() {
        var savedText = (typeof naviCookieConfig !== "undefined" && naviCookieConfig.savedText)
            ? naviCookieConfig.savedText
            : "Préférences enregistrées";
        var toast = document.createElement("div");
        toast.className = "navi-cookie-toast";
        toast.setAttribute("role", "status");
        toast.textContent = savedText;
        document.body.appendChild(toast);
        window.requestAnimationFrame(function () {
            toast.classList.add("navi-cookie-toast-visible");
        });
        setTimeout(function () {
            toast.classList.remove("navi-cookie-toast-visible");
            setTimeout(function () { toast.remove(); }, 300);
        }, 2200);
    }

    const btnAccepter = document.getElementById("navi-cookie-btn-accepter");
    const btnRefuser = document.getElementById("navi-cookie-btn-refuser");
    const btnPrefs = document.getElementById("navi-cookie-btn-prefs");
    const btnSavePrefs = document.getElementById("navi-cookie-btn-save-prefs");
    const btnCloseModal = document.getElementById("navi-cookie-btn-close-modal");
    const btnModalCross = modal ? modal.querySelector(".navi-cookie-modal-close") : null;

    if(btnAccepter) btnAccepter.addEventListener("click", () => setConsent(1, 1));
    if(btnRefuser) btnRefuser.addEventListener("click", () => setConsent(0, 0));

    if(btnPrefs) btnPrefs.addEventListener("click", (event) => {
        // #navi-cookie-banner (contrairement à la modale) n'est jamais
        // déplacé à l'intérieur de #navi-fab (voir assets/js/core.js) : sans
        // stopPropagation, ce même clic remonte jusqu'à document, où le
        // noyau referme tout ce qui est cliqué en dehors du FAB — annulant
        // l'ouverture qu'on vient de demander, dans le même clic (la modale
        // s'ouvre puis se referme aussitôt, perçu comme "la bannière
        // disparaît sans rien montrer").
        event.stopPropagation();
        banner.style.display = "none";
        openModal(btnPrefs);
    });

    // Le clic en dehors et la touche Échap sont désormais gérés de façon
    // centralisée par le noyau (assets/js/core.js), puisque ce panneau est
    // maintenant un contenu du même objet #navi-fab plutôt qu'un élément
    // indépendant.

    if(btnCloseModal) btnCloseModal.addEventListener("click", () => {
        if(!document.cookie.includes('navi_consent_all')) banner.style.display = "block";
        closeModal();
    });

    // Croix de fermeture en coin, cohérente avec les panneaux panier et
    // accessibilité (assets/js/sticky-cart.js, assets/js/accessibility.js).
    if(btnModalCross) btnModalCross.addEventListener("click", () => {
        if(!document.cookie.includes('navi_consent_all')) banner.style.display = "block";
        closeModal();
    });

    if(btnSavePrefs) btnSavePrefs.addEventListener("click", () => {
        const stats = document.getElementById("navi-cookie-chk-stats").checked ? 1 : 0;
        const mkt = document.getElementById("navi-cookie-chk-mkt").checked ? 1 : 0;
        setConsent(stats, mkt);
    });

    // Réouverture de la modale depuis l'icône 🍪 du bouton flottant central (hub)
    document.addEventListener("navi:action", function(event) {
        if (event.detail && event.detail.action === "open-cookie-modal") {
            if (banner) banner.style.display = "none";
            openModal();
        }
    });

    // Réouverture depuis un lien [navi_cookie_preferences_link] posé par le
    // site dans son footer (includes/modules/cookie-consent/public-display.php) :
    // convention attendue par les visiteurs, en plus du raccourci du hub.
    document.addEventListener("click", function(event) {
        var link = event.target.closest(".navi-cookie-preferences-link");
        if (!link) return;
        event.preventDefault();
        // Même raison que sur le bouton "Personnaliser" de la bannière : ce
        // lien vit hors de #navi-fab, sans stopPropagation le clic remonterait
        // jusqu'au gestionnaire "clic en dehors" du noyau (assets/js/core.js)
        // et refermerait la modale dans le clic même qui l'ouvre.
        event.stopPropagation();
        if (banner) banner.style.display = "none";
        openModal(link);
    });

    // Le hub s'est refermé entièrement (clic extérieur, Échap, un autre
    // module affiché...) pendant que ce panneau était actif : on remet à
    // jour notre propre état d'affichage sans redéclencher de fermeture.
    document.addEventListener("navi:closed", function(event) {
        if (event.detail && event.detail.id === "cookie-consent") {
            if (modal) modal.classList.remove("navi-cookie-modal-overlay-open");
            document.removeEventListener("keydown", handleModalKeydown);
        }
    });
});
