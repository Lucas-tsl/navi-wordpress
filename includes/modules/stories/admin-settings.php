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
    register_setting( 'navi_stories_options_group', 'navi_stories_color_bubble_border', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_bubble_border_style', array( 'type' => 'string', 'sanitize_callback' => 'navi_sanitize_story_border_style', 'default' => NAVI_STORIES_DEFAULT_BUBBLE_BORDER_STYLE ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_bubble_gradient_angle', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_story_gradient_angle', 'default' => NAVI_STORIES_DEFAULT_GRADIENT_ANGLE ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_bubble_gradient_color_1', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_bubble_gradient_color_2', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_bubble_gradient_color_3', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_bubble_size', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_story_bubble_size', 'default' => NAVI_STORIES_DEFAULT_BUBBLE_SIZE ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_color_phone_bg', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_color_close_icon', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_color_close_bg', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_color_overlay', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_hex_color' ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_phone_padding', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_story_phone_padding', 'default' => NAVI_STORIES_DEFAULT_PHONE_PADDING ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_phone_width', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_story_phone_width', 'default' => NAVI_STORIES_DEFAULT_PHONE_WIDTH ) );
    register_setting( 'navi_stories_options_group', 'navi_stories_video_zoom', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_story_video_zoom', 'default' => NAVI_STORIES_DEFAULT_VIDEO_ZOOM ) );

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
    $padding        = navi_stories_phone_padding();
    $width          = navi_stories_phone_width();
    $videoZoom      = navi_stories_video_zoom();
    $borderWidth    = navi_stories_border_width();
    $borderStyle    = navi_stories_bubble_border_style();
    $bubbleBorder   = navi_stories_color_bubble_border();
    $gradientAngle  = navi_stories_bubble_gradient_angle();
    $gradientColor1 = navi_stories_bubble_gradient_color_1();
    $gradientColor2 = navi_stories_bubble_gradient_color_2();
    $gradientColor3 = navi_stories_bubble_gradient_color_3();
    $bubbleSize     = navi_stories_bubble_size();
    // Couleur par défaut réelle de la bordure unie quand aucune n'est
    // réglée : --navi-color-accent (assets/css/core.css), jamais réglable
    // depuis Navi > Apparence pour le moment — sert uniquement d'indication
    // dans le sélecteur de couleur (le CSS applique ce même repli).
    $bubbleBorderDefault = '#2563eb';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Réglages Stories', 'navi' ); ?></h1>

        <h2 class="nav-tab-wrapper" id="navi-stories-tabs">
            <a href="#bulles" class="nav-tab nav-tab-active" data-tab="bulles"><?php esc_html_e( 'Bulles', 'navi' ); ?></a>
            <a href="#mockup" class="nav-tab" data-tab="mockup"><?php esc_html_e( 'Mockup', 'navi' ); ?></a>
        </h2>

        <form method="post" action="options.php">
            <?php settings_fields( 'navi_stories_options_group' ); ?>

            <div class="navi-stories-tab-panel" data-tab-panel="bulles">
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
                    <?php navi_render_visibility_fields( 'stories' ); ?>
                </table>

                <h2><?php esc_html_e( 'Aspect de la bulle', 'navi' ); ?></h2>
                <div style="display:flex; gap:32px; flex-wrap:wrap; align-items:flex-start; margin-top:16px;">
                    <div style="flex:1; min-width:260px;">
                        <p>
                            <label for="navi_bubble_border_range"><?php esc_html_e( 'Épaisseur de la bordure', 'navi' ); ?></label>
                            (<output id="navi_bubble_border_output"><?php echo esc_html( $borderWidth ); ?></output> px)
                        </p>
                        <input type="range" id="navi_bubble_border_range" name="navi_stories_border_width"
                               min="0" max="20" step="1"
                               value="<?php echo esc_attr( $borderWidth ); ?>" style="width:100%;" />

                        <p style="margin-top:20px;">
                            <label for="navi_bubble_border_style"><?php esc_html_e( 'Type de bordure', 'navi' ); ?></label>
                        </p>
                        <select id="navi_bubble_border_style" name="navi_stories_bubble_border_style">
                            <option value="gradient" <?php selected( 'gradient', $borderStyle ); ?>><?php esc_html_e( 'Dégradé', 'navi' ); ?></option>
                            <option value="solid" <?php selected( 'solid', $borderStyle ); ?>><?php esc_html_e( 'Couleur unie', 'navi' ); ?></option>
                        </select>

                        <div id="navi_bubble_border_solid_fields" <?php echo 'solid' === $borderStyle ? '' : 'style="display:none;"'; ?>>
                            <p style="margin-top:20px;">
                                <label for="navi_stories_color_bubble_border"><?php esc_html_e( 'Couleur de la bordure', 'navi' ); ?></label>
                            </p>
                            <input type="text" name="navi_stories_color_bubble_border" id="navi_stories_color_bubble_border" class="navi-color-picker" value="<?php echo esc_attr( $bubbleBorder ); ?>" data-default-color="<?php echo esc_attr( $bubbleBorderDefault ); ?>" />
                            <p class="description"><?php esc_html_e( 'Vide = couleur d\'accent du bouton flottant.', 'navi' ); ?></p>
                        </div>

                        <div id="navi_bubble_border_gradient_fields" <?php echo 'gradient' === $borderStyle ? '' : 'style="display:none;"'; ?>>
                            <p style="margin-top:20px;">
                                <label for="navi_bubble_gradient_angle_range"><?php esc_html_e( 'Angle du dégradé', 'navi' ); ?></label>
                                (<output id="navi_bubble_gradient_angle_output"><?php echo esc_html( $gradientAngle ); ?></output>°)
                            </p>
                            <input type="range" id="navi_bubble_gradient_angle_range" name="navi_stories_bubble_gradient_angle"
                                   min="0" max="360" step="5"
                                   value="<?php echo esc_attr( $gradientAngle ); ?>" style="width:100%;" />

                            <p style="margin-top:16px; display:flex; gap:16px; flex-wrap:wrap;">
                                <span>
                                    <label for="navi_stories_bubble_gradient_color_1"><?php esc_html_e( 'Couleur 1', 'navi' ); ?></label><br />
                                    <input type="text" name="navi_stories_bubble_gradient_color_1" id="navi_stories_bubble_gradient_color_1" class="navi-color-picker navi-gradient-color" value="<?php echo esc_attr( $gradientColor1 ); ?>" data-default-color="<?php echo esc_attr( NAVI_STORIES_DEFAULT_GRADIENT_COLOR_1 ); ?>" />
                                </span>
                                <span>
                                    <label for="navi_stories_bubble_gradient_color_2"><?php esc_html_e( 'Couleur 2', 'navi' ); ?></label><br />
                                    <input type="text" name="navi_stories_bubble_gradient_color_2" id="navi_stories_bubble_gradient_color_2" class="navi-color-picker navi-gradient-color" value="<?php echo esc_attr( $gradientColor2 ); ?>" data-default-color="<?php echo esc_attr( NAVI_STORIES_DEFAULT_GRADIENT_COLOR_2 ); ?>" />
                                </span>
                                <span>
                                    <label for="navi_stories_bubble_gradient_color_3"><?php esc_html_e( 'Couleur 3', 'navi' ); ?></label><br />
                                    <input type="text" name="navi_stories_bubble_gradient_color_3" id="navi_stories_bubble_gradient_color_3" class="navi-color-picker navi-gradient-color" value="<?php echo esc_attr( $gradientColor3 ); ?>" data-default-color="<?php echo esc_attr( NAVI_STORIES_DEFAULT_GRADIENT_COLOR_3 ); ?>" />
                                </span>
                            </p>
                            <p class="description"><?php esc_html_e( 'Réglage par défaut : anneau dégradé sombre/clair/sombre à 45°.', 'navi' ); ?></p>
                        </div>

                        <p style="margin-top:20px;">
                            <label for="navi_bubble_size_range"><?php esc_html_e( 'Taille de la bulle', 'navi' ); ?></label>
                            (<output id="navi_bubble_size_output"><?php echo esc_html( $bubbleSize ); ?></output> px)
                        </p>
                        <input type="range" id="navi_bubble_size_range" name="navi_stories_bubble_size"
                               min="<?php echo esc_attr( NAVI_STORIES_MIN_BUBBLE_SIZE ); ?>" max="<?php echo esc_attr( NAVI_STORIES_MAX_BUBBLE_SIZE ); ?>" step="4"
                               value="<?php echo esc_attr( $bubbleSize ); ?>" style="width:100%;" />
                    </div>

                    <div style="flex:0 0 220px; display:flex; align-items:center; justify-content:center; min-height:180px; padding:20px; background:#f6f6f6; border-radius:4px;">
                        <div id="naviPreviewBubble" style="border-radius:50%; box-sizing:border-box; transition:width .1s ease, height .1s ease, border-width .1s ease; width:<?php echo esc_attr( $bubbleSize ); ?>px; height:<?php echo esc_attr( $bubbleSize ); ?>px; border:<?php echo esc_attr( $borderWidth ); ?>px solid transparent; background:linear-gradient(#000,#000) padding-box, <?php echo esc_attr( navi_stories_bubble_border_css_value() ); ?> border-box;"></div>
                    </div>
                </div>
            </div>

            <div class="navi-stories-tab-panel" data-tab-panel="mockup" style="display:none;">
                <table class="form-table">
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

                        <p style="margin-top:20px;">
                            <label for="navi_video_zoom_range"><?php esc_html_e( 'Zoom de la vidéo', 'navi' ); ?></label>
                            (<output id="navi_video_zoom_output"><?php echo esc_html( $videoZoom ); ?></output> %)
                        </p>
                        <input type="range" id="navi_video_zoom_range" name="navi_stories_video_zoom"
                               min="<?php echo esc_attr( NAVI_STORIES_MIN_VIDEO_ZOOM ); ?>" max="<?php echo esc_attr( NAVI_STORIES_MAX_VIDEO_ZOOM ); ?>" step="1"
                               value="<?php echo esc_attr( $videoZoom ); ?>" style="width:100%;" />
                        <p class="description">
                            <?php esc_html_e( 'YouTube affiche parfois son propre bandeau titre/chaîne et son filigrane "Shorts" par-dessus la vidéo (surtout au chargement et à la fin), sans moyen de le retirer autrement. Zoomer la vidéo le pousse hors du cadre visible, au prix d\'un léger recadrage sur les côtés. 100 % = aucun recadrage.', 'navi' ); ?>
                        </p>
                    </div>

                    <div style="flex:0 0 220px; display:flex; align-items:center; justify-content:center; min-height:260px; padding:20px; background:#f6f6f6; border-radius:4px;">
                        <div id="naviPreviewPhone" style="position:relative; aspect-ratio:9/18.5; background:#111; border-radius:34px; box-sizing:border-box; box-shadow:0 10px 30px rgba(0,0,0,.25); transition:width .1s ease, padding .1s ease; width:<?php echo esc_attr( $width ); ?>px; padding:<?php echo esc_attr( $padding ); ?>px;">
                            <div style="width:100%; height:100%; border-radius:24px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#000;">
                                <div id="naviPreviewVideo" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; transition:transform .1s ease; transform:scale(<?php echo esc_attr( $videoZoom / 100 ); ?>);">
                                    <span style="color:#fff; font-size:.8125rem; font-family:sans-serif; opacity:.6;"><?php esc_html_e( 'Vidéo', 'navi' ); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        (function () {
            // Onglets : pas de rechargement de page, un seul formulaire pour
            // les deux onglets (mêmes options_group) — juste un
            // afficher/masquer, l'ancre #bulles/#mockup permet de rouvrir
            // le bon onglet après enregistrement (redirection options.php).
            var tabs = document.querySelectorAll('#navi-stories-tabs .nav-tab');
            var panels = document.querySelectorAll('.navi-stories-tab-panel');
            function activateTab(name) {
                tabs.forEach(function (tab) {
                    tab.classList.toggle('nav-tab-active', tab.dataset.tab === name);
                });
                panels.forEach(function (panel) {
                    panel.style.display = panel.dataset.tabPanel === name ? '' : 'none';
                });
            }
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    activateTab(tab.dataset.tab);
                    window.location.hash = tab.dataset.tab;
                });
            });
            var initial = window.location.hash.replace('#', '');
            if (initial === 'mockup') activateTab('mockup');

            var borderRange = document.getElementById('navi_bubble_border_range');
            var borderOutput = document.getElementById('navi_bubble_border_output');
            var sizeRange = document.getElementById('navi_bubble_size_range');
            var sizeOutput = document.getElementById('navi_bubble_size_output');
            var styleSelect = document.getElementById('navi_bubble_border_style');
            var solidFields = document.getElementById('navi_bubble_border_solid_fields');
            var gradientFields = document.getElementById('navi_bubble_border_gradient_fields');
            var colorInput = document.getElementById('navi_stories_color_bubble_border');
            var angleRange = document.getElementById('navi_bubble_gradient_angle_range');
            var angleOutput = document.getElementById('navi_bubble_gradient_angle_output');
            var gradientColorInputs = document.querySelectorAll('.navi-gradient-color');
            var previewBubble = document.getElementById('naviPreviewBubble');

            function currentBorderBg() {
                if (styleSelect && 'solid' === styleSelect.value) {
                    return colorInput && colorInput.value ? colorInput.value : '<?php echo esc_js( $bubbleBorderDefault ); ?>';
                }
                var stops = Array.prototype.map.call(gradientColorInputs, function (input) {
                    return input.value || input.getAttribute('data-default-color');
                });
                var angle = angleRange ? angleRange.value : <?php echo (int) NAVI_STORIES_DEFAULT_GRADIENT_ANGLE; ?>;
                return 'linear-gradient(' + angle + 'deg, ' + stops.join(', ') + ')';
            }

            function updateBubblePreview() {
                if (!previewBubble) return;
                previewBubble.style.borderWidth = borderRange.value + 'px';
                previewBubble.style.width = sizeRange.value + 'px';
                previewBubble.style.height = sizeRange.value + 'px';
                previewBubble.style.background = 'linear-gradient(#000,#000) padding-box, ' + currentBorderBg() + ' border-box';
            }

            if (styleSelect && solidFields && gradientFields) {
                styleSelect.addEventListener('change', function () {
                    var isSolid = 'solid' === styleSelect.value;
                    solidFields.style.display = isSolid ? '' : 'none';
                    gradientFields.style.display = isSolid ? 'none' : '';
                    updateBubblePreview();
                });
            }

            if (borderRange && sizeRange && previewBubble) {
                borderRange.addEventListener('input', function () {
                    borderOutput.textContent = borderRange.value;
                    updateBubblePreview();
                });
                sizeRange.addEventListener('input', function () {
                    sizeOutput.textContent = sizeRange.value;
                    updateBubblePreview();
                });
                if (angleRange) {
                    angleRange.addEventListener('input', function () {
                        angleOutput.textContent = angleRange.value;
                        updateBubblePreview();
                    });
                }
                // wp-color-picker remplace l'input par un widget iris ; son
                // événement 'change' (natif, redéclenché par iris à chaque
                // sélection) suffit à capter les mises à jour sans dépendre
                // de l'API jQuery interne du color picker.
                if (colorInput) {
                    colorInput.addEventListener('change', updateBubblePreview);
                    jQuery(colorInput).on('irischange', updateBubblePreview);
                }
                gradientColorInputs.forEach(function (input) {
                    input.addEventListener('change', updateBubblePreview);
                    jQuery(input).on('irischange', updateBubblePreview);
                });
            }

            var paddingRange = document.getElementById('navi_phone_padding_range');
            var widthRange = document.getElementById('navi_phone_width_range');
            var paddingOutput = document.getElementById('navi_phone_padding_output');
            var widthOutput = document.getElementById('navi_phone_width_output');
            var previewPhone = document.getElementById('naviPreviewPhone');
            var zoomRange = document.getElementById('navi_video_zoom_range');
            var zoomOutput = document.getElementById('navi_video_zoom_output');
            var previewVideo = document.getElementById('naviPreviewVideo');
            if (!paddingRange || !widthRange || !previewPhone) return;

            paddingRange.addEventListener('input', function () {
                paddingOutput.textContent = paddingRange.value;
                previewPhone.style.padding = paddingRange.value + 'px';
            });
            widthRange.addEventListener('input', function () {
                widthOutput.textContent = widthRange.value;
                previewPhone.style.width = widthRange.value + 'px';
            });
            if (zoomRange && previewVideo) {
                zoomRange.addEventListener('input', function () {
                    zoomOutput.textContent = zoomRange.value;
                    previewVideo.style.transform = 'scale(' + (zoomRange.value / 100) + ')';
                });
            }
        })();
    </script>
    <?php
}
