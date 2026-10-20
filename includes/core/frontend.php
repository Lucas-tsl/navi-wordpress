<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', 'navi_enqueue_core_assets', 5 );
function navi_enqueue_core_assets() {
    navi_enqueue_style( 'navi-core-css', NAVI_PLUGIN_URL . 'assets/css/core.css', array(), NAVI_VERSION );
    navi_enqueue_script( 'navi-core-js', NAVI_PLUGIN_URL . 'assets/js/core.js', array(), NAVI_VERSION, true );

    // Surcharge des couleurs/arrondis de la DA (Navi > Apparence), uniquement
    // pour les propriétés qui s'écartent de la valeur par défaut de
    // assets/css/core.css (:root) — pas besoin d'un style inline sinon.
    // wp_add_inline_style rattache ce <style> juste après navi-core-css,
    // quel que soit l'ordre d'enqueue des autres feuilles du plugin.
    $ink          = navi_color_ink();
    $ink_soft     = navi_color_ink_soft();
    $radiusButton = navi_radius_button();
    $radiusImage  = navi_radius_image();
    $overrides    = array();

    if ( '#1a1a1a' !== $ink || '#6b6b6b' !== $ink_soft ) {
        $ink_rgb                    = navi_hex_to_rgb( $ink );
        $overrides['--navi-color-ink']      = esc_html( $ink );
        $overrides['--navi-color-ink-soft'] = esc_html( $ink_soft );
        if ( ! empty( $ink_rgb ) ) {
            $overrides['--navi-color-ink-rgb'] = esc_html( $ink_rgb );
        }
    }
    if ( 4 !== $radiusButton ) {
        $overrides['--navi-radius-button'] = $radiusButton . 'px';
    }
    if ( 4 !== $radiusImage ) {
        $overrides['--navi-radius-image'] = $radiusImage . 'px';
    }

    $css = '';
    if ( ! empty( $overrides ) ) {
        $css .= ':root{';
        foreach ( $overrides as $property => $value ) {
            $css .= $property . ':' . $value . ';';
        }
        $css .= '}';
    }

    // Visibilité par appareil (un réglage par module, voir
    // includes/modules/*/admin-settings.php et navi_render_visibility_fields()
    // dans helpers.php) : même seuil que le reste du hub (480px, voir
    // assets/css/core.css). display:none!important — un thème/plugin tiers
    // peut appliquer ses propres règles avec une spécificité plus élevée
    // sur des sélecteurs génériques comme .navi-fab-item.
    $desktopHidden = array();
    $mobileHidden  = array();
    foreach ( Navi_Module_Registry::all() as $module_id => $module ) {
        if ( empty( $module['visibility_selector'] ) ) {
            continue;
        }
        if ( ! navi_show_desktop( $module_id ) ) {
            $desktopHidden[] = $module['visibility_selector'];
        }
        if ( ! navi_show_mobile( $module_id ) ) {
            $mobileHidden[] = $module['visibility_selector'];
        }
    }
    if ( ! empty( $desktopHidden ) ) {
        $css .= '@media (min-width:481px){' . implode( ',', $desktopHidden ) . '{display:none!important}}';
    }
    if ( ! empty( $mobileHidden ) ) {
        $css .= '@media (max-width:480px){' . implode( ',', $mobileHidden ) . '{display:none!important}}';
    }

    if ( '' !== $css ) {
        wp_add_inline_style( 'navi-core-css', $css );
    }

    // Icône "retour en haut" : toujours proposée par le noyau, visible uniquement
    // après 50% de scroll. Ce n'est pas un module car elle n'a pas d'état activable.
    $items = array(
        array(
            'id'              => 'top',
            'icon'            => '↑',
            'label'           => __( 'Haut de page', 'saito-navi' ),
            'shortLabel'      => __( 'Haut', 'saito-navi' ),
            'action'          => 'scroll-top',
            'condition'       => 'scroll',
            'scrollThreshold' => 50,
        ),
    );

    foreach ( Navi_Module_Registry::active_modules() as $module_id ) {
        $module = Navi_Module_Registry::get( $module_id );
        if ( empty( $module['fab_action'] ) ) {
            continue;
        }
        // Module actif mais sans rien à afficher sur cette page (ex. le
        // calendrier quand Saito Calendar Event est absent) : pas d'icône.
        if ( ! empty( $module['fab_enabled_callback'] ) && is_callable( $module['fab_enabled_callback'] ) && ! call_user_func( $module['fab_enabled_callback'] ) ) {
            continue;
        }
        $items[] = array(
            'id'         => $module_id,
            'icon'       => $module['icon'],
            'iconSvg'    => $module['icon_svg'],
            'label'      => $module['label'],
            // Légende affichée sous l'icône dans le menu du FAB : plus courte
            // que le libellé complet, qui déborderait sous l'icône. Repli sur
            // le libellé complet si un module n'en définit pas.
            'shortLabel' => ! empty( $module['short_label'] ) ? $module['short_label'] : $module['label'],
            'action'     => $module['fab_action'],
            'condition'  => $module['fab_condition'],
        );
    }

    $is_product = function_exists( 'is_product' ) && is_product();
    $item_ids   = wp_list_pluck( $items, 'id' );

    wp_localize_script(
        'navi-core-js',
        'naviConfig',
        array(
            'items'      => $items,
            'isProduct'  => $is_product,
            'mainIcon'   => navi_fab_main_icon( $is_product, $item_ids ),
            'closeLabel' => __( 'Fermer', 'saito-navi' ),
        )
    );
}

