<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Traduction du plugin pilotée par la langue active détectée via WPML (qui
 * gère lui-même l'URL, quel que soit son format configuré : sous-dossier,
 * sous-domaine ou paramètre), avec repli sur la locale WordPress si WPML est
 * absent — sans dépendre d'un fichier .mo compilé ni d'un plugin de
 * traduction supplémentaire (Loco Translate, etc.). Un réglage explicite
 * (Navi > Navi > Langue du plugin) permet de forcer une langue quelle que
 * soit cette détection automatique — voir navi_available_languages().
 *
 * Si aucun dictionnaire ne correspond à la langue retenue, les chaînes
 * françaises d'origine s'affichent simplement telles quelles (le français
 * est la langue "source" du code, jamais absente).
 */
function navi_current_language() {
    $override = navi_current_language_override();
    if ( 'auto' !== $override ) {
        return $override;
    }

    if ( has_filter( 'wpml_current_language' ) ) {
        $lang = apply_filters( 'wpml_current_language', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- hook de WPML, pas un hook déclaré par ce plugin.
        if ( ! empty( $lang ) ) {
            return $lang;
        }
    }
    return substr( get_locale(), 0, 2 );
}

// Valeur brute du réglage (Navi > Navi > Langue du plugin) : 'auto', ou un
// code de langue forcé — contrairement à navi_current_language() ci-dessus,
// qui résout 'auto' en une langue effective. Sert à présélectionner la
// bonne option dans le <select> (voir navi_render_dashboard_page()).
function navi_current_language_override() {
    return get_option( 'navi_language', 'auto' );
}

// Langues proposées dans le sélecteur (Navi > Navi) : 'auto' + une entrée
// par dictionnaire disponible (voir navi_translate_strings() ci-dessous).
// Nom affiché dans sa propre langue (autonyme), sauf 'auto'.
function navi_available_languages() {
    return array(
        'auto' => __( 'Automatique', 'navi' ),
        'fr'   => 'Français',
        'en'   => 'English',
    );
}

function navi_sanitize_language( $value ) {
    return array_key_exists( $value, navi_available_languages() ) ? $value : 'auto';
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
 * Dictionnaire français → anglais — une entrée par chaîne __()/_e()/
 * esc_html__()/esc_html_e()/esc_attr__()/esc_attr_e() du domaine 'navi'
 * dans le code (vérifiable par un grep sur ", 'navi' )"). À compléter à
 * chaque nouvelle chaîne ajoutée ailleurs dans le plugin, sous peine de
 * mélange de langues dans le BO/front pour les sites en anglais.
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
        'Bientôt disponible' => 'Coming soon',
        'Bientôt disponible.' => 'Coming soon.',
        'Général' => 'General',
        'Actif' => 'Active',
        'Inactif' => 'Inactive',
        'Activer ce module' => 'Enable this module',
        'Réglages' => 'Settings',
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
        'Apparence' => 'Appearance',
        "Couleurs du bouton flottant et des panneaux (cookies, accessibilité), arrondis des boutons et de l'image produit (panier sticky) : à adapter à l'identité visuelle de ce site." => "Colors of the floating button and panels (cookies, accessibility), corner radius of buttons and the product image (sticky cart): adapt to this site's visual identity.",
        'Couleur principale' => 'Primary color',
        'Couleur secondaire' => 'Secondary color',
        'Arrondi des boutons (px)' => 'Button corner radius (px)',
        "Arrondi de l'image produit (px)" => 'Product image corner radius (px)',
        '0 = angles droits. Boutons concernés : bannière cookies, panier sticky.' => '0 = square corners. Affected buttons: cookie banner, sticky cart.',
        'Miniature produit affichée dans le panier sticky.' => 'Product thumbnail shown in the sticky cart.',
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
        'Haut' => 'Top',
        'Automatique' => 'Automatic',
        'Langue du plugin' => 'Plugin language',
        "Par défaut, Navi suit la langue détectée automatiquement (WPML si actif, sinon la langue du site). Choisissez une langue ici pour l'imposer, quelle que soit cette détection." => "By default, Navi follows the automatically detected language (WPML if active, otherwise the site's language). Choose a language here to force it, regardless of this detection.",

        // Panier automatique (sticky-cart).
        'Ajout au panier' => 'Add to cart',
        'Panneau produit (image, variation, ajout au panier) ancré au bouton, qui suit l\'utilisateur sur les fiches produit.' => 'Product panel (image, variation, add to cart) anchored to the button, following the user across product pages.',
        'Panier' => 'Cart',
        'Le panneau panier détecte automatiquement le prix, le nom et l\'image du produit sur la fiche produit, en essayant plusieurs emplacements courants (blocs Gutenberg, WooCommerce classique, quelques variantes de thèmes répandues). Si votre thème ne les affiche pas correctement, indiquez ici un sélecteur CSS précis : il sera essayé en priorité, avant la détection automatique. Laisser vide pour garder la détection automatique.' => "The cart panel automatically detects the product's price, name and image on the product page, trying several common locations (Gutenberg blocks, classic WooCommerce, a few common theme variants). If your theme does not display them correctly, specify a precise CSS selector here: it will be tried first, before automatic detection. Leave empty to keep automatic detection.",
        'Sélecteur CSS du prix' => 'Price CSS selector',
        'Sélecteur CSS du nom du produit' => 'Product name CSS selector',
        "Sélecteur CSS de l'image du produit" => 'Product image CSS selector',
        'Ajouter au panier - ' => 'Add to cart - ',
        'Ajout en cours...' => 'Adding...',
        'Ajouté' => 'Added',
        'Rupture de stock' => 'Out of stock',
        'Choisir une option' => 'Choose an option',
        'Navi : WooCommerce est inactif. Les modules "Panier automatique" et "Stories" ne fonctionneront pas tant que WooCommerce ne sera pas réactivé.' => 'Navi: WooCommerce is inactive. The "Automatic cart" and "Stories" modules will not work until WooCommerce is reactivated.',

        // Bannière cookies (mise à jour BO modernisé).
        "Bannière RGPD conforme au Google Consent Mode V2. À chaque choix du visiteur, si %1\$s est défini sur le site, le plugin appelle %2\$s ; il pousse aussi un événement %3\$s dans le dataLayer à chaque changement. Dans GTM : activez le Consent Mode (état par défaut refusé), et utilisez cet événement comme déclencheur personnalisé pour vos propres balises conditionnées au consentement." => "GDPR banner, compliant with Google Consent Mode V2. On every visitor choice, if %1\$s is defined on the site, the plugin calls %2\$s; it also pushes a %3\$s event to the dataLayer on every change. In GTM: enable Consent Mode (default state denied), and use this event as a custom trigger for your own tags gated on consent.",

        // Stories.
        'Stories' => 'Stories',
        'Stories (Navi)' => 'Stories (Navi)',
        'Bulles vidéo "stories" sur la fiche produit, jusqu\'à 4 par produit (YouTube ou vidéo MP4 importée).' => 'Video "stories" bubbles on the product page, up to 4 per product (YouTube or uploaded MP4 video).',
        'Afficher automatiquement après la galerie produit' => 'Automatically display after the product gallery',
        'Décocher pour positionner les bulles vous-même via le shortcode %s (dans le contenu, un constructeur de page, ou un template de thème) plutôt qu\'automatiquement après les images du produit.' => 'Uncheck to position the bubbles yourself via the %s shortcode (in the content, a page builder, or a theme template) instead of automatically after the product images.',
        'Afficher le titre de la bulle' => 'Show the bubble title',
        'Afficher sur ordinateur' => 'Show on desktop',
        'Afficher sur mobile' => 'Show on mobile',
        'Bulles' => 'Bubbles',
        'Mockup' => 'Mockup',
        'Aspect de la bulle' => 'Bubble appearance',
        'Épaisseur de la bordure' => 'Border width',
        'Type de bordure' => 'Border type',
        'Dégradé' => 'Gradient',
        'Couleur unie' => 'Solid color',
        'Couleur de la bordure' => 'Border color',
        "Vide = couleur d'accent du bouton flottant." => 'Empty = floating button accent color.',
        'Angle du dégradé' => 'Gradient angle',
        'Couleur 1' => 'Color 1',
        'Couleur 2' => 'Color 2',
        'Couleur 3' => 'Color 3',
        'Réglage par défaut : anneau dégradé sombre/clair/sombre à 45°.' => 'Default setting: dark/light/dark gradient ring at 45°.',
        'Taille de la bulle' => 'Bubble size',
        'Couleur du fond du mockup téléphone' => 'Phone mockup background color',
        'Couleur de la croix (icône)' => 'Close icon color',
        'Couleur du fond du bouton de fermeture' => 'Close button background color',
        'Couleur du fond plein écran (mobile)' => 'Fullscreen background color (mobile)',
        'Aspect du mockup' => 'Mockup appearance',
        "Épaisseur du cadre autour de l'écran vidéo et taille du mockup de téléphone (panneau desktop/laptop/tablette)." => 'Thickness of the frame around the video screen and size of the phone mockup (desktop/laptop/tablet panel).',
        "Épaisseur du cadre autour de l'écran" => 'Frame thickness around the screen',
        'Taille du mockup de téléphone' => 'Phone mockup size',
        'Zoom de la vidéo' => 'Video zoom',
        'YouTube affiche parfois son propre bandeau titre/chaîne et son filigrane "Shorts" par-dessus la vidéo (surtout au chargement et à la fin), sans moyen de le retirer autrement. Zoomer la vidéo le pousse hors du cadre visible, au prix d\'un léger recadrage sur les côtés. 100 % = aucun recadrage.' => 'YouTube sometimes shows its own title/channel bar and "Shorts" watermark over the video (especially when loading and at the end), with no other way to remove it. Zooming the video pushes it out of the visible frame, at the cost of a slight crop on the sides. 100% = no cropping.',
        'Langue, taille du texte, contraste et curseur restent toujours actifs pour les visiteurs — seule leur visibilité par appareil se règle ici.' => 'Language, text size, contrast and cursor always stay active for visitors — only their visibility per device is configured here.',

        // Onglet Stories de la fiche produit (admin-product-tab.php).
        'Jusqu\'à 4 stories par produit. Chaque story affiche une bulle vidéo cliquable sur la fiche produit. Collez une URL ou un identifiant YouTube pour un aperçu immédiat, ou importez une vidéo MP4 (max. %d Mo).' => 'Up to 4 stories per product. Each story shows a clickable video bubble on the product page. Paste a YouTube URL or ID for an instant preview, or upload an MP4 video (max. %d MB).',
        'Story #%d' => 'Story #%d',
        'Configurée' => 'Configured',
        'Vide' => 'Empty',
        'Aucune vidéo' => 'No video',
        'URL ou identifiant YouTube' => 'YouTube URL or ID',
        'Libellé affiché' => 'Displayed label',
        'Prévisualisation personnalisée (optionnel)' => 'Custom preview (optional)',
        'Sans vidéo de prévisualisation, la bulle affiche une image fixe (vignette YouTube). Pour une bulle animée (mini-vidéo en boucle, plus vivante), importez un court extrait MP4 ci-dessous.' => 'Without a preview video, the bubble shows a still image (YouTube thumbnail). For an animated bubble (looping mini video, more lively), upload a short MP4 clip below.',
        'URL de la vidéo de prévisualisation (MP4)' => 'Preview video URL (MP4)',
        'Laisser vide pour utiliser la vignette YouTube par défaut.' => 'Leave empty to use the default YouTube thumbnail.',
        '...ou importer un fichier MP4' => '...or upload an MP4 file',
        'dépasse la taille maximale autorisée' => 'exceeds the maximum allowed size',

        // Validation des uploads MP4 (data.php).
        'Erreur lors du transfert du fichier.' => 'Error while uploading the file.',
        'Le fichier dépasse la taille maximale autorisée (%d Mo).' => 'The file exceeds the maximum allowed size (%d MB).',
        'Seuls les fichiers .mp4 sont acceptés.' => 'Only .mp4 files are accepted.',
        'Le fichier ne semble pas être une vidéo MP4 valide.' => 'The file does not appear to be a valid MP4 video.',
        "Échec de l'enregistrement du fichier." => 'Failed to save the file.',
        'Stories Navi : certains fichiers ont été ignorés.' => 'Navi Stories: some files were ignored.',

        // Panneau frontend Stories (stories.js, localisé via stories-frontend.php).
        'Story précédente' => 'Previous story',
        'Story suivante' => 'Next story',
        'Vidéo' => 'Video',
    );
}
