<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Bloc Gutenberg équivalent du shortcode [navi_stories] (public-display.php) :
 * même résultat, réglé visuellement dans l'éditeur plutôt qu'en tapant un
 * attribut texte. Bloc dynamique (pas de save() côté JS) — le rendu passe
 * toujours par navi_stories_shortcode(), seule source de vérité pour ce
 * balisage, jamais dupliqué ici.
 */
add_action( 'init', 'navi_stories_register_block' );
function navi_stories_register_block() {
    wp_register_script(
        'navi-stories-block-editor',
        NAVI_PLUGIN_URL . 'assets/js/stories-block.js',
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
        NAVI_VERSION,
        true
    );

    register_block_type(
        'navi/stories',
        array(
            'title'           => __( 'Stories (Navi)', 'saito-navi' ),
            'description'     => __( 'Bulles vidéo "stories" pour un produit WooCommerce.', 'saito-navi' ),
            'category'        => 'woocommerce',
            'icon'            => 'video-alt3',
            'editor_script'   => 'navi-stories-block-editor',
            'render_callback' => 'navi_stories_block_render',
            'attributes'      => array(
                'productId' => array(
                    'type'    => 'string',
                    'default' => '',
                ),
            ),
        )
    );
}

// navi_stories_shortcode() (public-display.php) n'est définie que côté
// front/REST (voir stories/module.php) : jamais un problème ici, ce
// render_callback n'est lui-même jamais invoqué en contexte wp-admin pur
// (rendu front réel, ou appel REST du bloc-renderer pour l'aperçu éditeur —
// les deux hors du contexte is_admin()).
function navi_stories_block_render( $attributes ) {
    $atts = array();
    if ( ! empty( $attributes['productId'] ) ) {
        $atts['id'] = $attributes['productId'];
    }
    return navi_stories_shortcode( $atts );
}
