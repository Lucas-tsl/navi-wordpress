<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Redirection unique vers Navi > Navi juste après l'activation (jamais lors
 * d'une activation groupée depuis Extensions > Extensions installées, pour
 * ne pas interrompre ce flux) + carte "Premiers pas" affichée sur l'onglet
 * Général tant qu'elle n'a pas été explicitement masquée — un plugin qui
 * regroupe 4 modules d'un coup gagne à orienter un peu le premier réglage,
 * plutôt que de laisser un nouvel utilisateur deviner par où commencer.
 */
add_action( 'activated_plugin', 'navi_marquer_activation_recente' );
function navi_marquer_activation_recente( $plugin ) {
    if ( plugin_basename( NAVI_PLUGIN_DIR . 'navi.php' ) === $plugin ) {
        set_transient( 'navi_redirect_after_activation', 1, 30 );
    }
}

add_action( 'admin_init', 'navi_rediriger_apres_activation' );
function navi_rediriger_apres_activation() {
    if ( ! get_transient( 'navi_redirect_after_activation' ) ) {
        return;
    }
    delete_transient( 'navi_redirect_after_activation' );

    if ( isset( $_GET['activate-multi'] ) || ! navi_user_can_manage() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture seule (aiguille un affichage), rien n'est écrit à partir de cette valeur ; le transient lui-même ne peut être posé que par notre propre hook d'activation.
        return;
    }
    wp_safe_redirect( admin_url( 'admin.php?page=navi-main' ) );
    exit;
}

// Masquage définitif de la carte "Premiers pas" — un simple lien protégé
// par nonce plutôt qu'un point d'entrée wp_ajax_* dédié à une seule action
// ponctuelle et sans retour visuel à animer.
add_action( 'admin_init', 'navi_masquer_premiers_pas' );
function navi_masquer_premiers_pas() {
    if ( ! isset( $_GET['navi_dismiss_welcome'] ) || ! navi_user_can_manage() ) {
        return;
    }
    check_admin_referer( 'navi_dismiss_welcome' );
    update_option( 'navi_welcome_dismissed', 1 );
    wp_safe_redirect( remove_query_arg( array( 'navi_dismiss_welcome', '_wpnonce' ) ) );
    exit;
}

// Carte affichée en tête de l'onglet Général tant qu'elle n'a pas été
// masquée — voir navi_render_dashboard_page() (admin-menu.php).
function navi_render_welcome_card() {
    if ( get_option( 'navi_welcome_dismissed' ) ) {
        return;
    }
    $dismiss_url = wp_nonce_url( add_query_arg( 'navi_dismiss_welcome', 1 ), 'navi_dismiss_welcome' );
    ?>
    <div class="navi-admin-card navi-admin-welcome">
        <a href="<?php echo esc_url( $dismiss_url ); ?>" class="navi-admin-welcome-dismiss" aria-label="<?php esc_attr_e( 'Fermer', 'saito-navi' ); ?>">✕</a>
        <h2><?php esc_html_e( 'Premiers pas avec Navi', 'saito-navi' ); ?></h2>
        <ol>
            <li><?php esc_html_e( 'Activez les modules dont vous avez besoin depuis leur propre onglet ci-dessus.', 'saito-navi' ); ?></li>
            <li><?php esc_html_e( "Adaptez les couleurs et les arrondis à votre identité visuelle dans la carte \"Apparence\" ci-dessous.", 'saito-navi' ); ?></li>
            <li><?php esc_html_e( "Vérifiez la position du bouton flottant si un autre widget occupe déjà ce coin de l'écran.", 'saito-navi' ); ?></li>
        </ol>
    </div>
    <?php
}
