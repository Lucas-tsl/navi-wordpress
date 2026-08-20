<?php
/**
 * Bootstrap PHPUnit — délibérément SANS la suite de tests WordPress
 * (wp-phpunit), qui exige une base de données MySQL et un checkout complet
 * de WordPress. Les fonctions couvertes ici (extraction d'ID YouTube,
 * sanitisation des couleurs/rayons/réglages Stories, registre des modules,
 * repli de langue de l'accessibilité) sont toutes des fonctions pures ou
 * quasi pures : seuls quelques bouchons minimalistes des fonctions
 * WordPress qu'elles appellent (__(), add_filter(), add_action(),
 * has_filter(), apply_filters(), wp_parse_args()) suffisent à les charger
 * et à les exécuter isolément, bien plus vite qu'un environnement
 * WordPress complet.
 *
 * Si un test a besoin d'une fonction WordPress non bouchonnée ici, c'est
 * probablement le signe qu'il teste autre chose qu'une fonction de
 * validation pure — à couvrir plutôt par un test manuel (Playwright) ou une
 * future suite basée sur wp-phpunit, pas en ajoutant des bouchons ad hoc.
 */

define( 'ABSPATH', __DIR__ . '/../' );

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = null ) {
        return $text;
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        return true;
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        return true;
    }
}

// Bouchons de test pour has_filter()/apply_filters() : contrairement à
// add_filter() ci-dessus (no-op), ceux-ci s'appuient sur un registre en
// mémoire ($GLOBALS['navi_test_filters']) que les tests peuvent
// remplir/vider eux-mêmes (voir AccessibilityTest::navi_a11y_get_languages(),
// qui a besoin de simuler la présence ou l'absence du filtre WPML
// 'wpml_active_languages').
$GLOBALS['navi_test_filters'] = array();

if ( ! function_exists( 'has_filter' ) ) {
    function has_filter( $tag, $function_to_check = false ) {
        return ! empty( $GLOBALS['navi_test_filters'][ $tag ] );
    }
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $tag, $value, ...$args ) {
        if ( empty( $GLOBALS['navi_test_filters'][ $tag ] ) ) {
            return $value;
        }
        return call_user_func( $GLOBALS['navi_test_filters'][ $tag ], $value, ...$args );
    }
}

// Port fidèle (cas $args tableau uniquement, seul cas utilisé par ce plugin)
// de wp_parse_args() : nécessaire pour charger Navi_Module_Registry::register(),
// qui fusionne les arguments d'un module avec ses valeurs par défaut.
if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = array() ) {
        return array_merge( $defaults, (array) $args );
    }
}

require_once __DIR__ . '/../includes/core/helpers.php';
require_once __DIR__ . '/../includes/core/i18n.php';
require_once __DIR__ . '/../includes/core/class-navi-module-registry.php';
require_once __DIR__ . '/../includes/modules/stories/data.php';
require_once __DIR__ . '/../includes/modules/stories/appearance.php';
require_once __DIR__ . '/../includes/modules/accessibility/public-display.php';
