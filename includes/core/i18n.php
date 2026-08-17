<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Traduction du plugin pilotée par la langue active détectée via WPML (qui
 * gère lui-même l'URL, quel que soit son format configuré : sous-dossier,
 * sous-domaine ou paramètre), sans dépendre d'un fichier .mo compilé ni
 * d'un plugin de traduction supplémentaire (Loco Translate, etc.).
 *
 * Reste utilisable si le plugin est réinstallé sur un site sans WPML : dans
 * ce cas la langue détectée retombe sur la locale WordPress, et si aucun
 * dictionnaire ne correspond, les chaînes françaises d'origine s'affichent
 * simplement telles quelles (comportement inchangé).
 */
function navi_current_language() {
    if ( has_filter( 'wpml_current_language' ) ) {
        $lang = apply_filters( 'wpml_current_language', null );
        if ( ! empty( $lang ) ) {
            return $lang;
        }
    }
    return substr( get_locale(), 0, 2 );
}

// Intercepte toutes les chaînes du text-domain 'navi' (que ce soit via
// __(), _e(), esc_html__(), esc_html_e(), esc_attr__() ou esc_attr_e() :
// toutes passent par ce même filtre) pour les remplacer par leur traduction
// si la langue active correspond à un dictionnaire connu.
add_filter( 'gettext', 'navi_translate_strings', 10, 3 );
function navi_translate_strings( $translated, $original, $domain ) {
    if ( 'navi' !== $domain ) {
        return $translated;
    }

    $dictionaries = array(
        'en' => navi_dictionary_en(),
    );

    $lang = navi_current_language();
    if ( ! isset( $dictionaries[ $lang ] ) ) {
        return $translated;
    }

    $dictionary = $dictionaries[ $lang ];
    return isset( $dictionary[ $original ] ) ? $dictionary[ $original ] : $translated;
}

/**
 * Dictionnaire français → anglais. Reprend exactement les chaînes de
 * languages/navi-en_US.po (à garder synchronisés si l'un des deux est mis à jour).
 */
function navi_dictionary_en() {
    return array(
        'Haut de page' => 'Back to top',
        'Ouvrir le menu' => 'Open menu',
        'Fermer' => 'Close',
        "Nous utilisons des cookies pour assurer le bon fonctionnement du site, analyser notre trafic et personnaliser nos publicités. Vous pouvez choisir vos préférences ci-dessous." => 'We use cookies to ensure the site works properly, analyze our traffic, and personalize our ads. You can choose your preferences below.',
        'Consentement cookies' => 'Cookie consent',
        'Cookies' => 'Cookies',
        'Bannière RGPD et Google Consent Mode V2, connectée au DataLayer GTM.' => 'GDPR banner and Google Consent Mode V2, connected to the GTM DataLayer.',
        'Logo' => 'Logo',
        'Gérer le consentement' => 'Manage consent',
        'Politique de confidentialité' => 'Privacy policy',
        'Mentions légales' => 'Legal notice',
        'Tout Accepter' => 'Accept All',
        'Tout Refuser' => 'Reject All',
        'Personnaliser mes choix' => 'Customize my choices',
        'Préférences des cookies' => 'Cookie preferences',
        'Strictement Nécessaires' => 'Strictly Necessary',
        'Requis pour le site (panier, sécurité). Non désactivables.' => 'Required for the site (cart, security). Cannot be disabled.',
        'Statistiques (Google Analytics)' => 'Statistics (Google Analytics)',
        "Pour mesurer l'audience de la boutique." => "To measure the store's audience.",
        'Marketing (Pixel Facebook, Google Ads)' => 'Marketing (Facebook Pixel, Google Ads)',
        'Pour afficher des publicités ciblées.' => 'To display targeted ads.',
        'Enregistrer mes choix' => 'Save my choices',
        'Annuler' => 'Cancel',
        'Préférences enregistrées' => 'Preferences saved',
        'Gérer les cookies' => 'Manage cookies',
        'Accessibilité' => 'Accessibility',
        'Langue (via WPML, ou GTranslate si présent sur la page), taille du texte, contraste élevé, curseur agrandi et soulignage des liens.' => 'Language (via WPML, or GTranslate if present on the page), text size, high contrast, enlarged cursor and underlined links.',
        "Vous n'avez pas les permissions nécessaires pour accéder à cette page." => 'You do not have sufficient permissions to access this page.',
        'Activez ou désactivez les modules pilotés par le bouton flottant du site.' => "Enable or disable the modules driven by the site's floating button.",
        'Module' => 'Module',
        'Description' => 'Description',
        'Actif' => 'Active',
        'Bientôt disponible' => 'Coming soon',
        'Réglages' => 'Settings',
        'Réglages Bannière Cookie' => 'Cookie Banner Settings',
        'Configuration de la Bannière Cookie (GTM Edition)' => 'Cookie Banner Configuration (GTM Edition)',
        'Note : ce module communique directement avec Google Tag Manager via le Google Consent Mode V2.' => 'Note: this module communicates directly with Google Tag Manager via Google Consent Mode V2.',
        'URL du Logo' => 'Logo URL',
        'Laisser vide pour utiliser automatiquement le logo du site (Apparence > Personnaliser > Identité du site).' => "Leave empty to automatically use the site's logo (Appearance > Customize > Site Identity).",
        "Laisser vide si aucun logo ne doit apparaître. Le site n'a pas encore de logo configuré dans Apparence > Personnaliser > Identité du site — dès qu'il en aura un, il sera utilisé automatiquement ici." => "Leave empty if no logo should appear. The site does not yet have a logo configured in Appearance > Customize > Site Identity — as soon as it does, it will be used automatically here.",
        'Texte de la bannière' => 'Banner text',
        'URL Politique de confidentialité' => 'Privacy Policy URL',
        'URL Mentions légales' => 'Legal Notice URL',
        'Navi' => 'Navi',
        'Position du bouton flottant' => 'Floating button position',
        "Coin de l'écran" => 'Screen corner',
        'Bas droite (par défaut)' => 'Bottom right (default)',
        'Bas gauche' => 'Bottom left',
        "À changer si un autre widget flottant (chat, WhatsApp...) occupe déjà le bas droite du site." => "Change this if another floating widget (chat, WhatsApp...) already occupies the bottom right of the site.",
        'Couleurs du plugin' => 'Plugin colors',
        "Couleurs du bouton flottant et des panneaux (cookies, accessibilité) : à adapter à l'identité visuelle de ce site." => "Colors of the floating button and panels (cookies, accessibility): adapt to this site's visual identity.",
        'Couleur principale' => 'Primary color',
        'Couleur secondaire' => 'Secondary color',
        'Langue' => 'Language',
        'Taille du texte' => 'Text size',
        'Réduire la taille du texte' => 'Decrease text size',
        'Augmenter la taille du texte' => 'Increase text size',
        'Contraste élevé' => 'High contrast',
        'Curseur agrandi' => 'Enlarged cursor',
        'Souligner les liens' => 'Underline links',
        'Réinitialiser les réglages' => 'Reset settings',
        'Aller aux réglages (accessibilité, cookies, panier)' => 'Go to settings (accessibility, cookies, cart)',
        'Aller au contenu' => 'Skip to content',
    );
}
