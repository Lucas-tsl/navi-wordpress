<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', 'navi_calendar_enqueue_assets', 20 );
function navi_calendar_enqueue_assets() {
    if ( ! navi_calendar_has_content() ) {
        return;
    }
    navi_enqueue_style( 'navi-calendar-css', NAVI_PLUGIN_URL . 'assets/css/calendar.css', array(), NAVI_VERSION );
    navi_enqueue_script( 'navi-calendar-js', NAVI_PLUGIN_URL . 'assets/js/calendar.js', array(), NAVI_VERSION, true );
}

// Rendu avant le bouton flottant (priorité 5, includes/core/frontend.php) :
// assets/js/calendar.js déplace ensuite ce panneau dans #navi-fab-detail.
// Le calendrier y charge lui-même ses scripts et styles (rendu hors
// contenu, voir Saito_CE_Public::render_calendar()), imprimés plus loin
// dans wp_footer.
add_action( 'wp_footer', 'navi_calendar_render_panel', 4 );
function navi_calendar_render_panel() {
    $markup = navi_calendar_markup();
    if ( '' === trim( $markup ) ) {
        return;
    }
    ?>
    <div id="navi-calendar-panel" class="navi-calendar-panel" tabindex="-1" role="dialog" aria-label="<?php esc_attr_e( 'Calendrier', 'saito-navi' ); ?>">
        <button type="button" class="navi-calendar-close" aria-label="<?php esc_attr_e( 'Fermer', 'saito-navi' ); ?>">✕</button>
        <div class="navi-calendar-scroll">
            <?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML du calendrier, déjà échappé par Saito Calendar Event. ?>
        </div>
    </div>
    <?php
}
