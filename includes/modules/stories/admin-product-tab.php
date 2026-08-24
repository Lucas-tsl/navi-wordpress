<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Onglet "Stories (Navi)" sur la fiche produit WooCommerce — équivalent de
 * Navi::hookDisplayAdminProductsExtra() côté PrestaShop.
 *
 * Si le plugin compagnon Navi FAQ est actif, son panneau "Navi" partagé
 * (includes/navi-panel.php côté navi-faq, metabox autonome sous l'éditeur
 * de description, hors "Données produit") prend le relais via le filtre
 * ouvert navi_product_panel_tabs — Stories y devient un onglet interne aux
 * côtés de FAQ, plutôt qu'une entrée de plus dans "Données produit". Sans
 * Navi FAQ, ce filtre n'existe simplement pas : navi_stories_uses_navi_panel()
 * retombe sur false et Stories garde son propre onglet WooCommerce comme
 * avant, sans dépendance dure à navi-faq.
 */
function navi_stories_uses_navi_panel() {
    if ( ! function_exists( 'navi_panel_get_tabs' ) ) {
        return false;
    }
    // navi-faq peut exclure 'product' de sa propre couverture (filtre
    // navi_faq_post_types) : dans ce cas son panneau ne s'enregistre pas du
    // tout sur la fiche produit, et s'appuyer dessus ferait disparaître
    // Stories plutôt que de la replier sur son propre onglet.
    if ( function_exists( 'navi_faq_post_types' ) && ! in_array( 'product', navi_faq_post_types(), true ) ) {
        return false;
    }
    return true;
}

add_filter( 'navi_product_panel_tabs', 'navi_stories_register_panel_tab' );
function navi_stories_register_panel_tab( $tabs ) {
    $tabs['stories'] = array(
        'label'    => __( 'Stories', 'saito-navi' ),
        'callback' => 'navi_stories_render_panel_content',
    );
    return $tabs;
}

add_filter( 'woocommerce_product_data_tabs', 'navi_stories_add_product_tab' );
function navi_stories_add_product_tab( $tabs ) {
    if ( navi_stories_uses_navi_panel() ) {
        return $tabs;
    }
    $tabs['navi_stories'] = array(
        'label'    => __( 'Stories (Navi)', 'saito-navi' ),
        'target'   => 'navi_stories_product_data',
        'class'    => array(),
        'priority' => 65,
    );
    return $tabs;
}

/**
 * wp.media (bouton "Choisir une vidéo", voir navi_stories_render_product_panel()
 * ci-dessous) : uniquement sur l'écran d'édition produit, jamais chargé
 * ailleurs dans l'admin.
 */
add_action( 'admin_enqueue_scripts', 'navi_stories_enqueue_media_library' );
function navi_stories_enqueue_media_library( $hook_suffix ) {
    if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || 'product' !== $screen->post_type ) {
        return;
    }
    wp_enqueue_media();

    wp_enqueue_style( 'navi-admin-stories-product-tab-css', NAVI_PLUGIN_URL . 'assets/css/admin-stories-product-tab.css', array(), NAVI_VERSION );

    wp_enqueue_script( 'navi-admin-stories-product-tab-js', NAVI_PLUGIN_URL . 'assets/js/admin-stories-product-tab.js', array( 'media-editor' ), NAVI_VERSION, true );
    wp_localize_script(
        'navi-admin-stories-product-tab-js',
        'naviStoryAdminData',
        array(
            'labelConfigured'  => __( 'Configurée', 'saito-navi' ),
            'labelEmpty'       => __( 'Vide', 'saito-navi' ),
            'mediaTitle'       => __( 'Choisir une vidéo', 'saito-navi' ),
            'mediaButton'      => __( 'Utiliser cette vidéo', 'saito-navi' ),
            'labelInvalidType' => __( 'Seuls les fichiers .mp4 sont acceptés.', 'saito-navi' ),
        )
    );
}

add_action( 'woocommerce_product_data_panels', 'navi_stories_render_product_panel' );
function navi_stories_render_product_panel() {
    if ( navi_stories_uses_navi_panel() ) {
        return; // Rendu par navi_panel_render() (navi-faq) via l'onglet enregistré ci-dessus.
    }
    global $post;
    if ( ! $post ) {
        return;
    }
    ?>
    <div id="navi_stories_product_data" class="panel woocommerce_options_panel hidden">
        <?php navi_stories_render_panel_content( $post ); ?>
    </div>
    <?php
}

/**
 * Contenu partagé entre les deux points d'entrée possibles : l'onglet
 * WooCommerce "Données produit" (navi_stories_render_product_panel()
 * ci-dessus) et l'onglet interne du panneau "Navi" partagé de navi-faq
 * (navi_stories_register_panel_tab() ci-dessus) — même props et champs de
 * sauvegarde (navi_story_nonce, navi_story_submitted) quel que soit le
 * conteneur visuel.
 */
