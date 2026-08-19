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

// Entier positif borné (0-50px) : un arrondi négatif n'a pas de sens, une
// valeur absurdement grande casserait la mise en page des petits boutons.
function navi_sanitize_radius( $value ) {
    $value = (int) $value;
    return max( 0, min( 50, $value ) );
}

// Arrondi des boutons (bannière cookies, panier sticky) — doit rester
// synchronisé avec --navi-radius-button dans assets/css/core.css.
function navi_radius_button() {
    $configured = get_option( 'navi_radius_button', '' );
    return '' !== $configured ? (int) $configured : 4;
}

// Arrondi de l'image produit (miniature du panier sticky) — doit rester
// synchronisé avec --navi-radius-image dans assets/css/core.css.
function navi_radius_image() {
    $configured = get_option( 'navi_radius_image', '' );
    return '' !== $configured ? (int) $configured : 4;
}

// Visibilité par appareil (un module donné, voir 'visibility_selector' dans
// Navi_Module_Registry) : options navi_show_desktop_<id>/navi_show_mobile_<id>,
// visibles par défaut (get_option avec default 1) tant que le marchand ne
// les a pas explicitement désactivées.
function navi_show_desktop( $module_id ) {
    return (bool) get_option( 'navi_show_desktop_' . $module_id, 1 );
}

function navi_show_mobile( $module_id ) {
    return (bool) get_option( 'navi_show_mobile_' . $module_id, 1 );
}

// En-tête partagé de chaque page Navi (assets/css/admin.css, .navi-admin-header)
// : logo + titre + sous-titre optionnel, pour ne pas dupliquer ce balisage
// dans chaque admin-settings.php de module.
function navi_admin_page_header( $title, $subtitle = '' ) {
    ?>
    <div class="navi-admin-header">
        <img src="<?php echo esc_url( NAVI_PLUGIN_URL . 'assets/img/logo-256.png' ); ?>" alt="" />
        <div>
            <h1><?php echo esc_html( $title ); ?></h1>
            <?php if ( '' !== $subtitle ) : ?>
                <p><?php echo esc_html( $subtitle ); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// À appeler juste après settings_fields() dans chaque onglet de Navi > Navi
// (voir navi_render_dashboard_page(), admin-menu.php) : le champ
// _wp_http_referer que settings_fields() ajoute déjà ne capture que
// REQUEST_URI côté serveur, qui ne contient jamais le fragment d'URL
// (#onglet) — un navigateur ne l'envoie jamais au serveur. Ce second champ,
// de même nom, est rempli à la soumission du formulaire (pas au chargement
// de la page : l'onglet actif — donc le hash — peut avoir changé entretemps)
// avec l'URL complète telle que vue par le navigateur ; comme les deux
// champs partagent le même name="_wp_http_referer", PHP ne garde dans
// $_POST que la dernière valeur soumise — la nôtre, placée après. Sans ça,
// tout enregistrement ramènerait sur l'onglet "Général" (redirection
// d'options.php, qui n'a jamais connu le hash).
function navi_render_hash_preserving_referer_field() {
    ?>
    <input type="hidden" name="_wp_http_referer" value="" />
    <script>
        (function () {
            var input = document.currentScript.previousElementSibling;
            var form = document.currentScript.closest( 'form' );
            if ( form ) {
                form.addEventListener( 'submit', function () {
                    input.value = window.location.pathname + window.location.search + window.location.hash;
                } );
            }
        })();
    </script>
    <?php
}

// Case à cocher "Activer ce module", en tête de chaque onglet de module
// (Navi > Navi, voir navi_render_dashboard_page(), admin-menu.php) — un
// module désactivé n'affiche plus rien côté front (Navi_Module_Registry::is_active(),
// vérifié par chaque includes/modules/<nom>/module.php au chargement).
function navi_render_module_active_field( $module_id ) {
    $module = Navi_Module_Registry::get( $module_id );
    if ( ! $module ) {
        return;
    }
    ?>
    <tr valign="top">
        <th scope="row"><?php esc_html_e( 'Activer ce module', 'navi' ); ?></th>
        <td>
            <input type="hidden" name="<?php echo esc_attr( $module['option_name'] ); ?>" value="0" />
            <input
                type="checkbox"
                name="<?php echo esc_attr( $module['option_name'] ); ?>"
                value="1"
                <?php checked( 1, get_option( $module['option_name'], $module['default_active'] ? 1 : 0 ) ); ?>
                <?php disabled( ! $module['available'] ); ?>
            />
        </td>
    </tr>
    <?php
}

// Deux cases à cocher "Afficher sur ordinateur"/"Afficher sur mobile" pour
// un module donné — même bloc de balisage réutilisé par chaque
// admin-settings.php de module concerné (cookie-consent, accessibility,
// sticky-cart), pour ne pas dupliquer ce HTML trois fois.
function navi_render_visibility_fields( $module_id ) {
    $desktop_name = 'navi_show_desktop_' . $module_id;
    $mobile_name  = 'navi_show_mobile_' . $module_id;
    ?>
    <tr valign="top">
        <th scope="row"><?php esc_html_e( 'Afficher sur ordinateur', 'navi' ); ?></th>
        <td>
            <input type="hidden" name="<?php echo esc_attr( $desktop_name ); ?>" value="0" />
            <input type="checkbox" name="<?php echo esc_attr( $desktop_name ); ?>" id="<?php echo esc_attr( $desktop_name ); ?>" value="1" <?php checked( navi_show_desktop( $module_id ) ); ?> />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><?php esc_html_e( 'Afficher sur mobile', 'navi' ); ?></th>
        <td>
            <input type="hidden" name="<?php echo esc_attr( $mobile_name ); ?>" value="0" />
            <input type="checkbox" name="<?php echo esc_attr( $mobile_name ); ?>" id="<?php echo esc_attr( $mobile_name ); ?>" value="1" <?php checked( navi_show_mobile( $module_id ) ); ?> />
        </td>
    </tr>
    <?php
}
