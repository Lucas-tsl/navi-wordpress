<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Réglages d'aspect des stories (Navi > Stories) — mêmes valeurs par défaut
 * que Navi PrestaShop (constantes DEFAULT_STORIES_..., MIN_STORIES_...,
 * MAX_STORIES_... de navi.php) et mêmes variables CSS --navi-story-...
 */

const NAVI_STORIES_DEFAULT_BORDER_WIDTH  = 2;
const NAVI_STORIES_DEFAULT_PHONE_BG      = '#111111';
const NAVI_STORIES_DEFAULT_CLOSE_ICON    = '#ffffff';
const NAVI_STORIES_DEFAULT_CLOSE_BG      = '#000000';
const NAVI_STORIES_DEFAULT_OVERLAY_BG    = '#000000';
const NAVI_STORIES_DEFAULT_PHONE_PADDING = 10;
const NAVI_STORIES_DEFAULT_PHONE_WIDTH   = 200;
const NAVI_STORIES_MIN_PHONE_WIDTH       = 150;
const NAVI_STORIES_MAX_PHONE_WIDTH       = 280;
const NAVI_STORIES_MAX_PHONE_PADDING     = 20;
const NAVI_STORIES_DEFAULT_BUBBLE_SIZE   = 64;
const NAVI_STORIES_MIN_BUBBLE_SIZE       = 40;
const NAVI_STORIES_MAX_BUBBLE_SIZE       = 120;

// Zoom de la vidéo dans le mockup (en %, 100 = aucun zoom). Solution
// principale contre le bandeau titre/chaîne et le filigrane "Shorts" de
// YouTube : le masque JS (assets/js/stories.js, attachVideoMask(), API
// IFrame YouTube) qui couvre l'iframe précisément pendant les deux
// fenêtres où ce chrome est visible (chargement, puis fin de vidéo) —
// ce réglage de zoom n'est qu'un filet de secours pour les cas où cette
// détection échouerait (API bloquée, réseau très lent). 100% par défaut
// : pas de recadrage permanent, le masque suffit dans la grande majorité
// des cas testés.
const NAVI_STORIES_DEFAULT_VIDEO_ZOOM = 100;
const NAVI_STORIES_MIN_VIDEO_ZOOM     = 100;
const NAVI_STORIES_MAX_VIDEO_ZOOM     = 150;

// Bordure de bulle en dégradé par défaut (repris tel quel du motif de
// référence lst-video-story : anneau "métallique" sombre/clair/sombre à
// 45°) plutôt qu'une couleur unie — "gradient" reste le réglage par
// défaut de navi_stories_bubble_border_style() ci-dessous.
const NAVI_STORIES_DEFAULT_BUBBLE_BORDER_STYLE = 'gradient';
const NAVI_STORIES_DEFAULT_GRADIENT_ANGLE       = 45;
const NAVI_STORIES_DEFAULT_GRADIENT_COLOR_1     = '#101820';
const NAVI_STORIES_DEFAULT_GRADIENT_COLOR_2     = '#cccccc';
const NAVI_STORIES_DEFAULT_GRADIENT_COLOR_3     = '#101820';

function navi_sanitize_story_border_width( $value ) {
    return max( 0, min( 20, (int) $value ) );
}

function navi_sanitize_story_phone_padding( $value ) {
    return max( 0, min( NAVI_STORIES_MAX_PHONE_PADDING, (int) $value ) );
}

function navi_sanitize_story_phone_width( $value ) {
    return max( NAVI_STORIES_MIN_PHONE_WIDTH, min( NAVI_STORIES_MAX_PHONE_WIDTH, (int) $value ) );
}

function navi_sanitize_story_bubble_size( $value ) {
    return max( NAVI_STORIES_MIN_BUBBLE_SIZE, min( NAVI_STORIES_MAX_BUBBLE_SIZE, (int) $value ) );
}

function navi_sanitize_story_border_style( $value ) {
    return in_array( $value, array( 'solid', 'gradient' ), true ) ? $value : NAVI_STORIES_DEFAULT_BUBBLE_BORDER_STYLE;
}

function navi_sanitize_story_gradient_angle( $value ) {
    return max( 0, min( 360, (int) $value ) );
}

function navi_sanitize_story_video_zoom( $value ) {
    return max( NAVI_STORIES_MIN_VIDEO_ZOOM, min( NAVI_STORIES_MAX_VIDEO_ZOOM, (int) $value ) );
}

function navi_stories_show_label() {
    return (bool) get_option( 'navi_stories_show_label', 1 );
}

/**
 * Affichage automatique après la galerie produit (voir
 * public-display.php) — désactivable pour les sites qui ne veulent
 * positionner les bulles que via le shortcode [navi_stories].
 */
function navi_stories_auto_display() {
    return (bool) get_option( 'navi_stories_auto_display', 1 );
}

function navi_stories_border_width() {
    $configured = get_option( 'navi_stories_border_width', '' );
    return '' !== $configured ? (int) $configured : NAVI_STORIES_DEFAULT_BORDER_WIDTH;
}

/**
 * Couleur de bordure en mode "solid" — pas de constante DEFAULT dédiée :
 * vide retombe sur la couleur d'accent du thème
 * (voir navi_stories_bubble_border_css_value() ci-dessous).
 */
