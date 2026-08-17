<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function navi_enqueue_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
    wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

function navi_enqueue_script( $handle, $src, $deps = array(), $ver = false, $in_footer = true ) {
    wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

function navi_user_can_manage() {
    return current_user_can( 'manage_options' );
}

function navi_sanitize_checkbox( $value ) {
    return empty( $value ) ? 0 : 1;
}

// Whitelist stricte : toute valeur inattendue retombe sur 'right' (position
// par défaut du FAB), plutôt que de laisser passer une chaîne arbitraire.
function navi_sanitize_fab_position( $value ) {
    return ( 'left' === $value ) ? 'left' : 'right';
}

/**
 * Slug du menu admin sous lequel Navi (et ses sous-pages, comme les réglages
 * du module cookies) se rattachent : toujours son propre menu de premier
 * niveau ('navi-main'), indépendant de tout autre plugin.
 */
function navi_admin_parent_slug() {
    return 'navi-main';
}

// Triplet "R, G, B" à partir d'un hex #rrggbb ou #rgb : nécessaire pour les
// couleurs de la DA (voir Navi > Couleurs, includes/core/admin-menu.php)
// réutilisées dans des rgba(..., alpha) en CSS (assets/css/core.css), où un
// hex seul ne peut pas être converti côté navigateur. Chaîne vide si la
// valeur n'est pas un hex valide (ex. option encore vide sur ce site).
function navi_hex_to_rgb( $hex ) {
    $hex = ltrim( (string) $hex, '#' );
    if ( 3 === strlen( $hex ) ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
        return '';
    }
    return implode( ', ', array_map( 'hexdec', str_split( $hex, 2 ) ) );
}

// Couleur principale (encre) effectivement utilisée : celle choisie dans
// Navi > Couleurs si elle existe, sinon la couleur par défaut du plugin (doit
// rester synchronisée avec --navi-color-ink dans assets/css/core.css).
function navi_color_ink() {
    $configured = get_option( 'navi_color_ink', '' );
    return ! empty( $configured ) ? $configured : '#1a1a1a';
}

// Couleur secondaire (encre adoucie), même logique que navi_color_ink() (doit
// rester synchronisée avec --navi-color-ink-soft dans assets/css/core.css).
function navi_color_ink_soft() {
    $configured = get_option( 'navi_color_ink_soft', '' );
    return ! empty( $configured ) ? $configured : '#6b6b6b';
}
