<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rendu des bulles en fiche produit — un seul hook
 * (woocommerce_product_thumbnails, juste après la galerie produit),
 * équivalent de Navi::hookDisplayAfterProductThumbs() côté PrestaShop.
 */
add_action( 'woocommerce_product_thumbnails', 'navi_stories_render_bubbles' );
function navi_stories_render_bubbles() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }

    global $product;
    if ( ! $product ) {
        return;
    }

    $product_id = $product->get_id();
    $stories    = navi_stories_get_configured( $product_id );
    if ( empty( $stories ) ) {
        return;
    }

    $show_label = navi_stories_show_label();
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
}
