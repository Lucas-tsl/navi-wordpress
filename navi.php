<?php
/**
 * Plugin Name: Navi
 * Description: Hub d'engagement flottant pour WordPress/WooCommerce : consentement cookies (Google Consent Mode V2), ajout au panier automatique sur fiche produit, accessibilité (langue, taille du texte, contraste, curseur, soulignage des liens), pilotés depuis un bouton unique.
 * Version: 0.3.0
 * Author: Troteseil Lucas
 * Author URI: https://github.com/Lucas-tsl
 * Text Domain: navi
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Sécurité : empêche l'accès direct au fichier
}

define( 'NAVI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NAVI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NAVI_VERSION', '0.3.0' );

// Chargement des traductions
add_action( 'plugins_loaded', 'navi_charger_traductions' );
function navi_charger_traductions() {
    load_plugin_textdomain( 'navi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Noyau : registre de modules, helpers, menu admin, bouton flottant (FAB)
require_once NAVI_PLUGIN_DIR . 'includes/core/class-navi-module-registry.php';
require_once NAVI_PLUGIN_DIR . 'includes/core/helpers.php';
require_once NAVI_PLUGIN_DIR . 'includes/core/i18n.php';
require_once NAVI_PLUGIN_DIR . 'includes/core/admin-menu.php';
require_once NAVI_PLUGIN_DIR . 'includes/core/frontend.php';

// Modules : chacun s'enregistre auprès du noyau puis charge son propre code
// s'il est actif. Un futur module suit exactement ce même schéma (voir README).
require_once NAVI_PLUGIN_DIR . 'includes/modules/cookie-consent/module.php';
require_once NAVI_PLUGIN_DIR . 'includes/modules/accessibility/module.php';
require_once NAVI_PLUGIN_DIR . 'includes/modules/sticky-cart/module.php';
