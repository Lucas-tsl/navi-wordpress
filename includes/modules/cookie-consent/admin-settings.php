<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'navi_cookie_enregistrer_parametres' );
function navi_cookie_enregistrer_parametres() {
    register_setting( 'navi_cookie_options_group', 'navi_cookie_logo_url', array( 'sanitize_callback' => 'esc_url_raw' ) );
    register_setting( 'navi_cookie_options_group', 'navi_cookie_texte_banniere', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
    register_setting( 'navi_cookie_options_group', 'navi_cookie_url_politique', array( 'sanitize_callback' => 'esc_url_raw' ) );
    register_setting( 'navi_cookie_options_group', 'navi_cookie_url_mentions', array( 'sanitize_callback' => 'esc_url_raw' ) );

    // Activation du module + visibilité par appareil (navi_render_module_active_field()/
    // navi_render_visibility_fields(), helpers.php) : enregistrées dans CE
    // groupe (pas le groupe central navi_modules_group) puisque le formulaire
    // qui les soumet (navi_cookie_render_settings_panel ci-dessous) utilise
    // settings_fields('navi_cookie_options_group').
    register_setting( 'navi_cookie_options_group', 'navi_module_active_cookie-consent', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_cookie_options_group', 'navi_show_desktop_cookie-consent', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_cookie_options_group', 'navi_show_mobile_cookie-consent', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
}

// Contenu de l'onglet "Cookies" (Navi > Navi) — plus de page dédiée séparée ;
// voir navi_render_dashboard_page() (admin-menu.php), qui l'appelle via
// $module['settings_panel_callback'] (class-navi-module-registry.php).
function navi_cookie_render_settings_panel() {
    $texte_defaut = navi_cookie_texte_par_defaut();
    ?>
    <form method="post" action="options.php">
        <?php settings_fields( 'navi_cookie_options_group' ); ?>
        <?php navi_render_hash_preserving_referer_field(); ?>
        <div class="navi-admin-card">
            <p class="description">
                <?php
                printf(
                    /* translators: 1: gtag, 2: gtag('consent', 'update', ...), 3: navi_cookie_consent_updated (nom d'événement dataLayer) */
                    esc_html__( "Bannière RGPD conforme au Google Consent Mode V2. À chaque choix du visiteur, si %1\$s est défini sur le site, le plugin appelle %2\$s ; il pousse aussi un événement %3\$s dans le dataLayer à chaque changement. Dans GTM : activez le Consent Mode (état par défaut refusé), et utilisez cet événement comme déclencheur personnalisé pour vos propres balises conditionnées au consentement.", 'saito-navi' ),
                    '<code>gtag</code>',
                    '<code>gtag(\'consent\', \'update\', …)</code>',
                    '<code>navi_cookie_consent_updated</code>'
                );
                ?>
            </p>
            <table class="form-table">
                <?php navi_render_module_active_field( 'cookie-consent' ); ?>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'URL du Logo', 'saito-navi' ); ?></th>
                    <td>
                        <input type="text" name="navi_cookie_logo_url" value="<?php echo esc_attr( get_option( 'navi_cookie_logo_url', '' ) ); ?>" placeholder="<?php echo esc_attr( navi_cookie_logo_url_par_defaut() ); ?>" class="regular-text" />
                        <p class="description">
                            <?php
                            $logo_site = navi_cookie_logo_url_par_defaut();
                            if ( ! empty( $logo_site ) ) {
                                esc_html_e( "Laisser vide pour utiliser automatiquement le logo du site (Apparence > Personnaliser > Identité du site).", 'saito-navi' );
                            } else {
                                esc_html_e( "Laisser vide si aucun logo ne doit apparaître. Le site n'a pas encore de logo configuré dans Apparence > Personnaliser > Identité du site — dès qu'il en aura un, il sera utilisé automatiquement ici.", 'saito-navi' );
                            }
                            ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top"><th scope="row"><?php esc_html_e( 'Texte de la bannière', 'saito-navi' ); ?></th><td><textarea name="navi_cookie_texte_banniere" rows="4" cols="60"><?php echo esc_textarea( get_option( 'navi_cookie_texte_banniere', $texte_defaut ) ); ?></textarea></td></tr>
                <tr valign="top"><th scope="row"><?php esc_html_e( 'URL Politique de confidentialité', 'saito-navi' ); ?></th><td><input type="text" name="navi_cookie_url_politique" value="<?php echo esc_attr( get_option( 'navi_cookie_url_politique' ) ); ?>" class="regular-text" /></td></tr>
                <tr valign="top"><th scope="row"><?php esc_html_e( 'URL Mentions légales', 'saito-navi' ); ?></th><td><input type="text" name="navi_cookie_url_mentions" value="<?php echo esc_attr( get_option( 'navi_cookie_url_mentions' ) ); ?>" class="regular-text" /></td></tr>
                <?php navi_render_visibility_fields( 'cookie-consent' ); ?>
            </table>
        </div>
        <?php submit_button(); ?>
    </form>
    <?php
}
