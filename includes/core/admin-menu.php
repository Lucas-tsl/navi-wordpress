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

// Chaque module a sa propre option d'activation, déclarée ici en une seule
// fois pour tous les modules du registre.
add_action( 'admin_init', 'navi_register_module_settings' );
function navi_register_module_settings() {
    foreach ( Navi_Module_Registry::all() as $module ) {
        register_setting(
            'navi_modules_group',
            $module['option_name'],
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'navi_sanitize_checkbox',
                'default'           => $module['default_active'] ? 1 : 0,
            )
        );
    }

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

function navi_render_dashboard_page() {
    if ( ! navi_user_can_manage() ) {
        wp_die( esc_html__( "Vous n'avez pas les permissions nécessaires pour accéder à cette page.", 'navi' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Navi', 'navi' ); ?></h1>
        <p><?php esc_html_e( 'Activez ou désactivez les modules pilotés par le bouton flottant du site.', 'navi' ); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields( 'navi_modules_group' ); ?>
            <table class="widefat striped" style="max-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th><?php esc_html_e( 'Module', 'navi' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'navi' ); ?></th>
                        <th style="width: 90px;"><?php esc_html_e( 'Actif', 'navi' ); ?></th>
                        <th style="width: 110px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( Navi_Module_Registry::all() as $module ) : ?>
                    <tr>
                        <td style="font-size: 20px;"><?php echo esc_html( $module['icon'] ); ?></td>
                        <td>
                            <strong><?php echo esc_html( $module['label'] ); ?></strong>
                            <?php if ( ! $module['available'] ) : ?>
                                <br /><em><?php esc_html_e( 'Bientôt disponible', 'navi' ); ?></em>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $module['description'] ); ?></td>
                        <td>
                            <input type="hidden" name="<?php echo esc_attr( $module['option_name'] ); ?>" value="0" />
                            <input
                                type="checkbox"
                                name="<?php echo esc_attr( $module['option_name'] ); ?>"
                                value="1"
                                <?php checked( 1, get_option( $module['option_name'], $module['default_active'] ? 1 : 0 ) ); ?>
                                <?php disabled( ! $module['available'] ); ?>
                            />
                        </td>
                        <td>
                            <?php if ( ! empty( $module['settings_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $module['settings_url'] ); ?>"><?php esc_html_e( 'Réglages', 'navi' ); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h2><?php esc_html_e( 'Position du bouton flottant', 'navi' ); ?></h2>
            <p>
                <label for="navi_fab_position"><?php esc_html_e( "Coin de l'écran", 'navi' ); ?></label><br />
                <select name="navi_fab_position" id="navi_fab_position">
                    <option value="right" <?php selected( 'right', get_option( 'navi_fab_position', 'right' ) ); ?>><?php esc_html_e( 'Bas droite (par défaut)', 'navi' ); ?></option>
                    <option value="left" <?php selected( 'left', get_option( 'navi_fab_position', 'right' ) ); ?>><?php esc_html_e( 'Bas gauche', 'navi' ); ?></option>
                </select>
            </p>
            <p class="description"><?php esc_html_e( "À changer si un autre widget flottant (chat, WhatsApp...) occupe déjà le bas droite du site.", 'navi' ); ?></p>

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

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
