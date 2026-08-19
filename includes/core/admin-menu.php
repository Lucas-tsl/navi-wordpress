<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'navi_add_admin_menu' );
function navi_add_admin_menu() {
    add_menu_page(
        __( 'Navi', 'navi' ),
        __( 'Navi', 'navi' ),
        'manage_options',
        'navi-main',
        'navi_render_dashboard_page',
        NAVI_PLUGIN_URL . 'assets/img/logo-32.png',
        58
    );
}

// WP core ajoute un padding-top à toute icône de menu personnalisée
// (#adminmenu .wp-menu-image img) qui décale notre logo — retiré ici,
// pour notre menu uniquement.
add_action( 'admin_head', 'navi_admin_menu_icon_css' );
function navi_admin_menu_icon_css() {
    echo '<style>#toplevel_page_navi-main .wp-menu-image img { padding: 0; }</style>';
}

// Réglages transverses (pas propres à un module) : chacun a sa propre
// option d'activation enregistrée par son propre onglet désormais (voir
// navi_render_module_active_field(), helpers.php, et le
// navi_..._enregistrer_parametres() de chaque module) — seuls les réglages
// globaux ci-dessous vivent dans ce groupe central, rendu par l'onglet
// "Général" (navi_render_dashboard_page() ci-dessous).
add_action( 'admin_init', 'navi_register_module_settings' );
function navi_register_module_settings() {
    // Position du bouton flottant : un widget tiers (chat, WhatsApp...) est
    // très souvent logé en bas-droite sur les sites e-commerce, d'où ce
    // réglage pour éviter toute collision visuelle.
    register_setting(
        'navi_modules_group',
        'navi_fab_position',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'navi_sanitize_fab_position',
            'default'           => 'right',
        )
    );

    // Couleurs de la DA (FAB + panneaux cookies/accessibilité) : chaque site
    // peut les adapter à sa propre identité visuelle sans toucher au CSS
    // (voir assets/css/core.css pour les variables --navi-color-ink*, et
    // includes/core/frontend.php pour la surcharge injectée par site).
    // sanitize_hex_color (WordPress core) renvoie '' pour une valeur vide ou
    // invalide : traité comme "pas de surcharge, garder la couleur par
    // défaut du plugin" par navi_color_ink()/navi_color_ink_soft()
    // (helpers.php), utilisées par la surcharge injectée dans
    // navi_enqueue_core_assets() (includes/core/frontend.php).
    register_setting(
        'navi_modules_group',
        'navi_color_ink',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    register_setting(
        'navi_modules_group',
        'navi_color_ink_soft',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    // Arrondis (boutons bannière cookies/panier sticky, image produit du
    // panier sticky) : même logique de surcharge que les couleurs
    // ci-dessus — voir navi_radius_button()/navi_radius_image() (helpers.php)
    // et la surcharge injectée dans includes/core/frontend.php.
    register_setting(
        'navi_modules_group',
        'navi_radius_button',
        array(
            'type'              => 'integer',
            'sanitize_callback' => 'navi_sanitize_radius',
            'default'           => 4,
        )
    );
    register_setting(
        'navi_modules_group',
        'navi_radius_image',
        array(
            'type'              => 'integer',
            'sanitize_callback' => 'navi_sanitize_radius',
            'default'           => 4,
        )
    );

    // Langue du plugin (voir navi_current_language(), includes/core/i18n.php) :
    // 'auto' laisse WPML/la locale du site décider, sinon force la langue
    // choisie quelle que soit la détection automatique.
    register_setting(
        'navi_modules_group',
        'navi_language',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'navi_sanitize_language',
            'default'           => 'auto',
        )
    );
}

