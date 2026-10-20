<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', 'navi_sticky_enqueue_assets', 20 );

function navi_sticky_enqueue_assets() {
    // WooCommerce peut être absent sur certaines installs du hub (modules
    // futurs non liés au e-commerce) : on évite un fatal error si is_product()
    // n'existe pas plutôt que de supposer WooCommerce toujours actif.
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }

    navi_enqueue_style(
        'navi-sticky-css',
        NAVI_PLUGIN_URL . 'assets/css/sticky-cart.css',
        array(),
        NAVI_VERSION
    );

    navi_enqueue_script(
        'navi-sticky-js',
        NAVI_PLUGIN_URL . 'assets/js/sticky-cart.js',
        array( 'jquery' ),
        NAVI_VERSION,
        true
    );

    wp_localize_script(
        'navi-sticky-js',
        'naviStickyCartI18n',
        array(
            'addToCartText'       => navi_sticky_text( 'navi_sticky_text_add_to_cart', __( 'Ajouter au panier - ', 'saito-navi' ), ' - ' ),
            'addingText'          => __( 'Ajout en cours...', 'saito-navi' ),
            'addedText'           => __( 'Ajouté', 'saito-navi' ),
            'outOfStockText'      => navi_sticky_text( 'navi_sticky_text_out_of_stock', __( 'Rupture de stock', 'saito-navi' ) ),
            'chooseVariationText' => __( 'Choisir une option', 'saito-navi' ),
        )
    );

    // Sélecteurs CSS personnalisés (Navi > Panier, admin-settings.php) :
    // essayés en PREMIER par assets/js/sticky-cart.js, avant la chaîne de
    // secours intégrée au plugin — pour un thème dont le balisage prix/nom/
    // image n'est reconnu par aucun des sélecteurs génériques déjà prévus.
    // swatchSelector suit un principe différent (modèle avec "{value}",
    // voir getSwatchVisual() dans sticky-cart.js) : il ne remplace pas une
    // chaîne de secours générique mais complète WCBoost Variation Swatches
    // pour les thèmes qui affichent leurs propres vignettes de variation.
    // Chaîne vide = pas de surcharge/vignette personnalisée.
    wp_localize_script(
        'navi-sticky-js',
        'naviStickyCartConfig',
        array(
            'priceSelector'  => get_option( 'navi_sticky_selector_price', '' ),
            'nameSelector'   => get_option( 'navi_sticky_selector_name', '' ),
            'imageSelector'  => get_option( 'navi_sticky_selector_image', '' ),
            'swatchSelector' => get_option( 'navi_sticky_selector_swatch', '' ),
        )
    );
}

// Texte personnalisé du panneau (Navi > Panier, admin-settings.php), ou le
// texte par défaut traduit si le réglage est vide. $suffix sépare le texte
// du bouton du prix qui le suit ("Ajouter au panier - 20,00 €") : déjà
// présent dans le texte par défaut, il est ajouté au texte personnalisé.
function navi_sticky_text( $option, $default, $suffix = '' ) {
    $text = trim( (string) get_option( $option, '' ) );
    return '' === $text ? $default : $text . $suffix;
}
