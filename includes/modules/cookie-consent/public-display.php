<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Lit et assainit une valeur de cookie ; retourne null si absente.
function navi_cookie_cookie_value( $nom ) {
    if ( ! isset( $_COOKIE[ $nom ] ) ) {
        return null;
    }
    return sanitize_text_field( wp_unslash( $_COOKIE[ $nom ] ) );
}

// Injection du Google Consent Mode V2 (AVANT GTM, très important de garder ce
// script tôt dans le <head>) : enqueue en priorité 1 sur wp_enqueue_scripts
// avec in_footer=false, pour être imprimé avant tout hook wp_head par
// défaut (priorité 10), là où un snippet GTM classique s'injecte.
add_action( 'wp_enqueue_scripts', 'navi_cookie_enqueue_consent_mode', 1 );
function navi_cookie_enqueue_consent_mode() {
    wp_register_script( 'navi-cookie-consent-mode', NAVI_PLUGIN_URL . 'assets/js/cookie-consent-mode.js', array(), NAVI_VERSION, false );
    wp_localize_script(
        'navi-cookie-consent-mode',
        'naviConsentModeData',
        array( 'version' => NAVI_COOKIE_CONSENT_VERSION )
    );
    wp_enqueue_script( 'navi-cookie-consent-mode' );
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
        array( 'text' => __( 'Gérer les cookies', 'saito-navi' ) ),
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
        <?php if ( ! empty( $logo ) ) : ?><img src="<?php echo esc_url( $logo ); ?>" alt="<?php esc_attr_e( 'Logo', 'saito-navi' ); ?>" class="navi-cookie-logo" /><?php endif; ?>
        <h3 class="navi-cookie-title" id="navi-cookie-banner-title"><?php esc_html_e( 'Gérer le consentement', 'saito-navi' ); ?></h3>
        <p class="navi-cookie-desc"><?php echo nl2br( esc_html( $texte ) ); ?></p>
        <div class="navi-cookie-links">
            <a href="<?php echo esc_url( $url_politique ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'saito-navi' ); ?></a> | <a href="<?php echo esc_url( $url_mentions ); ?>"><?php esc_html_e( 'Mentions légales', 'saito-navi' ); ?></a>
        </div>
        <?php /* "Tout Accepter" et "Tout Refuser" à même niveau, même poids visuel :
                 la CNIL exige une prééminence équivalente entre les deux (recommandations
                 2020). "Personnaliser" reste un choix possible mais secondaire. */ ?>
        <div class="navi-cookie-actions">
            <button id="navi-cookie-btn-accepter" class="navi-cookie-btn navi-cookie-btn-accepter"><?php esc_html_e( 'Tout Accepter', 'saito-navi' ); ?></button>
            <button id="navi-cookie-btn-refuser" class="navi-cookie-btn navi-cookie-btn-refuser"><?php esc_html_e( 'Tout Refuser', 'saito-navi' ); ?></button>
        </div>
        <button id="navi-cookie-btn-prefs" class="navi-cookie-btn-link"><?php esc_html_e( 'Personnaliser mes choix', 'saito-navi' ); ?></button>
    </div>

    <div id="navi-cookie-modal-overlay" class="navi-cookie-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="navi-cookie-modal-title" tabindex="-1">
        <div class="navi-cookie-modal" tabindex="-1">
            <button type="button" class="navi-cookie-modal-close" aria-label="<?php esc_attr_e( 'Fermer', 'saito-navi' ); ?>">✕</button>
            <div class="navi-cookie-modal-scroll">
                <h3 class="navi-cookie-title" id="navi-cookie-modal-title"><?php esc_html_e( 'Préférences des cookies', 'saito-navi' ); ?></h3>
                <div class="navi-cookie-type">
                    <label for="navi-cookie-chk-necessaires">
                        <strong><?php esc_html_e( 'Strictement Nécessaires', 'saito-navi' ); ?></strong>
                        <p class="navi-cookie-desc"><?php esc_html_e( 'Requis pour le site (panier, sécurité). Non désactivables.', 'saito-navi' ); ?></p>
                    </label>
                    <input type="checkbox" id="navi-cookie-chk-necessaires" checked disabled>
                </div>
                <div class="navi-cookie-type">
                    <label for="navi-cookie-chk-stats">
                        <strong><?php esc_html_e( 'Statistiques (Google Analytics)', 'saito-navi' ); ?></strong>
                        <p class="navi-cookie-desc"><?php esc_html_e( "Pour mesurer l'audience de la boutique.", 'saito-navi' ); ?></p>
                    </label>
                    <input type="checkbox" id="navi-cookie-chk-stats" <?php echo ( '1' === navi_cookie_cookie_value( 'navi_consent_stats' ) ) ? 'checked' : ''; ?>>
                </div>
                <div class="navi-cookie-type">
                    <label for="navi-cookie-chk-mkt">
                        <strong><?php esc_html_e( 'Marketing (Pixel Facebook, Google Ads)', 'saito-navi' ); ?></strong>
                        <p class="navi-cookie-desc"><?php esc_html_e( 'Pour afficher des publicités ciblées.', 'saito-navi' ); ?></p>
                    </label>
                    <input type="checkbox" id="navi-cookie-chk-mkt" <?php echo ( '1' === navi_cookie_cookie_value( 'navi_consent_mkt' ) ) ? 'checked' : ''; ?>>
                </div>
                <div class="navi-cookie-actions" style="margin-top: 20px;">
                    <button id="navi-cookie-btn-save-prefs" class="navi-cookie-btn navi-cookie-btn-accepter"><?php esc_html_e( 'Enregistrer mes choix', 'saito-navi' ); ?></button>
                    <button id="navi-cookie-btn-close-modal" class="navi-cookie-btn navi-cookie-btn-refuser"><?php esc_html_e( 'Annuler', 'saito-navi' ); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php
}
