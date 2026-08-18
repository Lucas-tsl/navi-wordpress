<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$navi_options = array(
    // Activation/désactivation des modules (réglage central du hub)
    'navi_module_active_cookie-consent',
    'navi_module_active_accessibility',
    'navi_module_active_sticky-cart',
    'navi_module_active_stories',
    // Position du bouton flottant
    'navi_fab_position',
    // Apparence (Navi > Apparence)
    'navi_color_ink',
    'navi_color_ink_soft',
    'navi_radius_button',
    'navi_radius_image',
    // Module Cookie Consent
    'navi_cookie_logo_url',
    'navi_cookie_texte_banniere',
    'navi_cookie_url_politique',
    'navi_cookie_url_mentions',
    // Module Panier (Navi > Panier)
    'navi_sticky_selector_price',
    'navi_sticky_selector_name',
    'navi_sticky_selector_image',
    // Module Stories (Navi > Stories)
    'navi_stories_show_label',
    'navi_stories_border_width',
    'navi_stories_color_phone_bg',
    'navi_stories_color_close_icon',
    'navi_stories_color_close_bg',
    'navi_stories_color_overlay',
    'navi_stories_phone_padding',
    'navi_stories_phone_width',
    // Visibilité par appareil (un réglage par module)
    'navi_show_desktop_cookie-consent',
    'navi_show_mobile_cookie-consent',
    'navi_show_desktop_accessibility',
    'navi_show_mobile_accessibility',
    'navi_show_desktop_sticky-cart',
    'navi_show_mobile_sticky-cart',
    'navi_show_desktop_stories',
    'navi_show_mobile_stories',
);

foreach ( $navi_options as $navi_option ) {
    delete_option( $navi_option );
}

// Fichiers MP4 uploadés (wp_upload_dir()/navi-stories/) — pas de table à
// supprimer, le postmeta _navi_stories part avec chaque produit/le site.
if ( ! function_exists( 'navi_stories_uninstall_cleanup' ) ) {
    require_once __DIR__ . '/includes/modules/stories/data.php';
}
navi_stories_uninstall_cleanup();
