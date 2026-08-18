<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'navi_a11y_ajouter_menu' );
function navi_a11y_ajouter_menu() {
    add_submenu_page(
        navi_admin_parent_slug(),
        __( 'Réglages Accessibilité', 'navi' ),
        __( 'Accessibilité', 'navi' ),
        'manage_options',
        'navi-accessibility',
        'navi_a11y_page_reglages_html'
    );
}

add_action( 'admin_init', 'navi_a11y_enregistrer_parametres' );
function navi_a11y_enregistrer_parametres() {
    // Visibilité par appareil (voir navi_render_visibility_fields, helpers.php) :
    // seul réglage propre à ce module pour l'instant, le reste (langue,
    // taille du texte, contraste, curseur) n'a pas d'option BO — comportement
    // toujours actif, mémorisé côté visiteur (localStorage, assets/js/accessibility.js).
    register_setting( 'navi_a11y_options_group', 'navi_show_desktop_accessibility', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_a11y_options_group', 'navi_show_mobile_accessibility', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
}

function navi_a11y_page_reglages_html() {
    if ( ! navi_user_can_manage() ) {
        wp_die( esc_html__( "Vous n'avez pas les permissions nécessaires pour accéder à cette page.", 'navi' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Réglages Accessibilité', 'navi' ); ?></h1>
        <p class="description"><?php esc_html_e( 'Langue, taille du texte, contraste et curseur restent toujours actifs pour les visiteurs — seule leur visibilité par appareil se règle ici.', 'navi' ); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields( 'navi_a11y_options_group' ); ?>
            <table class="form-table">
                <?php navi_render_visibility_fields( 'accessibility' ); ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