function navi_stories_render_panel_content( $post ) {
    if ( ! $post ) {
        return;
    }
    $slots = navi_stories_get( $post->ID );
    ?>
        <div class="options_group" style="padding: 12px 20px;">
            <p>
                <?php esc_html_e( "Jusqu'à 4 stories par produit. Chaque story affiche une bulle vidéo cliquable sur la fiche produit. Collez une URL ou un identifiant YouTube pour un aperçu immédiat, ou choisissez une vidéo MP4 depuis la médiathèque.", 'saito-navi' ); ?>
            </p>

            <?php wp_nonce_field( 'navi_story_save_' . $post->ID, 'navi_story_nonce' ); ?>
            <input type="hidden" name="navi_story_submitted" value="1" />

            <div class="navi-story-admin-grid">
                <?php foreach ( $slots as $index => $slot ) : ?>
                    <div class="navi-story-admin-card" data-slot="<?php echo esc_attr( $index ); ?>">
                        <div class="navi-story-admin-card-header">
                            <?php /* translators: %d: numéro de l'emplacement de story (1 à NAVI_STORY_LIMIT). */ ?>
                            <span class="navi-story-admin-card-title"><?php echo esc_html( sprintf( __( 'Story #%d', 'saito-navi' ), $index ) ); ?></span>
                            <span class="navi-story-admin-badge<?php echo $slot['youtube'] ? ' is-filled' : ''; ?>" id="navi-story-badge-<?php echo esc_attr( $index ); ?>">
                                <?php echo $slot['youtube'] ? esc_html__( 'Configurée', 'saito-navi' ) : esc_html__( 'Vide', 'saito-navi' ); ?>
                            </span>
                        </div>

                        <div class="navi-story-admin-preview">
                            <?php
                            $thumbnail = $slot['youtube'] ? 'https://img.youtube.com/vi/' . rawurlencode( $slot['youtube'] ) . '/mqdefault.jpg' : '';
                            ?>
                            <img id="navi-story-admin-thumb-<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $thumbnail ); ?>" alt="" style="<?php echo $thumbnail ? 'display:block;' : ''; ?>" />
                            <span class="navi-story-admin-placeholder" id="navi-story-admin-placeholder-<?php echo esc_attr( $index ); ?>" style="<?php echo $thumbnail ? 'display:none;' : ''; ?>">
                                <?php esc_html_e( 'Aucune vidéo', 'saito-navi' ); ?>
                            </span>
                        </div>

                        <div class="navi-story-admin-body">
                            <div class="form-field">
                                <label for="navi_story_youtube_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'URL ou identifiant YouTube', 'saito-navi' ); ?></label>
                                <input type="text" class="navi-story-admin-youtube-input" style="width:100%;"
                                       id="navi_story_youtube_<?php echo esc_attr( $index ); ?>"
                                       name="navi_story_youtube_<?php echo esc_attr( $index ); ?>"
                                       value="<?php echo esc_attr( $slot['youtube'] ); ?>"
                                       placeholder="https://www.youtube.com/watch?v=..."
                                       data-slot="<?php echo esc_attr( $index ); ?>" />
                            </div>

                            <div class="form-field">
                                <label for="navi_story_label_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Libellé affiché', 'saito-navi' ); ?></label>
                                <input type="text" style="width:100%;"
                                       id="navi_story_label_<?php echo esc_attr( $index ); ?>"
                                       name="navi_story_label_<?php echo esc_attr( $index ); ?>"
                                       value="<?php echo esc_attr( $slot['label'] ); ?>" />
                            </div>

                            <details>
                                <summary><?php esc_html_e( 'Prévisualisation personnalisée (optionnel)', 'saito-navi' ); ?></summary>

                                <p class="description">
                                    <?php esc_html_e( 'Sans vidéo de prévisualisation, la bulle affiche une image fixe (vignette YouTube). Pour une bulle animée (mini-vidéo en boucle, plus vivante), importez un court extrait MP4 ci-dessous.', 'saito-navi' ); ?>
                                </p>

                                <div class="form-field">
                                    <label for="navi_story_preview_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'URL de la vidéo de prévisualisation (MP4)', 'saito-navi' ); ?></label>
                                    <input type="text" style="width:100%;"
                                           id="navi_story_preview_<?php echo esc_attr( $index ); ?>"
                                           name="navi_story_preview_<?php echo esc_attr( $index ); ?>"
                                           value="<?php echo esc_attr( $slot['preview'] ); ?>" />
                                    <p class="description"><?php esc_html_e( 'Laisser vide pour utiliser la vignette YouTube par défaut.', 'saito-navi' ); ?></p>
                                </div>

                                <div class="form-field">
                                    <label><?php esc_html_e( '...ou choisir depuis la médiathèque', 'saito-navi' ); ?></label>
                                    <p>
                                        <button type="button" class="button navi-story-admin-media-button" data-slot="<?php echo esc_attr( $index ); ?>">
                                            <?php esc_html_e( 'Choisir une vidéo', 'saito-navi' ); ?>
                                        </button>
                                    </p>
                                    <p class="navi-story-admin-file-info" id="navi-story-admin-file-info-<?php echo esc_attr( $index ); ?>"></p>
                                </div>
                            </details>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
}

/**
 * Sauvegarde — équivalent de Navi::handleProductSave() côté PrestaShop.
 * woocommerce_process_product_meta ne se déclenche qu'à un vrai
 * enregistrement produit réel depuis l'admin (déjà protégé par la session
 * WordPress + le nonce global du formulaire produit) ; on vérifie ici en
 * plus notre propre nonce dédié et la capacité edit_product, même niveau de
 * rigueur que côté PrestaShop.
 */
add_action( 'woocommerce_process_product_meta', 'navi_stories_process_product_meta' );
function navi_stories_process_product_meta( $product_id ) {
    if ( ! isset( $_POST['navi_story_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['navi_story_nonce'] ) ), 'navi_story_save_' . $product_id ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_product', $product_id ) ) {
        return;
    }
    navi_stories_save( $product_id );
}
