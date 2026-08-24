// Injection du Google Consent Mode V2 — voir navi_cookie_enqueue_consent_mode()
// (includes/modules/cookie-consent/public-display.php), AVANT GTM, très
// important de garder ce script tôt dans le <head> (enqueue en priorité 1
// sur wp_enqueue_scripts, in_footer=false, donc imprimé avant tout hook
// wp_head par défaut — priorité 10 — comme un snippet GTM classique).
// naviConsentModeData est injecté par wp_localize_script().
(function () {
    'use strict';

    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }

    var naviConsentVersion = document.cookie.match(/(?:^|; )navi_consent_version=([^;]*)/);
    var naviHasConsent = document.cookie.indexOf('navi_consent_all=') !== -1
        && naviConsentVersion !== null
        && naviConsentVersion[1] === naviConsentModeData.version;
    var naviStats = document.cookie.indexOf('navi_consent_stats=1') !== -1 ? 'granted' : 'denied';
    var naviMkt = document.cookie.indexOf('navi_consent_mkt=1') !== -1 ? 'granted' : 'denied';

    gtag('consent', 'default', {
        'ad_storage': naviHasConsent ? naviMkt : 'denied',
        'ad_user_data': naviHasConsent ? naviMkt : 'denied',
        'ad_personalization': naviHasConsent ? naviMkt : 'denied',
        'analytics_storage': naviHasConsent ? naviStats : 'denied',
        // Le délai n'a d'utilité que le temps de laisser un nouveau visiteur
        // répondre à la bannière ; inutile de ralentir GTM pour un visiteur
        // dont le choix est déjà connu.
        'wait_for_update': naviHasConsent ? 0 : 500
    });
})();
