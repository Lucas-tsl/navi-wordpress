<?php
/**
 * Bootstrap PHPUnit — délibérément SANS la suite de tests WordPress
 * (wp-phpunit), qui exige une base de données MySQL et un checkout complet
 * de WordPress. Les fonctions couvertes ici (validation MP4, extraction
 * d'ID YouTube, sanitisation des couleurs/rayons/réglages Stories) sont
 * toutes des fonctions pures ou quasi pures : seuls quelques bouchons
 * minimalistes des fonctions WordPress qu'elles appellent (__(), add_filter())
 * suffisent à les charger et à les exécuter isolément, bien plus vite qu'un
 * environnement WordPress complet.
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

require_once __DIR__ . '/../includes/core/helpers.php';
require_once __DIR__ . '/../includes/core/i18n.php';
require_once __DIR__ . '/../includes/modules/stories/data.php';
require_once __DIR__ . '/../includes/modules/stories/appearance.php';
