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
            'addToCartText'       => __( 'Ajouter au panier - ', 'saito-navi' ),
            'addingText'          => __( 'Ajout en cours...', 'saito-navi' ),
            'addedText'           => __( 'Ajouté', 'saito-navi' ),
            'outOfStockText'      => __( 'Rupture de stock', 'saito-navi' ),
            'chooseVariationText' => __( 'Choisir une option', 'saito-navi' ),
        )
    );

    // Sélecteurs CSS personnalisés (Navi > Panier, admin-settings.php) :
    // essayés en PREMIER par assets/js/sticky-cart.js, avant la chaîne de
    // secours intégrée au plugin — pour un thème dont le balisage prix/nom/
    // image n'est reconnu par aucun des sélecteurs génériques déjà prévus.
    // Chaîne vide = pas de surcharge, la chaîne intégrée s'applique seule.
    wp_localize_script(
        'navi-sticky-js',
        'naviStickyCartConfig',
        array(
            'priceSelector' => get_option( 'navi_sticky_selector_price', '' ),
            'nameSelector'  => get_option( 'navi_sticky_selector_name', '' ),
            'imageSelector' => get_option( 'navi_sticky_selector_image', '' ),
        )
    );
}
