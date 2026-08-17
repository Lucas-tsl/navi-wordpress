<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Lit et assainit une valeur de cookie ; retourne null si absente.
function navi_cookie_cookie_value( $nom ) {
    if ( ! isset( $_COOKIE[ $nom ] ) ) {
        return null;
    }
    return sanitize_text_field( wp_unslash( $_COOKIE[ $nom ] ) );
}

// Injection du Google Consent Mode V2 (AVANT GTM, très important de garder ce JS ici dans le <head>)
add_action( 'wp_head', 'navi_cookie_inject_consent_mode', 1 );
function navi_cookie_inject_consent_mode() {
    ?>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        var naviConsentVersion = document.cookie.match(/(?:^|; )navi_consent_version=([^;]*)/);
        var naviHasConsent = document.cookie.indexOf('navi_consent_all=') !== -1
            && naviConsentVersion !== null
            && naviConsentVersion[1] === '<?php echo esc_js( NAVI_COOKIE_CONSENT_VERSION ); ?>';
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
    </script>
    <?php
}

// Lien de gestion des cookies utilisable n'importe où (typiquement le footer
// du site) : [navi_cookie_preferences_link] ou [navi_cookie_preferences_link
// text="Gérer les cookies"]. Le seul point d'entrée existant jusqu'ici pour
// rouvrir les préférences était l'icône 🍪 du bouton flottant, deux clics
// sans le repère visuel qu'un visiteur attend habituellement (un lien dédié
// en pied de page).
add_shortcode( 'navi_cookie_preferences_link', 'navi_cookie_preferences_link_shortcode' );
function navi_cookie_preferences_link_shortcode( $atts ) {
    $atts = shortcode_atts(
        array( 'text' => __( 'Gérer les cookies', 'navi' ) ),
        $atts,
        'navi_cookie_preferences_link'
    );
    return '<a href="#" class="navi-cookie-preferences-link">' . esc_html( $atts['text'] ) . '</a>';
}

// Affichage HTML de la bannière et de la modale de préférences.
// Le bouton de réouverture après consentement est désormais le bouton
// flottant central du hub (icône 🍪 dans includes/core/frontend.php),
// qui déclenche l'événement 'navi:action' écouté dans assets/js/cookie-consent.js.
add_action( 'wp_footer', 'navi_cookie_afficher_banniere' );
function navi_cookie_afficher_banniere() {
    $logo          = navi_cookie_logo_url();
    $texte         = get_option( 'navi_cookie_texte_banniere', navi_cookie_texte_par_defaut() );
    $url_politique = get_option( 'navi_cookie_url_politique', '#' );
    $url_mentions  = get_option( 'navi_cookie_url_mentions', '#' );

    $choix_fait = null !== navi_cookie_cookie_value( 'navi_consent_all' )
        && navi_cookie_cookie_value( 'navi_consent_version' ) === NAVI_COOKIE_CONSENT_VERSION;
    ?>

    <div id="navi-cookie-banner" class="navi-cookie-banner" role="region" aria-labelledby="navi-cookie-banner-title" style="display: <?php echo $choix_fait ? 'none' : 'block'; ?>;">
        <?php if ( ! empty( $logo ) ) : ?><img src="<?php echo esc_url( $logo ); ?>" alt="<?php esc_attr_e( 'Logo', 'navi' ); ?>" class="navi-cookie-logo" /><?php endif; ?>
        <h3 class="navi-cookie-title" id="navi-cookie-banner-title"><?php esc_html_e( 'Gérer le consentement', 'navi' ); ?></h3>
        <p class="navi-cookie-desc"><?php echo nl2br( esc_html( $texte ) ); ?></p>
        <div class="navi-cookie-links">
            <a href="<?php echo esc_url( $url_politique ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'navi' ); ?></a> | <a href="<?php echo esc_url( $url_mentions ); ?>"><?php esc_html_e( 'Mentions légales', 'navi' ); ?></a>
        </div>
        <?php /* "Tout Accepter" et "Tout Refuser" à même niveau, même poids visuel :
                 la CNIL exige une prééminence équivalente entre les deux (recommandations
                 2020). "Personnaliser" reste un choix possible mais secondaire. */ ?>
        <div class="navi-cookie-actions">
            <button id="navi-cookie-btn-accepter" class="navi-cookie-btn navi-cookie-btn-accepter"><?php esc_html_e( 'Tout Accepter', 'navi' ); ?></button>
            <button id="navi-cookie-btn-refuser" class="navi-cookie-btn navi-cookie-btn-refuser"><?php esc_html_e( 'Tout Refuser', 'navi' ); ?></button>
        </div>
        <button id="navi-cookie-btn-prefs" class="navi-cookie-btn-link"><?php esc_html_e( 'Personnaliser mes choix', 'navi' ); ?></button>
    </div>

    <div id="navi-cookie-modal-overlay" class="navi-cookie-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="navi-cookie-modal-title" tabindex="-1">
        <div class="navi-cookie-modal" tabindex="-1">
            <button type="button" class="navi-cookie-modal-close" aria-label="<?php esc_attr_e( 'Fermer', 'navi' ); ?>">✕</button>
            <div class="navi-cookie-modal-scroll">
                <h3 class="navi-cookie-title" id="navi-cookie-modal-title"><?php esc_html_e( 'Préférences des cookies', 'navi' ); ?></h3>
                <div class="navi-cookie-type">
                    <label for="navi-cookie-chk-necessaires">
                        <strong><?php esc_html_e( 'Strictement Nécessaires', 'navi' ); ?></strong>
                        <p class="navi-cookie-desc"><?php esc_html_e( 'Requis pour le site (panier, sécurité). Non désactivables.', 'navi' ); ?></p>
                    </label>
                    <input type="checkbox" id="navi-cookie-chk-necessaires" checked disabled>
                </div>
                <div class="navi-cookie-type">
                    <label for="navi-cookie-chk-stats">
                        <strong><?php esc_html_e( 'Statistiques (Google Analytics)', 'navi' ); ?></strong>
                        <p class="navi-cookie-desc"><?php esc_html_e( "Pour mesurer l'audience de la boutique.", 'navi' ); ?></p>
                    </label>
                    <input type="checkbox" id="navi-cookie-chk-stats" <?php echo ( '1' === navi_cookie_cookie_value( 'navi_consent_stats' ) ) ? 'checked' : ''; ?>>
                </div>
                <div class="navi-cookie-type">
                    <label for="navi-cookie-chk-mkt">
                        <strong><?php esc_html_e( 'Marketing (Pixel Facebook, Google Ads)', 'navi' ); ?></strong>
                        <p class="navi-cookie-desc"><?php esc_html_e( 'Pour afficher des publicités ciblées.', 'navi' ); ?></p>
                    </label>
                    <input type="checkbox" id="navi-cookie-chk-mkt" <?php echo ( '1' === navi_cookie_cookie_value( 'navi_consent_mkt' ) ) ? 'checked' : ''; ?>>
                </div>
                <div class="navi-cookie-actions" style="margin-top: 20px;">
                    <button id="navi-cookie-btn-save-prefs" class="navi-cookie-btn navi-cookie-btn-accepter"><?php esc_html_e( 'Enregistrer mes choix', 'navi' ); ?></button>
                    <button id="navi-cookie-btn-close-modal" class="navi-cookie-btn navi-cookie-btn-refuser"><?php esc_html_e( 'Annuler', 'navi' ); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php
}
