<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'navi_calendar_enregistrer_parametres' );
function navi_calendar_enregistrer_parametres() {
    register_setting( 'navi_calendar_options_group', 'navi_calendar_id', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0 ) );

    // Activation du module + visibilité par appareil (voir helpers.php).
    register_setting( 'navi_calendar_options_group', 'navi_module_active_calendar', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_calendar_options_group', 'navi_show_desktop_calendar', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_calendar_options_group', 'navi_show_mobile_calendar', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
}

// Contenu de l'onglet "Calendrier" (Saito > Navi).
function navi_calendar_render_settings_panel() {
    if ( ! navi_calendar_plugin_active() ) {
        ?>
        <div class="navi-admin-card">
            <p class="description">
                <?php esc_html_e( 'Ce module affiche un calendrier du plugin Saito Calendar Event dans le bouton flottant. Installez et activez Saito Calendar Event pour l\'utiliser.', 'saito-navi' ); ?>
            </p>
            <p><a class="button" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=saito-calendar-event&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Installer Saito Calendar Event', 'saito-navi' ); ?></a></p>
        </div>
        <?php
        return;
    }

    $calendars = get_posts(
        array(
            'post_type'      => SAITO_CE_POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        )
    );
    $current = (int) get_option( 'navi_calendar_id', 0 );
    ?>
    <form method="post" action="options.php">
        <?php settings_fields( 'navi_calendar_options_group' ); ?>
        <?php navi_render_hash_preserving_referer_field(); ?>
        <div class="navi-admin-card">
            <p class="description">
                <?php esc_html_e( 'Quand ce module est actif, le bouton flottant prend une icône de calendrier qui ouvre directement le calendrier choisi ci-dessous, sur toutes les pages qui ne l\'affichent pas déjà (sauf les fiches produit). Dans le pied de page, il redevient l\'engrenage des réglages.', 'saito-navi' ); ?>
            </p>
            <table class="form-table">
                <?php navi_render_module_active_field( 'calendar' ); ?>
                <tr valign="top">
                    <th scope="row"><label for="navi_calendar_id"><?php esc_html_e( 'Calendrier affiché', 'saito-navi' ); ?></label></th>
                    <td>
                        <select name="navi_calendar_id" id="navi_calendar_id">
                            <option value="0" <?php selected( 0, $current ); ?>><?php esc_html_e( 'Le plus récent', 'saito-navi' ); ?></option>
                            <?php foreach ( $calendars as $calendar ) : ?>
                                <option value="<?php echo esc_attr( $calendar->ID ); ?>" <?php selected( $calendar->ID, $current ); ?>><?php echo esc_html( $calendar->post_title ? $calendar->post_title : '#' . $calendar->ID ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'En dehors de sa période de publication (réglée dans le calendrier), l\'icône n\'apparaît pas.', 'saito-navi' ); ?></p>
                    </td>
                </tr>
                <?php navi_render_visibility_fields( 'calendar' ); ?>
            </table>
        </div>
        <?php submit_button(); ?>
    </form>
    <?php
}
