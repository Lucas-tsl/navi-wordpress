<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/appearance.php';

Navi_Module_Registry::register(
    'stories',
    array(
        'label'           => __( 'Stories', 'saito-navi' ),
        'short_label'     => __( 'Stories', 'saito-navi' ),
        'icon'            => '▶️',
        'icon_svg'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>',
        'description'     => __( 'Bulles vidéo "stories" sur la fiche produit, jusqu\'à 4 par produit (YouTube ou vidéo MP4 importée).', 'saito-navi' ),
        'option_name'     => 'navi_module_active_stories',
        'default_active'  => true,
        // Pas d'icône dans le menu du FAB : les bulles s'affichent déjà sur
        // la fiche produit elle-même (voir public-display.php), pas besoin
        // d'un point d'entrée supplémentaire dans le menu — même logique
        // que le panier sticky avant l'ajout de son icône de réouverture
        // (ici, rouvrir une story fermée n'a pas de sens, contrairement au
        // panier qu'on peut vouloir rouvrir après une fermeture manuelle).
        'fab_action'      => '',
        'fab_condition'   => '',
        'available'       => true,
        'settings_url'    => admin_url( 'admin.php?page=navi-main#stories' ),
        'settings_panel_callback' => 'navi_stories_render_settings_panel',
        'visibility_selector' => '.navi-story-row',
    )
);

// Chargé même si le module est désactivé : sinon son onglet de réglages
// (Navi > Navi > Stories) disparaîtrait avec lui, sans aucun moyen de le
// réactiver depuis le BO (voir navi_stories_render_settings_panel()).
if ( is_admin() ) {
    require_once __DIR__ . '/admin-settings.php';
}

if ( Navi_Module_Registry::is_active( 'stories' ) ) {
    if ( is_admin() ) {
        require_once __DIR__ . '/admin-product-tab.php';
    } else {
        require_once __DIR__ . '/public-display.php';
        require_once __DIR__ . '/stories-frontend.php';
    }
}
