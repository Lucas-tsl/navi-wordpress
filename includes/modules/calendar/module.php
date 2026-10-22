<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Libellés traduits du module, lus à la demande par le registre (voir
// 'texts', Navi_Module_Registry::register()).
function navi_calendar_module_texts() {
    return array(
        'label'       => __( 'Calendrier', 'saito-navi' ),
        'short_label' => __( 'Calendrier', 'saito-navi' ),
        'description' => __( 'Ouvre un calendrier Saito Calendar Event (calendrier de l\'Avent, compte à rebours) dans le bouton flottant, sur toutes les pages.', 'saito-navi' ),
    );
}

/**
 * Le plugin Saito Calendar Event est-il actif ? Vérifié à l'usage et non au
 * chargement de Navi : l'ordre de chargement des plugins n'est pas garanti.
 */
function navi_calendar_plugin_active() {
    return class_exists( 'Saito_CE_Public' ) && defined( 'SAITO_CE_POST_TYPE' );
}

/**
 * Calendrier affiché : celui choisi dans Navi > Calendrier, sinon le plus
 * récent publié et en cours d'affichage (sa programmation l'affiche
 * aujourd'hui) — un calendrier préparé à l'avance (ex. la Black Week en
 * octobre) ne masque pas celui qui est en ligne. 0 si aucun.
 */
function navi_calendar_id() {
    if ( ! navi_calendar_plugin_active() ) {
        return 0;
    }
    $id = (int) get_option( 'navi_calendar_id', 0 );
    if ( $id && 'publish' === get_post_status( $id ) && SAITO_CE_POST_TYPE === get_post_type( $id ) ) {
        return $id;
    }
    $recent = get_posts(
        array(
            'post_type'      => SAITO_CE_POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'fields'         => 'ids',
        )
    );
    foreach ( $recent as $calendar_id ) {
        if ( ! method_exists( 'Saito_CE_Public', 'get_schedule_state' ) || 'live' === Saito_CE_Public::get_schedule_state( Saito_CE_Public::get_schedule( $calendar_id ) ) ) {
            return (int) $calendar_id;
        }
    }
    return 0;
}

/**
 * Rendu du calendrier pour la page en cours, mis en cache le temps de la
 * requête : l'icône du menu (wp_enqueue_scripts) et le panneau (wp_footer)
 * en ont besoin tous les deux. Chaîne vide = rien à afficher (pas de
 * calendrier, hors période de publication, ou déjà présent dans la page).
 */
function navi_calendar_markup() {
    static $markup = null;
    if ( null !== $markup ) {
        return $markup;
    }
    $markup = '';
    $id     = navi_calendar_id();
    // Fiche produit : le bouton y est réservé à l'ajout au panier.
    if ( ! $id || ( function_exists( 'is_product' ) && is_product() ) ) {
        return $markup;
    }
    // La page affiche déjà ce calendrier (shortcode ou bloc) : inutile de le
    // proposer une seconde fois dans le bouton flottant.
    $post = is_singular() ? get_post() : null;
    if ( $post && ( has_shortcode( $post->post_content, 'saito_calendar_event' ) || has_block( 'saito-calendar-event/calendar', $post ) ) ) {
        return $markup;
    }
    $markup = (string) Saito_CE_Public::render_calendar( $id );
    // Hors période de publication, le calendrier ne rend que son message
    // d'attente / de fin : pas de quoi proposer une icône.
    if ( false !== strpos( $markup, 'saito-ce-schedule-message' ) ) {
        $markup = '';
    }
    return $markup;
}

Navi_Module_Registry::register(
    'calendar',
    array(
        'texts'           => 'navi_calendar_module_texts',
        'icon'            => '📅',
        'icon_svg'        => NAVI_CALENDAR_SVG,
        'option_name'     => 'navi_module_active_calendar',
        'default_active'  => true,
        'fab_action'      => 'open-calendar-panel',
        'fab_condition'   => '',
        'fab_enabled_callback' => 'navi_calendar_has_content',
        'fab_main_icon'   => 'calendar',
        'available'       => true,
        'settings_url'    => admin_url( 'admin.php?page=navi-main#calendar' ),
        'settings_panel_callback' => 'navi_calendar_render_settings_panel',
        'visibility_selector' => '.navi-fab-item[data-item-id="calendar"]',
    )
);

function navi_calendar_has_content() {
    return '' !== trim( navi_calendar_markup() );
}

// Chargé même si le module est désactivé : sinon son onglet de réglages
// disparaîtrait avec lui (voir sticky-cart/module.php).
if ( is_admin() ) {
    require_once __DIR__ . '/admin-settings.php';
}

if ( Navi_Module_Registry::is_active( 'calendar' ) && ! is_admin() ) {
    require_once __DIR__ . '/public-display.php';
}
