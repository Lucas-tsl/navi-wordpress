<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Réglages partagés de Saito > Outils : supprimés seulement avec le dernier plugin Saito.
require __DIR__ . '/includes/saito-hub/uninstall.php';

$navi_options = array(
    // Activation/désactivation des modules (réglage central du hub)
    'navi_module_active_cookie-consent',
    'navi_module_active_accessibility',
    'navi_module_active_sticky-cart',
    'navi_module_active_calendar',
    'navi_module_active_stories',
    // Position du bouton flottant, langue forcée du plugin
    'navi_fab_position',
    'navi_language',
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
    'navi_sticky_selector_swatch',
    'navi_sticky_text_add_to_cart',
    'navi_sticky_text_out_of_stock',
    // Module Calendrier (Saito > Navi > Calendrier)
    'navi_calendar_id',
    // Module Stories (Navi > Stories)
    'navi_stories_auto_display',
    'navi_stories_show_label',
    'navi_stories_border_width',
    'navi_stories_color_bubble_border',
    'navi_stories_bubble_border_style',
    'navi_stories_bubble_gradient_angle',
    'navi_stories_bubble_gradient_color_1',
    'navi_stories_bubble_gradient_color_2',
    'navi_stories_bubble_gradient_color_3',
    'navi_stories_bubble_size',
    'navi_stories_color_phone_bg',
    'navi_stories_color_close_icon',
    'navi_stories_color_close_bg',
    'navi_stories_color_overlay',
    'navi_stories_phone_padding',
    'navi_stories_phone_width',
    'navi_stories_video_zoom',
    // Visibilité par appareil (un réglage par module)
    'navi_show_desktop_cookie-consent',
    'navi_show_mobile_cookie-consent',
    'navi_show_desktop_accessibility',
    'navi_show_mobile_accessibility',
    'navi_show_desktop_sticky-cart',
    'navi_show_mobile_sticky-cart',
    'navi_show_desktop_calendar',
    'navi_show_mobile_calendar',
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
