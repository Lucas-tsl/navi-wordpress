<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Langues actives fournies par WPML, si le plugin est installé et configuré.
 * On n'utilise que son API officielle (filtre 'wpml_active_languages') :
 * si WPML est absent, ce filtre n'existe pas et renvoie simplement null.
 * Retourne un tableau vide dans ce cas, pour masquer proprement le sélecteur
 * de langue plutôt que d'afficher un choix qui ne ferait rien.
 */
function navi_a11y_get_languages() {
    if ( ! has_filter( 'wpml_active_languages' ) ) {
        return array();
    }
    $languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
    return is_array( $languages ) ? $languages : array();
}

// Lien d'évitement (WCAG 2.4.1) : la cible réelle est déterminée en JS
// (assets/js/accessibility.js), ce fichier ne connaissant pas la structure
// du thème actif.
add_action( 'wp_body_open', 'navi_a11y_render_skip_link' );
function navi_a11y_render_skip_link() {
    ?>
    <a href="#navi-a11y-main-content" class="navi-a11y-skip-link"><?php esc_html_e( 'Aller au contenu', 'navi' ); ?></a>
    <?php
}

add_action( 'wp_footer', 'navi_a11y_render_panel' );
function navi_a11y_render_panel() {
    $navi_languages   = navi_a11y_get_languages();
    // Repli GTranslate quand WPML est absent : le shortcode [gtranslate] du
    // plugin GTranslate peut être placé n'importe où sur le site
    // (documentation GTranslate) — l'utiliser directement ici, plutôt que
    // d'espérer qu'un exemplaire existe déjà ailleurs sur CETTE page (ce
    // qui n'est pas garanti : un widget/shortcode placé dans le footer ou une
    // sidebar spécifique peut être absent d'un template de fiche produit).
    // shortcode_exists() : évite d'afficher "[gtranslate]" en texte brut si
    // le plugin GTranslate n'est pas actif.
    $navi_use_gtranslate = empty( $navi_languages ) && shortcode_exists( 'gtranslate' );
    ?>
    <div id="navi-a11y-panel" class="navi-a11y-panel" tabindex="-1">
        <button type="button" class="navi-a11y-close" aria-label="<?php esc_attr_e( 'Fermer', 'navi' ); ?>">✕</button>
        <div class="navi-a11y-scroll">
            <h3 class="navi-a11y-title"><?php esc_html_e( 'Accessibilité', 'navi' ); ?></h3>

            <?php if ( ! empty( $navi_languages ) ) : ?>
            <div class="navi-a11y-row">
                <label for="navi-a11y-lang"><?php esc_html_e( 'Langue', 'navi' ); ?></label>
                <select id="navi-a11y-lang">
                    <?php foreach ( $navi_languages as $navi_language ) : ?>
                        <option
                            value="<?php echo esc_url( $navi_language['url'] ); ?>"
                            <?php selected( ! empty( $navi_language['active'] ) ); ?>
                        ><?php echo esc_html( $navi_language['translated_name'] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ( $navi_use_gtranslate ) : ?>
            <div class="navi-a11y-row navi-a11y-row--gtranslate">
                <label id="navi-a11y-gtranslate-label"><?php esc_html_e( 'Langue', 'navi' ); ?></label>
                <div class="navi-a11y-gtranslate" aria-labelledby="navi-a11y-gtranslate-label">
                    <?php echo do_shortcode( '[gtranslate]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode du plugin GTranslate, pas une entrée utilisateur. ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="navi-a11y-row">
                <span id="navi-a11y-textsize-label"><?php esc_html_e( 'Taille du texte', 'navi' ); ?></span>
                <div class="navi-a11y-stepper">
                    <button type="button" id="navi-a11y-textsize-dec" aria-label="<?php esc_attr_e( 'Réduire la taille du texte', 'navi' ); ?>" aria-describedby="navi-a11y-textsize-label">−</button>
                    <span id="navi-a11y-textsize-value" aria-live="polite">100%</span>
                    <button type="button" id="navi-a11y-textsize-inc" aria-label="<?php esc_attr_e( 'Augmenter la taille du texte', 'navi' ); ?>" aria-describedby="navi-a11y-textsize-label">+</button>
                </div>
            </div>

            <div class="navi-a11y-row">
                <span><?php esc_html_e( 'Contraste élevé', 'navi' ); ?></span>
                <button type="button" id="navi-a11y-contrast-toggle" class="navi-a11y-switch" aria-pressed="false">
                    <span class="navi-a11y-switch-knob"></span>
                </button>
            </div>

            <div class="navi-a11y-row">
                <span><?php esc_html_e( 'Curseur agrandi', 'navi' ); ?></span>
                <button type="button" id="navi-a11y-cursor-toggle" class="navi-a11y-switch" aria-pressed="false">
                    <span class="navi-a11y-switch-knob"></span>
                </button>
            </div>

            <div class="navi-a11y-row">
                <span><?php esc_html_e( 'Souligner les liens', 'navi' ); ?></span>
                <button type="button" id="navi-a11y-underline-toggle" class="navi-a11y-switch" aria-pressed="false">
                    <span class="navi-a11y-switch-knob"></span>
                </button>
            </div>

            <button type="button" id="navi-a11y-reset" class="navi-a11y-reset"><?php esc_html_e( 'Réinitialiser les réglages', 'navi' ); ?></button>
        </div>
    </div>
    <?php
}
