<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'navi_a11y_enregistrer_parametres' );
function navi_a11y_enregistrer_parametres() {
    // Visibilité par appareil (voir navi_render_visibility_fields, helpers.php) :
    // seul réglage propre à ce module en plus de l'activation — le reste
    // (langue, taille du texte, contraste, curseur) n'a pas d'option BO,
    // comportement toujours actif, mémorisé côté visiteur (localStorage,
    // assets/js/accessibility.js).
    register_setting( 'navi_a11y_options_group', 'navi_module_active_accessibility', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_a11y_options_group', 'navi_show_desktop_accessibility', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_a11y_options_group', 'navi_show_mobile_accessibility', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
}

// Contenu de l'onglet "Accessibilité" (Navi > Navi) — voir
// navi_cookie_render_settings_panel() (cookie-consent/admin-settings.php)
// pour le patron général.
function navi_a11y_render_settings_panel() {
    ?>
    <form method="post" action="options.php">
        <?php settings_fields( 'navi_a11y_options_group' ); ?>
        <?php navi_render_hash_preserving_referer_field(); ?>
        <div class="navi-admin-card">
            <p class="description"><?php esc_html_e( 'Langue, taille du texte, contraste et curseur restent toujours actifs pour les visiteurs — seule leur visibilité par appareil se règle ici.', 'saito-navi' ); ?></p>
            <table class="form-table">
                <?php navi_render_module_active_field( 'accessibility' ); ?>
                <?php navi_render_visibility_fields( 'accessibility' ); ?>
            </table>
        </div>
        <?php submit_button(); ?>
    </form>
    <?php
}
