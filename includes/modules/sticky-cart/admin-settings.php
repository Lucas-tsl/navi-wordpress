<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Réglages du panneau panier : aucun des trois sites d'origine n'avait ce
 * réglage (l'activation seule suffisait, chacun avait son propre code
 * adapté à son thème). Un plugin public ne peut pas deviner à l'avance le
 * balisage de chaque thème WooCommerce existant — la chaîne de sélecteurs
 * CSS intégrée à assets/js/sticky-cart.js couvre les cas les plus courants
 * (blocs Gutenberg, WooCommerce classique, quelques variantes de thèmes
 * répandues), mais un marchand sur un thème non couvert doit pouvoir
 * renseigner lui-même le bon sélecteur plutôt que d'attendre une mise à
 * jour du plugin — même mécanisme que la surcharge de couleur (Navi >
 * Navi > Apparence, includes/core/admin-menu.php) : valeur par défaut =
 * chaîne de secours intégrée, un réglage explicite est essayé en premier.
 */
add_action( 'admin_init', 'navi_sticky_enregistrer_parametres' );
function navi_sticky_enregistrer_parametres() {
    register_setting( 'navi_sticky_options_group', 'navi_sticky_selector_price', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'navi_sticky_options_group', 'navi_sticky_selector_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'navi_sticky_options_group', 'navi_sticky_selector_image', array( 'sanitize_callback' => 'sanitize_text_field' ) );

    // Activation du module + visibilité par appareil (voir navi_render_module_active_field()/
    // navi_render_visibility_fields, helpers.php).
    register_setting( 'navi_sticky_options_group', 'navi_module_active_sticky-cart', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_sticky_options_group', 'navi_show_desktop_sticky-cart', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
    register_setting( 'navi_sticky_options_group', 'navi_show_mobile_sticky-cart', array( 'type' => 'integer', 'sanitize_callback' => 'navi_sanitize_checkbox', 'default' => 1 ) );
}

// Contenu de l'onglet "Panier" (Navi > Navi) — voir
// navi_cookie_render_settings_panel() (cookie-consent/admin-settings.php)
// pour le patron général.
function navi_sticky_render_settings_panel() {
    ?>
    <form method="post" action="options.php">
        <?php settings_fields( 'navi_sticky_options_group' ); ?>
        <?php navi_render_hash_preserving_referer_field(); ?>
        <div class="navi-admin-card">
            <p class="description">
                <?php esc_html_e( 'Le panneau panier détecte automatiquement le prix, le nom et l\'image du produit sur la fiche produit, en essayant plusieurs emplacements courants (blocs Gutenberg, WooCommerce classique, quelques variantes de thèmes répandues). Si votre thème ne les affiche pas correctement, indiquez ici un sélecteur CSS précis : il sera essayé en priorité, avant la détection automatique. Laisser vide pour garder la détection automatique.', 'navi' ); ?>
            </p>
            <table class="form-table">
                <?php navi_render_module_active_field( 'sticky-cart' ); ?>
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
                <?php navi_render_visibility_fields( 'sticky-cart' ); ?>
            </table>
        </div>
        <?php submit_button(); ?>
    </form>
    <?php
}
