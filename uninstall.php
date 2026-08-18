<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$navi_options = array(
    // Activation/désactivation des modules (réglage central du hub)
    'navi_module_active_cookie-consent',
    'navi_module_active_accessibility',
    'navi_module_active_sticky-cart',
    // Position du bouton flottant
    'navi_fab_position',
    // Couleurs de la DA (Navi > Couleurs)
    'navi_color_ink',
    'navi_color_ink_soft',
    // Module Cookie Consent
    'navi_cookie_logo_url',
    'navi_cookie_texte_banniere',
    'navi_cookie_url_politique',
    'navi_cookie_url_mentions',
    // Module Panier (Navi > Panier)
    'navi_sticky_selector_price',
    'navi_sticky_selector_name',
    'navi_sticky_selector_image',
);

foreach ( $navi_options as $navi_option ) {
    delete_option( $navi_option );
}
