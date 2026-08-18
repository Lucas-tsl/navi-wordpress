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
            'label'           => __( 'Haut de page', 'navi' ),
            'shortLabel'      => __( 'Haut', 'navi' ),
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

    wp_localize_script(
        'navi-core-js',
        'naviConfig',
        array(
            'items'      => $items,
            'isProduct'  => function_exists( 'is_product' ) && is_product(),
            'closeLabel' => __( 'Fermer', 'navi' ),
        )
    );
}

// SVG dessiné plutôt que l'emoji ⚙️, dont le rendu diffère trop d'un
// appareil/navigateur à l'autre (Windows, macOS, Android, iOS) pour rester
// cohérent — même logique que les icônes SVG des modules
// (class-navi-module-registry.php).
define( 'NAVI_GEAR_SVG', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>' );

// Lien d'évitement dédié : sans lui, un utilisateur clavier doit tabuler à
// travers toute la page avant d'atteindre les réglages (cookies,
// accessibilité), le FAB étant rendu en tout dernier dans le DOM (wp_footer).
// wp_body_open est le point d'ancrage recommandé par WordPress pour ce type
// de lien : la plupart des thèmes l'appellent juste après <body>.
add_action( 'wp_body_open', 'navi_render_skip_to_fab_link' );
function navi_render_skip_to_fab_link() {
    ?>
    <a href="#navi-fab-toggle" class="navi-skip-link"><?php esc_html_e( 'Aller aux réglages (accessibilité, cookies, panier)', 'navi' ); ?></a>
    <?php
}

add_action( 'wp_footer', 'navi_render_fab_markup', 5 );
function navi_render_fab_markup() {
    // Un seul objet DOM traverse les 3 états (fermé / menu / détail) : voir
    // assets/css/core.css et assets/js/core.js. #navi-fab-detail est le slot
    // partagé où chaque module vient afficher son propre contenu (déplacé ou
    // injecté par assets/js/core.js), plutôt que de flotter indépendamment.
    ?>
    <div id="navi-fab" class="navi-fab" data-state="closed" data-position="<?php echo esc_attr( get_option( 'navi_fab_position', 'right' ) ); ?>">
        <button type="button" id="navi-fab-toggle" class="navi-fab-toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'navi' ); ?>">
            <span class="navi-fab-gear" aria-hidden="true"><?php echo NAVI_GEAR_SVG; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constante SVG interne, pas une entrée utilisateur. ?></span>
        </button>
        <div id="navi-fab-menu" class="navi-fab-menu" role="menu"></div>
        <div id="navi-fab-detail" class="navi-fab-detail"></div>
    </div>
    <?php
}
