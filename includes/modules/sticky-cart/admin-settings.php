<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Réglages du panneau panier : aucun des trois sites d'origine n'avait ce
 * sous-menu (l'activation seule suffisait, chacun avait son propre code
 * adapté à son thème). Un plugin public ne peut pas deviner à l'avance le
 * balisage de chaque thème WooCommerce existant — la chaîne de sélecteurs
 * CSS intégrée à assets/js/sticky-cart.js couvre les cas les plus courants
 * (blocs Gutenberg, WooCommerce classique, quelques variantes de thèmes
 * répandues), mais un marchand sur un thème non couvert doit pouvoir
 * renseigner lui-même le bon sélecteur plutôt que d'attendre une mise à
 * jour du plugin — même mécanisme que la surcharge de couleur (Navi >
 * Couleurs, includes/core/admin-menu.php) : valeur par défaut = chaîne de
 * secours intégrée, un réglage explicite est essayé en premier.
 */
add_action( 'admin_menu', 'navi_sticky_ajouter_menu' );
function navi_sticky_ajouter_menu() {
    add_submenu_page(
        navi_admin_parent_slug(),
        __( 'Réglages Panier', 'navi' ),
        __( 'Panier', 'navi' ),
        'manage_options',
        'navi-sticky-cart',
        'navi_sticky_page_reglages_html'
    );
}

add_action( 'admin_init', 'navi_sticky_enregistrer_parametres' );
function navi_sticky_enregistrer_parametres() {
    register_setting( 'navi_sticky_options_group', 'navi_sticky_selector_price', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'navi_sticky_options_group', 'navi_sticky_selector_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'navi_sticky_options_group', 'navi_sticky_selector_image', array( 'sanitize_callback' => 'sanitize_text_field' ) );
}

function navi_sticky_page_reglages_html() {
    if ( ! navi_user_can_manage() ) {
        wp_die( esc_html__( "Vous n'avez pas les permissions nécessaires pour accéder à cette page.", 'navi' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Réglages du panneau panier', 'navi' ); ?></h1>
        <p>
            <?php esc_html_e( 'Le panneau panier détecte automatiquement le prix, le nom et l\'image du produit sur la fiche produit, en essayant plusieurs emplacements courants (blocs Gutenberg, WooCommerce classique, quelques variantes de thèmes répandues).', 'navi' ); ?>
        </p>
        <p>
            <?php esc_html_e( 'Si votre thème n\'affiche pas correctement ces informations dans le panneau, indiquez ici un sélecteur CSS précis : il sera essayé en priorité, avant la détection automatique. Laisser vide pour garder la détection automatique.', 'navi' ); ?>
        </p>
        <form method="post" action="options.php">
            <?php settings_fields( 'navi_sticky_options_group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="navi_sticky_selector_price"><?php esc_html_e( 'Sélecteur CSS du prix', 'navi' ); ?></label></th>
                    <td><input type="text" name="navi_sticky_selector_price" id="navi_sticky_selector_price" value="<?php echo esc_attr( get_option( 'navi_sticky_selector_price', '' ) ); ?>" class="regular-text" placeholder=".summary .price" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="navi_sticky_selector_name"><?php esc_html_e( 'Sélecteur CSS du nom du produit', 'navi' ); ?></label></th>
                    <td><input type="text" name="navi_sticky_selector_name" id="navi_sticky_selector_name" value="<?php echo esc_attr( get_option( 'navi_sticky_selector_name', '' ) ); ?>" class="regular-text" placeholder="h1.product_title" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="navi_sticky_selector_image"><?php esc_html_e( 'Sélecteur CSS de l\'image du produit', 'navi' ); ?></label></th>
                    <td><input type="text" name="navi_sticky_selector_image" id="navi_sticky_selector_image" value="<?php echo esc_attr( get_option( 'navi_sticky_selector_image', '' ) ); ?>" class="regular-text" placeholder=".woocommerce-product-gallery__image img" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