// Sélecteurs de couleur natifs WordPress (wp-color-picker), uniquement sur
// la page du tableau de bord du plugin : pas besoin de charger ce script sur
// le reste de l'admin.
add_action( 'admin_enqueue_scripts', 'navi_enqueue_color_picker_assets' );
function navi_enqueue_color_picker_assets( $hook_suffix ) {
    if ( 'toplevel_page_navi-main' !== $hook_suffix ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_add_inline_script(
        'wp-color-picker',
        "jQuery(function($){ $('.navi-color-picker').wpColorPicker(); });"
    );
}

// Design system du BO (assets/css/admin.css) : reskin de la page Navi
// uniquement (page unique désormais — modules réglés par onglet, voir
// navi_render_dashboard_page() ci-dessous), jamais chargé ailleurs dans
// l'admin WordPress.
add_action( 'admin_enqueue_scripts', 'navi_enqueue_admin_assets' );
function navi_enqueue_admin_assets( $hook_suffix ) {
    if ( 'toplevel_page_navi-main' !== $hook_suffix ) {
        return;
    }
    navi_enqueue_style( 'navi-admin-css', NAVI_PLUGIN_URL . 'assets/css/admin.css', array(), NAVI_VERSION );
}

/**
 * Page unique Navi > Navi, avec un onglet "Général" et un onglet par module
 * (contenu fourni par $module['settings_panel_callback'], voir
 * class-navi-module-registry.php et includes/modules/<nom>/admin-settings.php)
 * — remplace les anciennes sous-pages "Réglages" séparées par module.
 */
function navi_render_dashboard_page() {
    if ( ! navi_user_can_manage() ) {
        wp_die( esc_html__( "Vous n'avez pas les permissions nécessaires pour accéder à cette page.", 'navi' ) );
    }
    $modules = Navi_Module_Registry::all();
    ?>
    <div class="wrap navi-admin">
        <?php navi_admin_page_header( __( 'Navi', 'navi' ) ); ?>

        <h2 class="nav-tab-wrapper" id="navi-main-tabs">
            <a href="#general" class="nav-tab nav-tab-active" data-tab="general"><?php esc_html_e( 'Général', 'navi' ); ?></a>
            <?php foreach ( $modules as $id => $module ) : ?>
                <a href="#<?php echo esc_attr( $id ); ?>" class="nav-tab" data-tab="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( ! empty( $module['short_label'] ) ? $module['short_label'] : $module['label'] ); ?></a>
            <?php endforeach; ?>
        </h2>

        <div class="navi-admin-tab-panel" data-tab-panel="general">
            <div class="navi-admin-modules">
                <?php foreach ( $modules as $id => $module ) : ?>
                    <div class="navi-admin-module">
                        <div class="navi-admin-module-icon"><?php echo esc_html( $module['icon'] ); ?></div>
                        <div class="navi-admin-module-body">
                            <strong><?php echo esc_html( $module['label'] ); ?></strong>
                            <?php if ( ! $module['available'] ) : ?>
                                <em class="is-unavailable"><?php esc_html_e( 'Bientôt disponible', 'navi' ); ?></em>
                            <?php elseif ( Navi_Module_Registry::is_active( $id ) ) : ?>
                                <em class="is-active"><?php esc_html_e( 'Actif', 'navi' ); ?></em>
                            <?php else : ?>
                                <em class="is-inactive"><?php esc_html_e( 'Inactif', 'navi' ); ?></em>
                            <?php endif; ?>
                            <p><?php echo esc_html( $module['description'] ); ?></p>
                        </div>
                        <?php if ( $module['available'] ) : ?>
                            <a class="navi-admin-module-settings" href="#<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Réglages', 'navi' ); ?></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'navi_modules_group' ); ?>
                <?php navi_render_hash_preserving_referer_field(); ?>

                <div class="navi-admin-card">
                    <h2><?php esc_html_e( 'Position du bouton flottant', 'navi' ); ?></h2>
                    <p>
                        <label for="navi_fab_position"><?php esc_html_e( "Coin de l'écran", 'navi' ); ?></label><br />
                        <select name="navi_fab_position" id="navi_fab_position">
                            <option value="right" <?php selected( 'right', get_option( 'navi_fab_position', 'right' ) ); ?>><?php esc_html_e( 'Bas droite (par défaut)', 'navi' ); ?></option>
                            <option value="left" <?php selected( 'left', get_option( 'navi_fab_position', 'right' ) ); ?>><?php esc_html_e( 'Bas gauche', 'navi' ); ?></option>
                        </select>
                    </p>
                    <p class="description"><?php esc_html_e( "À changer si un autre widget flottant (chat, WhatsApp...) occupe déjà le bas droite du site.", 'navi' ); ?></p>
                </div>

                <div class="navi-admin-card">
                    <h2><?php esc_html_e( 'Langue du plugin', 'navi' ); ?></h2>
                    <p class="description"><?php esc_html_e( "Par défaut, Navi suit la langue détectée automatiquement (WPML si actif, sinon la langue du site). Choisissez une langue ici pour l'imposer, quelle que soit cette détection.", 'navi' ); ?></p>
                    <p>
                        <label for="navi_language"><?php esc_html_e( 'Langue', 'navi' ); ?></label><br />
                        <select name="navi_language" id="navi_language">
                            <?php foreach ( navi_available_languages() as $code => $label ) : ?>
                                <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, navi_current_language_override() ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                </div>

                <div class="navi-admin-card">
                    <h2><?php esc_html_e( 'Apparence', 'navi' ); ?></h2>
                    <p class="description"><?php esc_html_e( "Couleurs du bouton flottant et des panneaux (cookies, accessibilité), arrondis des boutons et de l'image produit (panier sticky) : à adapter à l'identité visuelle de ce site.", 'navi' ); ?></p>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="navi_color_ink"><?php esc_html_e( 'Couleur principale', 'navi' ); ?></label></th>
                            <td><input type="text" name="navi_color_ink" id="navi_color_ink" class="navi-color-picker" value="<?php echo esc_attr( navi_color_ink() ); ?>" data-default-color="#1a1a1a" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="navi_color_ink_soft"><?php esc_html_e( 'Couleur secondaire', 'navi' ); ?></label></th>
                            <td><input type="text" name="navi_color_ink_soft" id="navi_color_ink_soft" class="navi-color-picker" value="<?php echo esc_attr( navi_color_ink_soft() ); ?>" data-default-color="#6b6b6b" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="navi_radius_button"><?php esc_html_e( 'Arrondi des boutons (px)', 'navi' ); ?></label></th>
                            <td>
                                <input type="number" name="navi_radius_button" id="navi_radius_button" min="0" max="50" value="<?php echo esc_attr( navi_radius_button() ); ?>" class="small-text" /> px
                                <p class="description"><?php esc_html_e( '0 = angles droits. Boutons concernés : bannière cookies, panier sticky.', 'navi' ); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="navi_radius_image"><?php esc_html_e( "Arrondi de l'image produit (px)", 'navi' ); ?></label></th>
                            <td>
                                <input type="number" name="navi_radius_image" id="navi_radius_image" min="0" max="50" value="<?php echo esc_attr( navi_radius_image() ); ?>" class="small-text" /> px
                                <p class="description"><?php esc_html_e( 'Miniature produit affichée dans le panier sticky.', 'navi' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <?php foreach ( $modules as $id => $module ) : ?>
            <div class="navi-admin-tab-panel" data-tab-panel="<?php echo esc_attr( $id ); ?>" style="display:none;">
                <?php if ( ! empty( $module['settings_panel_callback'] ) && is_callable( $module['settings_panel_callback'] ) ) : ?>
                    <?php call_user_func( $module['settings_panel_callback'] ); ?>
                <?php else : ?>
                    <p class="description"><?php esc_html_e( 'Bientôt disponible.', 'navi' ); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        (function () {
            // Onglets de premier niveau : afficher/masquer par hash d'URL
            // ("#cookie-consent", "#stories"...), pour permettre les liens
            // directs (ex. "Réglages" sur une carte de l'onglet Général) sans
            // rechargement de page. Le hash peut porter un second segment
            // pour un sous-onglet imbriqué (ex. "#stories/mockup", voir
            // includes/modules/stories/admin-settings.php) : seul le premier
            // segment est utilisé ici, le reste appartient au module.
            var tabs = document.querySelectorAll('#navi-main-tabs .nav-tab');
            var panels = document.querySelectorAll('.navi-admin-tab-panel');
            function activateTab( name ) {
                var found = false;
                tabs.forEach(function (tab) {
                    var match = tab.dataset.tab === name;
                    tab.classList.toggle('nav-tab-active', match);
                    if (match) found = true;
                });
                panels.forEach(function (panel) {
                    panel.style.display = panel.dataset.tabPanel === name ? '' : 'none';
                });
                return found;
            }
            function fromHash() {
                var name = window.location.hash.replace('#', '').split('/')[0];
                if (!name || !activateTab(name)) activateTab('general');
            }
            window.addEventListener('hashchange', fromHash);
            fromHash();
        })();
    </script>
    <?php
}
