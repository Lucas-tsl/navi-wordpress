<?php
if ( ! defined( 'ABSPATH' ) ) exit;

Navi_Module_Registry::register(
    'accessibility',
    array(
        'label'           => __( 'Accessibilité', 'saito-navi' ),
        'short_label'     => __( 'Accessibilité', 'saito-navi' ),
        'icon'            => '♿',
        'icon_svg'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="4" r="1.5" fill="currentColor" stroke="none"></circle><path d="M11 6v6h5"></path><path d="M9 12l4 2 3 6"></path><circle cx="9" cy="16" r="5"></circle></svg>',
        'description'     => __( 'Langue (via WPML, ou GTranslate si présent sur la page), taille du texte, contraste élevé, curseur agrandi et soulignage des liens.', 'saito-navi' ),
        'option_name'     => 'navi_module_active_accessibility',
        'default_active'  => true,
        'fab_action'      => 'open-accessibility-panel',
        'fab_condition'   => '',
        'available'       => true,
        'settings_url'    => admin_url( 'admin.php?page=navi-main#accessibility' ),
        'settings_panel_callback' => 'navi_a11y_render_settings_panel',
        'visibility_selector' => '.navi-fab-item[data-item-id="accessibility"]',
    )
);

// Chargé même si le module est désactivé : sinon son onglet de réglages
// (Navi > Navi > Accessibilité) disparaîtrait avec lui, sans aucun moyen de
// le réactiver depuis le BO (voir navi_a11y_render_settings_panel()).
if ( is_admin() ) {
    require_once __DIR__ . '/admin-settings.php';
}

if ( Navi_Module_Registry::is_active( 'accessibility' ) ) {
    require_once __DIR__ . '/public-display.php';

    add_action( 'wp_enqueue_scripts', 'navi_a11y_enqueue_assets' );
    function navi_a11y_enqueue_assets() {
        // Les libellés sont déjà rendus côté serveur dans public-display.php ;
        // le JS n'a besoin d'aucune chaîne traduite, seulement du comportement.
        navi_enqueue_style( 'navi-a11y-css', NAVI_PLUGIN_URL . 'assets/css/accessibility.css', array(), NAVI_VERSION );
        navi_enqueue_script( 'navi-a11y-js', NAVI_PLUGIN_URL . 'assets/js/accessibility.js', array(), NAVI_VERSION, true );
    }
}
