<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'navi_cookie_ajouter_menu' );
function navi_cookie_ajouter_menu() {
    add_submenu_page(
        navi_admin_parent_slug(),
        __( 'Réglages Bannière Cookie', 'navi' ),
        __( 'Cookies', 'navi' ),
        'manage_options',
        'navi-cookie-consent',
        'navi_cookie_page_reglages_html'
    );
}

add_action( 'admin_init', 'navi_cookie_enregistrer_parametres' );
function navi_cookie_enregistrer_parametres() {
    register_setting( 'navi_cookie_options_group', 'navi_cookie_logo_url', array( 'sanitize_callback' => 'esc_url_raw' ) );
    register_setting( 'navi_cookie_options_group', 'navi_cookie_texte_banniere', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
    register_setting( 'navi_cookie_options_group', 'navi_cookie_url_politique', array( 'sanitize_callback' => 'esc_url_raw' ) );
    register_setting( 'navi_cookie_options_group', 'navi_cookie_url_mentions', array( 'sanitize_callback' => 'esc_url_raw' ) );

    // Visibilité par appareil (voir navi_render_visibility_fields, helpers.php) :
    // enregistrée dans CE groupe (pas le groupe central navi_modules_group)
    // puisque le formulaire qui les soumet (navi_cookie_page_reglages_html
    // ci-dessous) utilise settings_fields('navi_cookie_options_group').
    register_setting( 'navi_cookie_options_group', 'navi_show_desktop_cookie-consent', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_cookie_options_group', 'navi_show_mobile_cookie-consent', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
}

function navi_cookie_page_reglages_html() {
    if ( ! navi_user_can_manage() ) {
        wp_die( esc_html__( "Vous n'avez pas les permissions nécessaires pour accéder à cette page.", 'navi' ) );
    }
    $texte_defaut = navi_cookie_texte_par_defaut();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configuration de la Bannière Cookie (GTM Edition)', 'navi' ); ?></h1>
        <p><em><?php esc_html_e( 'Note : ce module communique directement avec Google Tag Manager via le Google Consent Mode V2.', 'navi' ); ?></em></p>
        <form method="post" action="options.php">
            <?php settings_fields( 'navi_cookie_options_group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'URL du Logo', 'navi' ); ?></th>
                    <td>
                        <input type="text" name="navi_cookie_logo_url" value="<?php echo esc_attr( get_option( 'navi_cookie_logo_url', '' ) ); ?>" placeholder="<?php echo esc_attr( navi_cookie_logo_url_par_defaut() ); ?>" class="regular-text" />
                        <p class="description">
                            <?php
                            $logo_site = navi_cookie_logo_url_par_defaut();
                            if ( ! empty( $logo_site ) ) {
                                esc_html_e( "Laisser vide pour utiliser automatiquement le logo du site (Apparence > Personnaliser > Identité du site).", 'navi' );
                            } else {
                                esc_html_e( "Laisser vide si aucun logo ne doit apparaître. Le site n'a pas encore de logo configuré dans Apparence > Personnaliser > Identité du site — dès qu'il en aura un, il sera utilisé automatiquement ici.", 'navi' );
                            }
                            ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top"><th scope="row"><?php esc_html_e( 'Texte de la bannière', 'navi' ); ?></th><td><textarea name="navi_cookie_texte_banniere" rows="4" cols="60"><?php echo esc_textarea( get_option( 'navi_cookie_texte_banniere', $texte_defaut ) ); ?></textarea></td></tr>
                <tr valign="top"><th scope="row"><?php esc_html_e( 'URL Politique de confidentialité', 'navi' ); ?></th><td><input type="text" name="navi_cookie_url_politique" value="<?php echo esc_attr( get_option( 'navi_cookie_url_politique' ) ); ?>" class="regular-text" /></td></tr>
                <tr valign="top"><th scope="row"><?php esc_html_e( 'URL Mentions légales', 'navi' ); ?></th><td><input type="text" name="navi_cookie_url_mentions" value="<?php echo esc_attr( get_option( 'navi_cookie_url_mentions' ) ); ?>" class="regular-text" /></td></tr>
                <?php navi_render_visibility_fields( 'cookie-consent' ); ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
