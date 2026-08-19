<?php
if ( ! defined( 'ABSPATH' ) ) exit;

Navi_Module_Registry::register(
    'sticky-cart',
    array(
        'label'           => __( 'Ajout au panier', 'saito-navi' ),
        'short_label'     => __( 'Panier', 'saito-navi' ),
        'icon'            => '🛒',
        // Icône du mini-panier WooCommerce (wc-block-mini-cart__icon) plutôt
        // qu'un tracé maison : se lit comme un élément natif de WooCommerce
        // plutôt qu'un pictogramme générique de plugin. fill="currentColor"
        // (au lieu du "#000000" d'origine, propre au bloc WooCommerce) :
        // suit la couleur du bouton comme les autres icônes du menu (voir
        // .navi-fab-item, assets/css/core.css). class="navi-fab-icon-cart" :
        // ce tracé WooCommerce laisse une marge interne bien plus généreuse
        // dans son viewBox que les icônes maison du hub (cookie,
        // accessibilité), ce qui le faisait paraître plus petit une fois
        // réduit à la même taille — voir la règle dédiée dans
        // assets/css/core.css qui l'agrandit légèrement pour compenser.
        'icon_svg'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 32 32" class="navi-fab-icon-cart"><path fill="currentColor" fill-rule="evenodd" d="M12.444 14.222a.89.89 0 0 1 .89.89 2.667 2.667 0 0 0 5.333 0 .889.889 0 1 1 1.777 0 4.444 4.444 0 1 1-8.888 0c0-.492.398-.89.888-.89M11.24 6.683a1 1 0 0 1 .76-.35h8a1 1 0 0 1 .76.35l4 4.666A1 1 0 0 1 24 13H8a1 1 0 0 1-.76-1.65zm1.22 1.65L10.174 11h11.652L19.54 8.333z" clip-rule="evenodd"></path><path fill="currentColor" fill-rule="evenodd" d="M7 12a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v13.333a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1zm2 1v11.333h14V13z" clip-rule="evenodd"></path></svg>',
        'description'     => __( 'Panneau produit (image, variation, ajout au panier) ancré au bouton, qui suit l\'utilisateur sur les fiches produit.', 'saito-navi' ),
        'option_name'     => 'navi_module_active_sticky-cart',
        'default_active'  => true,
        // Icône dans le menu du FAB (fiches produit uniquement, voir
        // fab_condition) : le panneau panier s'affiche déjà tout seul au
        // scroll (handleStickyVisibility, assets/js/sticky-cart.js), mais
        // rien ne permet de le rouvrir après une fermeture manuelle (croix)
        // — cette icône sert justement à ça (voir dismissedManually,
        // assets/js/sticky-cart.js).
        'fab_action'      => 'open-sticky-cart',
        'fab_condition'   => 'is_product',
        'available'       => true,
        'settings_url'    => admin_url( 'admin.php?page=navi-main#sticky-cart' ),
        'settings_panel_callback' => 'navi_sticky_render_settings_panel',
        'visibility_selector' => '#navi-sticky-bar, .navi-fab-item[data-item-id="sticky-cart"]',
    )
);

// Chargé même si le module est désactivé : sinon son onglet de réglages
// (Navi > Navi > Panier) disparaîtrait avec lui, sans aucun moyen de le
// réactiver depuis le BO (voir navi_sticky_render_settings_panel()).
if ( is_admin() ) {
    require_once __DIR__ . '/admin-settings.php';
}

if ( Navi_Module_Registry::is_active( 'sticky-cart' ) && ! is_admin() ) {
    require_once __DIR__ . '/sticky-frontend.php';
}
