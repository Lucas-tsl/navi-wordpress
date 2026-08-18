<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Onglet "Stories (Navi)" sur la fiche produit WooCommerce — équivalent de
 * Navi::hookDisplayAdminProductsExtra() côté PrestaShop.
 */
add_filter( 'woocommerce_product_data_tabs', 'navi_stories_add_product_tab' );
function navi_stories_add_product_tab( $tabs ) {
    $tabs['navi_stories'] = array(
        'label'    => __( 'Stories (Navi)', 'navi' ),
        'target'   => 'navi_stories_product_data',
        'class'    => array(),
        'priority' => 65,
    );
    return $tabs;
}

add_action( 'woocommerce_product_data_panels', 'navi_stories_render_product_panel' );
function navi_stories_render_product_panel() {
    global $post;
    if ( ! $post ) {
        return;
    }
    $slots = navi_stories_get( $post->ID );
    ?>
    <div id="navi_stories_product_data" class="panel woocommerce_options_panel hidden">
        <div class="options_group" style="padding: 12px 20px;">
            <p>
                <?php
                printf(
                    /* translators: %d: taille maximale en Mo */
                    esc_html__( "Jusqu'à 4 stories par produit. Chaque story affiche une bulle vidéo cliquable sur la fiche produit. Collez une URL ou un identifiant YouTube pour un aperçu immédiat, ou importez une vidéo MP4 (max. %d Mo).", 'navi' ),
                    (int) ( NAVI_STORY_MAX_BYTES / 1048576 )
                );
                ?>
            </p>

            <?php wp_nonce_field( 'navi_story_save_' . $post->ID, 'navi_story_nonce' ); ?>
            <input type="hidden" name="navi_story_submitted" value="1" />

            <style>
                .navi-story-admin-grid { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 16px; }
                .navi-story-admin-card { flex: 1 1 calc(50% - 16px); min-width: 280px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #fff; }
                .navi-story-admin-card-header { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f6f7f7; border-bottom: 1px solid #ddd; }
                .navi-story-admin-card-title { font-weight: 700; }
                .navi-story-admin-badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; background: #e5e5e5; color: #666; }
                .navi-story-admin-badge.is-filled { background: #d4edda; color: #256029; }
                .navi-story-admin-preview { position: relative; display: flex; align-items: center; justify-content: center; height: 140px; background: #222; }
                .navi-story-admin-preview img { max-width: 100%; max-height: 100%; display: none; }
                .navi-story-admin-placeholder { color: #999; font-size: 0.8125rem; }
                .navi-story-admin-body { padding: 14px; overflow: hidden; }
                .navi-story-admin-body .form-field { float: none; width: auto; padding: 0; margin: 0 0 12px; clear: both; }
                .navi-story-admin-body label { font-weight: 600; font-size: 0.8125rem; display: block; float: none; width: auto; margin: 0 0 4px; }
                .navi-story-admin-body details { margin-top: 8px; }
                .navi-story-admin-body summary { cursor: pointer; font-size: 0.8125rem; color: #666; margin-bottom: 8px; }
                .navi-story-admin-file-info { font-size: 0.75rem; margin-top: 4px; }
                .navi-story-admin-file-info.is-warning { color: #c0392b; font-weight: 700; }
            </style>

            <div class="navi-story-admin-grid">
                <?php foreach ( $slots as $index => $slot ) : ?>
                    <div class="navi-story-admin-card" data-slot="<?php echo esc_attr( $index ); ?>">
                        <div class="navi-story-admin-card-header">
                            <?php /* translators: %d: numéro de l'emplacement de story (1 à NAVI_STORY_LIMIT). */ ?>
                            <span class="navi-story-admin-card-title"><?php echo esc_html( sprintf( __( 'Story #%d', 'navi' ), $index ) ); ?></span>
                            <span class="navi-story-admin-badge<?php echo $slot['youtube'] ? ' is-filled' : ''; ?>" id="navi-story-badge-<?php echo esc_attr( $index ); ?>">
                                <?php echo $slot['youtube'] ? esc_html__( 'Configurée', 'navi' ) : esc_html__( 'Vide', 'navi' ); ?>
                            </span>
                        </div>

                        <div class="navi-story-admin-preview">
                            <?php
                            $thumbnail = $slot['youtube'] ? 'https://img.youtube.com/vi/' . rawurlencode( $slot['youtube'] ) . '/mqdefault.jpg' : '';
                            ?>
                            <img id="navi-story-admin-thumb-<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $thumbnail ); ?>" alt="" style="<?php echo $thumbnail ? 'display:block;' : ''; ?>" />
                            <span class="navi-story-admin-placeholder" id="navi-story-admin-placeholder-<?php echo esc_attr( $index ); ?>" style="<?php echo $thumbnail ? 'display:none;' : ''; ?>">
                                <?php esc_html_e( 'Aucune vidéo', 'navi' ); ?>
                            </span>
                        </div>

                        <div class="navi-story-admin-body">
                            <div class="form-field">
                                <label for="navi_story_youtube_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'URL ou identifiant YouTube', 'navi' ); ?></label>
                                <input type="text" class="navi-story-admin-youtube-input" style="width:100%;"
                                       id="navi_story_youtube_<?php echo esc_attr( $index ); ?>"
                                       name="navi_story_youtube_<?php echo esc_attr( $index ); ?>"
                                       value="<?php echo esc_attr( $slot['youtube'] ); ?>"
                                       placeholder="https://www.youtube.com/watch?v=..."
                                       data-slot="<?php echo esc_attr( $index ); ?>" />
                            </div>

                            <div class="form-field">
                                <label for="navi_story_label_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Libellé affiché', 'navi' ); ?></label>
                                <input type="text" style="width:100%;"
                                       id="navi_story_label_<?php echo esc_attr( $index ); ?>"
                                       name="navi_story_label_<?php echo esc_attr( $index ); ?>"
                                       value="<?php echo esc_attr( $slot['label'] ); ?>" />
                            </div>

                            <details>
                                <summary><?php esc_html_e( 'Prévisualisation personnalisée (optionnel)', 'navi' ); ?></summary>

                                <div class="form-field">
                                    <label for="navi_story_preview_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'URL de la vidéo de prévisualisation (MP4)', 'navi' ); ?></label>
                                    <input type="text" style="width:100%;"
                                           id="navi_story_preview_<?php echo esc_attr( $index ); ?>"
                                           name="navi_story_preview_<?php echo esc_attr( $index ); ?>"
                                           value="<?php echo esc_attr( $slot['preview'] ); ?>" />
                                    <p class="description"><?php esc_html_e( 'Laisser vide pour utiliser la vignette YouTube par défaut.', 'navi' ); ?></p>
                                </div>

                                <div class="form-field">
                                    <label for="navi_story_preview_file_<?php echo esc_attr( $index ); ?>"><?php esc_html_e( '...ou importer un fichier MP4', 'navi' ); ?></label>
                                    <input type="file" class="navi-story-admin-file-input"
                                           id="navi_story_preview_file_<?php echo esc_attr( $index ); ?>"
                                           name="navi_story_preview_file_<?php echo esc_attr( $index ); ?>"
                                           accept="video/mp4,.mp4"
                                           data-slot="<?php echo esc_attr( $index ); ?>" />
                                    <p class="navi-story-admin-file-info" id="navi-story-admin-file-info-<?php echo esc_attr( $index ); ?>"></p>
                                </div>
                            </details>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
                var NAVI_STORY_MAX_BYTES = <?php echo (int) NAVI_STORY_MAX_BYTES; ?>;
                var NAVI_STORY_LABEL_CONFIGURED = '<?php echo esc_js( __( 'Configurée', 'navi' ) ); ?>';
                var NAVI_STORY_LABEL_EMPTY = '<?php echo esc_js( __( 'Vide', 'navi' ) ); ?>';
                var NAVI_STORY_LABEL_TOO_LARGE = '<?php echo esc_js( __( 'dépasse la taille maximale autorisée', 'navi' ) ); ?>';

                (function () {
                    function extractYoutubeId(input) {
                        input = (input || '').trim();
                        if (!input) return '';
                        var urlMatch = input.match(/(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
                        if (urlMatch) return urlMatch[1];
                        if (/^[A-Za-z0-9_-]{11}$/.test(input)) return input;
                        return '';
                    }

                    function updatePreview(slot) {
                        var input = document.getElementById('navi_story_youtube_' + slot);
                        var thumb = document.getElementById('navi-story-admin-thumb-' + slot);
                        var placeholder = document.getElementById('navi-story-admin-placeholder-' + slot);
                        var badge = document.getElementById('navi-story-badge-' + slot);
                        if (!input || !thumb || !placeholder || !badge) return;

                        var videoId = extractYoutubeId(input.value);
                        if (videoId) {
                            thumb.src = 'https://img.youtube.com/vi/' + videoId + '/mqdefault.jpg';
                            thumb.style.display = 'block';
                            placeholder.style.display = 'none';
                            badge.textContent = NAVI_STORY_LABEL_CONFIGURED;
                            badge.classList.add('is-filled');
                        } else {
                            thumb.style.display = 'none';
                            placeholder.style.display = 'block';
                            badge.textContent = NAVI_STORY_LABEL_EMPTY;
                            badge.classList.remove('is-filled');
                        }
                    }

                    function updateFileInfo(slot, input) {
                        var info = document.getElementById('navi-story-admin-file-info-' + slot);
                        if (!info) return;
                        if (!input.files || !input.files.length) {
                            info.textContent = '';
                            info.classList.remove('is-warning');
                            return;
                        }
                        var file = input.files[0];
                        var sizeMb = (file.size / 1048576).toFixed(1);
                        if (file.size > NAVI_STORY_MAX_BYTES) {
                            info.textContent = file.name + ' — ' + sizeMb + ' Mo — ' + NAVI_STORY_LABEL_TOO_LARGE;
                            info.classList.add('is-warning');
                        } else {
                            info.textContent = file.name + ' — ' + sizeMb + ' Mo';
                            info.classList.remove('is-warning');
                        }
                    }

                    var youtubeInputs = document.querySelectorAll('.navi-story-admin-youtube-input');
                    for (var i = 0; i < youtubeInputs.length; i++) {
                        youtubeInputs[i].addEventListener('input', function (event) {
                            updatePreview(event.target.getAttribute('data-slot'));
                        });
                    }

                    var fileInputs = document.querySelectorAll('.navi-story-admin-file-input');
                    for (var j = 0; j < fileInputs.length; j++) {
                        fileInputs[j].addEventListener('change', function (event) {
                            updateFileInfo(event.target.getAttribute('data-slot'), event.target);
                        });
                    }
                })();
            </script>
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
