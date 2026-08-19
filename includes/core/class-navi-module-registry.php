<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registre central des modules du hub. Chaque module s'y déclare une fois
 * (voir includes/modules/.../module.php) ; le menu admin et le bouton flottant
 * du front-end lisent ce registre pour savoir quoi afficher, sans se
 * connaître les uns les autres.
 */
class Navi_Module_Registry {

    private static $modules = array();

    public static function register( $id, array $args ) {
        $defaults = array(
            'label'           => $id,
            'icon'            => '⚙️',
            // SVG monochrome (chaîne de balisage, currentColor) à préférer à
            // 'icon' pour le menu du bouton flottant : le rendu des emojis
            // varie trop d'un système à l'autre pour rester lisible une fois
            // désaturé. 'icon' reste utilisé tel quel dans le tableau de bord.
            'icon_svg'        => '',
            'description'     => '',
            'option_name'     => 'navi_module_active_' . $id,
            'default_active'  => true,
            'settings_url'    => '',
            // Callable qui affiche le contenu de l'onglet de ce module dans
            // Navi > Navi (voir navi_render_dashboard_page(), admin-menu.php) —
            // remplace l'ancienne page de réglages dédiée par module.
            'settings_panel_callback' => '',
            // Action déclenchée sur le bus d'événements front-end ('navi:action')
            // quand l'icône du module est cliquée dans le menu du bouton flottant.
            'fab_action'      => '',
            // Condition d'affichage de l'icône dans le menu : '' (toujours),
            // 'is_product' (fiche produit) ou 'scroll' (scroll > scrollThreshold %).
            'fab_condition'   => '',
            // false = module déclaré mais pas encore développé ("bientôt disponible").
            'available'       => true,
            // Sélecteur(s) CSS ciblant ce que le module affiche sur le site
            // (bannière, icône du menu du FAB...) — utilisé par la
            // visibilité par appareil (options navi_show_desktop_<id>/
            // navi_show_mobile_<id>, voir includes/core/frontend.php).
            // Chaîne vide = pas de réglage de visibilité par appareil pour
            // ce module (ex. un futur module sans rien à masquer par écran).
            'visibility_selector' => '',
        );

        self::$modules[ $id ] = wp_parse_args( $args, $defaults );
    }

    public static function all() {
        return self::$modules;
    }

    public static function get( $id ) {
        return isset( self::$modules[ $id ] ) ? self::$modules[ $id ] : null;
    }

    public static function is_active( $id ) {
        $module = self::get( $id );
        if ( ! $module || ! $module['available'] ) {
            return false;
        }
        return (bool) get_option( $module['option_name'], $module['default_active'] ? 1 : 0 );
    }

    public static function active_modules() {
        return array_filter(
            array_keys( self::$modules ),
            array( __CLASS__, 'is_active' )
        );
    }
}
