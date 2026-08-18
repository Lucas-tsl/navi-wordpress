<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'navi_stories_ajouter_menu' );
function navi_stories_ajouter_menu() {
    add_submenu_page(
        navi_admin_parent_slug(),
        __( 'Réglages Stories', 'navi' ),
        __( 'Stories', 'navi' ),
        'manage_options',
        'navi-stories',
        'navi_stories_page_reglages_html'
    );
}

add_action( 'admin_init', 'navi_stories_enregistrer_parametres' );
function navi_stories_enregistrer_parametres() {
    register_setting( 'navi_stories_options_group', 'navi_stories_show_label', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_auto_display', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_border_width', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_story_border_width', 'default' => NAVI_STORIES_DEFAULT_BORDER_WIDTH ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_color_phone_bg', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_color_close_icon', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_color_close_bg', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_color_overlay', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_phone_padding', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_story_phone_padding', 'default' => NAVI_STORIES_DEFAULT_PHONE_PADDING ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_phone_width', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_story_phone_width', 'default' => NAVI_STORIES_DEFAULT_PHONE_WIDTH ) );

    // Visibilité par appareil (voir navi_render_visibility_fields, helpers.php).
    register_setting( 'navi_stories_options_group', 'navi_show_desktop_stories', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_stories_options_group', 'navi_show_mobile_stories', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
}

// Sélecteurs de couleur natifs WordPress (wp-color-picker), uniquement sur
// cette page de réglages.
add_action( 'admin_enqueue_scripts', 'navi_stories_enqueue_color_picker_assets' );
function navi_stories_enqueue_color_picker_assets( $hook_suffix ) {
    if ( 'navi_page_navi-stories' !== $hook_suffix ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_add_inline_script(
        'wp-color-picker',
        "jQuery(function($){ $('.navi-color-picker').wpColorPicker(); });"
    );
}

function navi_stories_page_reglages_html() {
    if ( ! navi_user_can_manage() ) {
        wp_die( esc_html__( "Vous n'avez pas les permissions nécessaires pour accéder à cette page.", 'navi' ) );
    }
    $padding = navi_stories_phone_padding();
    $width   = navi_stories_phone_width();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Réglages Stories', 'navi' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'navi_stories_options_group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Afficher automatiquement après la galerie produit', 'navi' ); ?></th>
                    <td>
                        <input type="hidden" name="navi_stories_auto_display" value="0" />
                        <input type="checkbox" name="navi_stories_auto_display" value="1" <?php checked( navi_stories_auto_display() ); ?> />
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %s: nom du shortcode entre crochets, ex. [navi_stories] */
                                esc_html__( 'Décocher pour positionner les bulles vous-même via le shortcode %s (dans le contenu, un constructeur de page, ou un template de thème) plutôt qu\'automatiquement après les images du produit.', 'navi' ),
                                '<code>[navi_stories]</code>'
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Afficher le titre de la bulle', 'navi' ); ?></th>
                    <td>
                        <input type="hidden" name="navi_stories_show_label" value="0" />
                        <input type="checkbox" name="navi_stories_show_label" value="1" <?php checked( navi_stories_show_label() ); ?> />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="navi_stories_border_width"><?php esc_html_e( 'Épaisseur de la bordure (px)', 'navi' ); ?></label></th>
                    <td>
                        <input type="number" name="navi_stories_border_width" id="navi_stories_border_width" min="0" max="20" value="<?php echo esc_attr( navi_stories_border_width() ); ?>" class="small-text" /> px
                        <p class="description"><?php esc_html_e( '0 = pas de bordure.', 'navi' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="navi_stories_color_phone_bg"><?php esc_html_e( 'Couleur du fond du mockup téléphone', 'navi' ); ?></label></th>
                    <td><input type="text" name="navi_stories_color_phone_bg" id="navi_stories_color_phone_bg" class="navi-color-picker" value="<?php echo esc_attr( navi_stories_color_phone_bg() ); ?>" data-default-color="<?php echo esc_attr( NAVI_STORIES_DEFAULT_PHONE_BG ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="navi_stories_color_close_icon"><?php esc_html_e( 'Couleur de la croix (icône)', 'navi' ); ?></label></th>
                    <td><input type="text" name="navi_stories_color_close_icon" id="navi_stories_color_close_icon" class="navi-color-picker" value="<?php echo esc_attr( navi_stories_color_close_icon() ); ?>" data-default-color="<?php echo esc_attr( NAVI_STORIES_DEFAULT_CLOSE_ICON ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="navi_stories_color_close_bg"><?php esc_html_e( 'Couleur du fond du bouton de fermeture', 'navi' ); ?></label></th>
                    <td><input type="text" name="navi_stories_color_close_bg" id="navi_stories_color_close_bg" class="navi-color-picker" value="<?php echo esc_attr( navi_stories_color_close_bg() ); ?>" data-default-color="<?php echo esc_attr( NAVI_STORIES_DEFAULT_CLOSE_BG ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="navi_stories_color_overlay"><?php esc_html_e( 'Couleur du fond plein écran (mobile)', 'navi' ); ?></label></th>
                    <td><input type="text" name="navi_stories_color_overlay" id="navi_stories_color_overlay" class="navi-color-picker" value="<?php echo esc_attr( navi_stories_color_overlay() ); ?>" data-default-color="<?php echo esc_attr( NAVI_STORIES_DEFAULT_OVERLAY_BG ); ?>" /></td>
                </tr>
                <?php navi_render_visibility_fields( 'stories' ); ?>
            </table>

            <h2><?php esc_html_e( 'Aspect du mockup', 'navi' ); ?></h2>
            <p class="description"><?php esc_html_e( "Épaisseur du cadre autour de l'écran vidéo et taille du mockup de téléphone (panneau desktop/laptop/tablette).", 'navi' ); ?></p>
            <div style="display:flex; gap:32px; flex-wrap:wrap; align-items:flex-start; margin-top:16px;">
                <div style="flex:1; min-width:260px;">
                    <p>
                        <label for="navi_phone_padding_range"><?php esc_html_e( "Épaisseur du cadre autour de l'écran", 'navi' ); ?></label>
                        (<output id="navi_phone_padding_output"><?php echo esc_html( $padding ); ?></output> px)
                    </p>
                    <input type="range" id="navi_phone_padding_range" name="navi_stories_phone_padding"
                           min="0" max="<?php echo esc_attr( NAVI_STORIES_MAX_PHONE_PADDING ); ?>" step="2"
                           value="<?php echo esc_attr( $padding ); ?>" style="width:100%;" />

                    <p style="margin-top:20px;">
                        <label for="navi_phone_width_range"><?php esc_html_e( 'Taille du mockup de téléphone', 'navi' ); ?></label>
                        (<output id="navi_phone_width_output"><?php echo esc_html( $width ); ?></output> px)
                    </p>
                    <input type="range" id="navi_phone_width_range" name="navi_stories_phone_width"
                           min="<?php echo esc_attr( NAVI_STORIES_MIN_PHONE_WIDTH ); ?>" max="<?php echo esc_attr( NAVI_STORIES_MAX_PHONE_WIDTH ); ?>" step="10"
                           value="<?php echo esc_attr( $width ); ?>" style="width:100%;" />
                </div>

                <div style="flex:0 0 220px; display:flex; align-items:center; justify-content:center; min-height:260px; padding:20px; background:#f6f6f6; border-radius:4px;">
                    <div id="naviPreviewPhone" style="position:relative; aspect-ratio:9/18.5; background:#111; border-radius:34px; box-sizing:border-box; box-shadow:0 10px 30px rgba(0,0,0,.25); transition:width .1s ease, padding .1s ease; width:<?php echo esc_attr( $width ); ?>px; padding:<?php echo esc_attr( $padding ); ?>px;">
                        <div style="width:100%; height:100%; border-radius:24px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#000;">
                            <span style="color:#fff; font-size:.8125rem; font-family:sans-serif; opacity:.6;"><?php esc_html_e( 'Vidéo', 'navi' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        (function () {
            var paddingRange = document.getElementById('navi_phone_padding_range');
            var widthRange = document.getElementById('navi_phone_width_range');
            var paddingOutput = document.getElementById('navi_phone_padding_output');
            var widthOutput = document.getElementById('navi_phone_width_output');
            var previewPhone = document.getElementById('naviPreviewPhone');
            if (!paddingRange || !widthRange || !previewPhone) return;

            paddingRange.addEventListener('input', function () {
                paddingOutput.textContent = paddingRange.value;
                previewPhone.style.padding = paddingRange.value + 'px';
            });
            widthRange.addEventListener('input', function () {
                widthOutput.textContent = widthRange.value;
                previewPhone.style.width = widthRange.value + 'px';
            });
        })();
    </script>
    <?php
}