// Icône du bouton fermé selon la page : panier sur une fiche produit (panier
// flottant actif), calendrier si un calendrier Saito est proposé, engrenage
// sinon. L'engrenage revient de toute façon dès que le pied de page est
// visible (assets/js/core.js) : c'est là qu'on cherche les réglages du site.
function navi_fab_main_icon( $is_product, $item_ids ) {
    if ( $is_product && in_array( 'sticky-cart', $item_ids, true ) ) {
        return 'cart';
    }
    if ( in_array( 'calendar', $item_ids, true ) ) {
        return 'calendar';
    }
    return 'gear';
}

// SVG dessiné plutôt que l'emoji ⚙️, dont le rendu diffère trop d'un
// appareil/navigateur à l'autre (Windows, macOS, Android, iOS) pour rester
// cohérent — même logique que les icônes SVG des modules
// (class-navi-module-registry.php).
define( 'NAVI_GEAR_SVG', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>' );

// Sac de courses avec un « + » : se lit « ajouter au panier » sur une fiche
// produit, sans être confondu avec l'icône du mini-panier du thème.
define( 'NAVI_CART_PLUS_SVG', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19.2 13.5l-.8-4.6A1.5 1.5 0 0 0 16.9 7.6H7.1a1.5 1.5 0 0 0-1.5 1.3L4.2 18.6A1.2 1.2 0 0 0 5.4 20H12.5"></path><path d="M8.5 9.5V6.8a3.5 3.5 0 0 1 7 0v2.7"></path><path d="M18 15.5v6"></path><path d="M15 18.5h6"></path></svg>' );

define( 'NAVI_CALENDAR_SVG', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="15.5" rx="2"></rect><path d="M3.5 10h17"></path><path d="M8 3v4"></path><path d="M16 3v4"></path><path d="M12 13.2l.9 1.8 2 .3-1.45 1.4.35 2-1.8-.95-1.8.95.35-2-1.45-1.4 2-.3z"></path></svg>' );

// Lien d'évitement dédié : sans lui, un utilisateur clavier doit tabuler à
// travers toute la page avant d'atteindre les réglages (cookies,
// accessibilité), le FAB étant rendu en tout dernier dans le DOM (wp_footer).
// wp_body_open est le point d'ancrage recommandé par WordPress pour ce type
// de lien : la plupart des thèmes l'appellent juste après <body>.
add_action( 'wp_body_open', 'navi_render_skip_to_fab_link' );
function navi_render_skip_to_fab_link() {
    ?>
    <a href="#navi-fab-toggle" class="navi-skip-link"><?php esc_html_e( 'Aller aux réglages (accessibilité, cookies, panier)', 'saito-navi' ); ?></a>
    <?php
}

add_action( 'wp_footer', 'navi_render_fab_markup', 5 );
function navi_render_fab_markup() {
    // Un seul objet DOM traverse les 3 états (fermé / menu / détail) : voir
    // assets/css/core.css et assets/js/core.js. #navi-fab-detail est le slot
    // partagé où chaque module vient afficher son propre contenu (déplacé ou
    // injecté par assets/js/core.js), plutôt que de flotter indépendamment.
    ?>
    <div id="navi-fab" class="navi-fab" data-state="closed" data-icon="gear" data-position="<?php echo esc_attr( get_option( 'navi_fab_position', 'right' ) ); ?>">
        <button type="button" id="navi-fab-toggle" class="navi-fab-toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'saito-navi' ); ?>">
            <span class="navi-fab-gear" aria-hidden="true"><?php echo NAVI_GEAR_SVG; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constante SVG interne, pas une entrée utilisateur. ?></span>
            <span class="navi-fab-main-icon" data-icon="cart" aria-hidden="true"><?php echo NAVI_CART_PLUS_SVG; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constante SVG interne. ?></span>
            <span class="navi-fab-main-icon" data-icon="calendar" aria-hidden="true"><?php echo NAVI_CALENDAR_SVG; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constante SVG interne. ?></span>
        </button>
        <div id="navi-fab-menu" class="navi-fab-menu" role="menu"></div>
        <div id="navi-fab-detail" class="navi-fab-detail"></div>
    </div>
    <?php
}
