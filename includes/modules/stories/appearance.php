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

function navi_sanitize_story_border_width( $value ) {
    return max( 0, min( 20, (int) $value ) );
}

function navi_sanitize_story_phone_padding( $value ) {
    return max( 0, min( NAVI_STORIES_MAX_PHONE_PADDING, (int) $value ) );
}

function navi_sanitize_story_phone_width( $value ) {
    return max( NAVI_STORIES_MIN_PHONE_WIDTH, min( NAVI_STORIES_MAX_PHONE_WIDTH, (int) $value ) );
}

function navi_stories_show_label() {
    return (bool) get_option( 'navi_stories_show_label', 1 );
}

function navi_stories_border_width() {
    $configured = get_option( 'navi_stories_border_width', '' );
    return '' !== $configured ? (int) $configured : NAVI_STORIES_DEFAULT_BORDER_WIDTH;
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