function navi_stories_color_bubble_border() {
    return get_option( 'navi_stories_color_bubble_border', '' );
}

function navi_stories_bubble_size() {
    $configured = get_option( 'navi_stories_bubble_size', '' );
    return '' !== $configured ? (int) $configured : NAVI_STORIES_DEFAULT_BUBBLE_SIZE;
}

/**
 * "solid" (couleur unie, navi_stories_color_bubble_border()) ou
 * "gradient" (anneau en dégradé, réglage par défaut — voir
 * NAVI_STORIES_DEFAULT_BUBBLE_BORDER_STYLE).
 */
function navi_stories_bubble_border_style() {
    return get_option( 'navi_stories_bubble_border_style', NAVI_STORIES_DEFAULT_BUBBLE_BORDER_STYLE );
}

function navi_stories_bubble_gradient_angle() {
    $configured = get_option( 'navi_stories_bubble_gradient_angle', '' );
    return '' !== $configured ? (int) $configured : NAVI_STORIES_DEFAULT_GRADIENT_ANGLE;
}

function navi_stories_bubble_gradient_color_1() {
    $configured = get_option( 'navi_stories_bubble_gradient_color_1', '' );
    return $configured ? $configured : NAVI_STORIES_DEFAULT_GRADIENT_COLOR_1;
}

function navi_stories_bubble_gradient_color_2() {
    $configured = get_option( 'navi_stories_bubble_gradient_color_2', '' );
    return $configured ? $configured : NAVI_STORIES_DEFAULT_GRADIENT_COLOR_2;
}

function navi_stories_bubble_gradient_color_3() {
    $configured = get_option( 'navi_stories_bubble_gradient_color_3', '' );
    return $configured ? $configured : NAVI_STORIES_DEFAULT_GRADIENT_COLOR_3;
}

/**
 * Valeur CSS finale de --navi-story-bubble-border-bg : une couleur unie
 * (mode "solid", ou la couleur d'accent du thème si non réglée) ou une
 * fonction linear-gradient() complète (mode "gradient") — une seule
 * variable CSS couvre les deux cas, voir assets/css/stories.css (double
 * fond padding-box/border-box, `border-color: transparent`).
 */
function navi_stories_bubble_border_css_value() {
    if ( 'solid' === navi_stories_bubble_border_style() ) {
        $color = navi_stories_color_bubble_border();
        return $color ? $color : 'var(--navi-color-accent)';
    }

    return sprintf(
        'linear-gradient(%ddeg, %s, %s, %s)',
        navi_stories_bubble_gradient_angle(),
        navi_stories_bubble_gradient_color_1(),
        navi_stories_bubble_gradient_color_2(),
        navi_stories_bubble_gradient_color_3()
    );
}

/**
 * Valeur par défaut équivalente (mode "gradient", constantes
 * DEFAULT_GRADIENT_*) — sert uniquement à savoir si une surcharge doit
 * être injectée (voir stories-frontend.php), pas affichée telle quelle.
 */
function navi_stories_bubble_border_default_css_value() {
    return sprintf(
        'linear-gradient(%ddeg, %s, %s, %s)',
        NAVI_STORIES_DEFAULT_GRADIENT_ANGLE,
        NAVI_STORIES_DEFAULT_GRADIENT_COLOR_1,
        NAVI_STORIES_DEFAULT_GRADIENT_COLOR_2,
        NAVI_STORIES_DEFAULT_GRADIENT_COLOR_3
    );
}

function navi_stories_color_phone_bg() {
    $configured = get_option( 'navi_stories_color_phone_bg', '' );
    return $configured ? $configured : NAVI_STORIES_DEFAULT_PHONE_BG;
}

function navi_stories_color_close_icon() {
    $configured = get_option( 'navi_stories_color_close_icon', '' );
    return $configured ? $configured : NAVI_STORIES_DEFAULT_CLOSE_ICON;
}

function navi_stories_color_close_bg() {
    $configured = get_option( 'navi_stories_color_close_bg', '' );
    return $configured ? $configured : NAVI_STORIES_DEFAULT_CLOSE_BG;
}

function navi_stories_color_overlay() {
    $configured = get_option( 'navi_stories_color_overlay', '' );
    return $configured ? $configured : NAVI_STORIES_DEFAULT_OVERLAY_BG;
}

function navi_stories_phone_padding() {
    $configured = get_option( 'navi_stories_phone_padding', '' );
    return '' !== $configured ? (int) $configured : NAVI_STORIES_DEFAULT_PHONE_PADDING;
}

function navi_stories_phone_width() {
    $configured = get_option( 'navi_stories_phone_width', '' );
    return '' !== $configured ? (int) $configured : NAVI_STORIES_DEFAULT_PHONE_WIDTH;
}

/**
 * Zoom vidéo en %, 100-150 (voir NAVI_STORIES_DEFAULT_VIDEO_ZOOM
 * ci-dessus pour le pourquoi de ce réglage).
 */
function navi_stories_video_zoom() {
    $configured = get_option( 'navi_stories_video_zoom', '' );
    return '' !== $configured ? (int) $configured : NAVI_STORIES_DEFAULT_VIDEO_ZOOM;
}
