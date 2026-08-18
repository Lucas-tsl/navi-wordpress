<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rendu des bulles en fiche produit — deux points d'entrée pour le même
 * balisage : automatiquement après la galerie produit (hook
 * woocommerce_product_thumbnails, équivalent de
 * Navi::hookDisplayAfterProductThumbs() côté PrestaShop), et à la demande
 * via le shortcode [navi_stories] (positionnement manuel dans le contenu,
 * un constructeur de page, ou un template de thème).
 */

/**
 * Balisage des bulles pour un produit donné, ou chaîne vide si aucune
 * story configurée — partagé par le hook automatique et le shortcode.
 */
function navi_stories_render_bubbles_html( $product_id ) {
    $stories = navi_stories_get_configured( $product_id );
    if ( empty( $stories ) ) {
        return '';
    }

    $show_label = navi_stories_show_label();
    ob_start();
    ?>
    <div class="navi-story-row">
        <?php foreach ( $stories as $story ) : ?>
            <button type="button" class="navi-story-bubble"
                    data-video-id="<?php echo esc_attr( $story['youtube'] ); ?>"
                    data-product-id="<?php echo esc_attr( $product_id ); ?>"
                    data-label="<?php echo esc_attr( $story['label'] ); ?>">
                <span class="navi-story-bubble-circle">
                    <?php if ( '.mp4' === strtolower( substr( $story['preview'], -4 ) ) ) : ?>
                        <video class="navi-story-bubble-preview" muted loop autoplay playsinline preload="metadata">
                            <source src="<?php echo esc_url( $story['preview'] ); ?>" type="video/mp4">
                        </video>
                    <?php else : ?>
                        <img class="navi-story-bubble-preview" src="<?php echo esc_url( $story['preview'] ); ?>" alt="" loading="lazy" />
                    <?php endif; ?>
                    <span class="navi-story-bubble-play" aria-hidden="true"></span>
                </span>
                <?php if ( $show_label && '' !== $story['label'] ) : ?>
                    <span class="navi-story-bubble-label"><?php echo esc_html( $story['label'] ); ?></span>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Affichage automatique après la galerie produit — désactivable (Navi >
 * Stories, "Afficher automatiquement") pour les sites qui préfèrent
 * positionner les bulles eux-mêmes via le shortcode [navi_stories]
 * uniquement (évite un double affichage).
 */
add_action( 'woocommerce_product_thumbnails', 'navi_stories_render_bubbles' );
function navi_stories_render_bubbles() {
    if ( ! navi_stories_auto_display() ) {
        return;
    }

    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }

    global $product;
    if ( ! $product ) {
        return;
    }

    echo navi_stories_render_bubbles_html( $product->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- navi_stories_render_bubbles_html() échappe déjà chaque valeur individuellement au rendu.
}

/**
 * [navi_stories] — rendu manuel, indépendant du réglage "Afficher
 * automatiquement" ci-dessus (un shortcode posé explicitement doit
 * toujours s'afficher). Sans attribut `id`, utilise le produit de la
 * page courante (fiche produit) ; avec `id="123"`, affiche les stories
 * d'un produit précis (utile dans une boucle ou un constructeur de page).
 */
add_shortcode( 'navi_stories', 'navi_stories_shortcode' );
function navi_stories_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'navi_stories' );

    $product_id = (int) $atts['id'];
    if ( ! $product_id ) {
        global $product;
        if ( $product instanceof WC_Product ) {
            $product_id = $product->get_id();
        } elseif ( function_exists( 'is_product' ) && is_product() ) {
            $product_id = get_the_ID();
        }
    }

    if ( ! $product_id ) {
        return '';
    }

    return navi_stories_render_bubbles_html( $product_id );
}
