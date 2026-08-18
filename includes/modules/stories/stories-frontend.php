<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', 'navi_stories_enqueue_assets', 20 );
function navi_stories_enqueue_assets() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }

    // get_the_ID() plutôt que global $product : à ce stade (wp_enqueue_scripts,
    // déclenché depuis wp_head()), WooCommerce n'a pas toujours encore
    // remplacé $product par un vrai objet WC_Product sur ce thème — appeler
    // ->get_id() dessus provoque une erreur fatale ("Call to a member
    // function get_id() on string"). get_the_ID() s'appuie sur la requête
    // principale, déjà résolue à ce stade sur une fiche produit.
    $product_id = get_the_ID();
    if ( ! $product_id || empty( navi_stories_get_configured( $product_id ) ) ) {
        return;
    }

    navi_enqueue_style( 'navi-stories-css', NAVI_PLUGIN_URL . 'assets/css/stories.css', array(), NAVI_VERSION );
    navi_enqueue_script( 'navi-stories-js', NAVI_PLUGIN_URL . 'assets/js/stories.js', array(), NAVI_VERSION, true );

    wp_localize_script(
        'navi-stories-js',
        'naviStoriesI18n',
        array(
            'closeLabel' => __( 'Fermer', 'navi' ),
            'prevLabel'  => __( 'Story précédente', 'navi' ),
            'nextLabel'  => __( 'Story suivante', 'navi' ),
        )
    );

    // Surcharge d'aspect (Navi > Stories), uniquement pour les propriétés
    // qui s'écartent de la valeur par défaut de assets/css/stories.css
    // (:root) — même mécanisme que la surcharge de couleurs/arrondis du
    // noyau (includes/core/frontend.php).
    $border      = navi_stories_border_width();
    $phoneBg     = navi_stories_color_phone_bg();
    $closeIcon   = navi_stories_color_close_icon();
    $closeBg     = navi_stories_color_close_bg();
    $overlay     = navi_stories_color_overlay();
    $padding     = navi_stories_phone_padding();
    $width       = navi_stories_phone_width();
    $overrides   = array();

    if ( NAVI_STORIES_DEFAULT_BORDER_WIDTH !== $border ) {
        $overrides['--navi-story-border-width'] = $border . 'px';
    }
    if ( NAVI_STORIES_DEFAULT_PHONE_BG !== $phoneBg ) {
        $overrides['--navi-story-phone-bg'] = esc_html( $phoneBg );
    }
    if ( NAVI_STORIES_DEFAULT_CLOSE_ICON !== $closeIcon ) {
        $overrides['--navi-story-close-icon'] = esc_html( $closeIcon );
    }
    if ( NAVI_STORIES_DEFAULT_CLOSE_BG !== $closeBg ) {
        $overrides['--navi-story-close-bg'] = esc_html( $closeBg );
    }
    if ( NAVI_STORIES_DEFAULT_OVERLAY_BG !== $overlay ) {
        $overrides['--navi-story-overlay-bg'] = esc_html( $overlay );
    }
    if ( NAVI_STORIES_DEFAULT_PHONE_PADDING !== $padding ) {
        $overrides['--navi-story-phone-padding'] = $padding . 'px';
    }
    if ( NAVI_STORIES_DEFAULT_PHONE_WIDTH !== $width ) {
        $overrides['--navi-story-phone-width'] = $width . 'px';
    }

    if ( ! empty( $overrides ) ) {
        $css = ':root{';
        foreach ( $overrides as $property => $value ) {
            $css .= $property . ':' . $value . ';';
        }
        $css .= '}';
        wp_add_inline_style( 'navi-stories-css', $css );
    }
}
