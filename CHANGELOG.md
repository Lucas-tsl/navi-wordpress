# Changelog

## Non publié

Chantier 7 : préparation à la soumission WordPress.org, y compris la
correction de tous les points relevés par l'outil officiel **Plugin
Check**, plus deux corrections front trouvées pendant cette passe.

- `readme.txt` au format strict WordPress.org, **rédigé en anglais**
  (obligatoire depuis la politique WordPress.org de juillet 2025 —
  `readme_description_non_official_language`), en-tête `=== Saito Navi
  ===` (en-tête `Plugin Name` renommé : "Navi" seul est trop court,
  minimum 5 lettres latines requis par WordPress.org), `Tested up to:
  7.0`.
- `.wordpress-org/assets/` : icônes 128×128/256×256, bannière 772×250
  (générées à partir du logo existant), et 8 captures d'écran (Back
  Office et front, `screenshot-1.png` à `screenshot-8.png`) — exclu du
  zip du plugin (`.distignore`), destiné au dossier `assets/` du dépôt
  **SVN** WordPress.org, voir README.md section "Soumission
  WordPress.org".
- `navi.php` : en-têtes `Requires at least`/`Requires PHP` ajoutés ;
  suppression de l'appel à `load_plugin_textdomain()` (discouraged
  depuis WP 4.6 pour les plugins hébergés sur WordPress.org).
- `.distignore` : `scripts/` et `docker-compose.yml` désormais exclus du
  zip distribué (`application_detected` — les scripts de développement
  ne doivent pas être livrés avec le plugin).
- `includes/modules/stories/data.php` : upload des prévisualisations MP4
  réécrit avec `wp_handle_upload()` (redirigé vers `navi-stories/` via le
  filtre `upload_dir`) à la place de `move_uploaded_file()` direct,
  interdit par les règles WordPress.org (`Generic.PHP.ForbiddenFunctions`).
  Comportement inchangé (toujours hors médiathèque, toujours dans le
  dossier dédié).
- **Corrigé** : le fond blanc du panneau desktop Stories (mockup de
  téléphone) laissait apparaître un flash de carte claire autour du
  mockup à l'ouverture — panneau rendu transparent pour ce module
  uniquement (`assets/css/core.css`), même correctif que Navi
  PrestaShop.
- **Corrigé** : les libellés de l'onglet Stories (fiche produit) se
  chevauchaient avec le champ du dessus — le CSS de WooCommerce
  (`.woocommerce_options_panel label`) applique `float:left` et une
  marge négative (`margin-left:-150px`) à tous les `<label>`, jamais
  neutralisés par notre propre CSS (`includes/modules/stories/admin-product-tab.php`).
- Validé : le hub d'engagement et les bulles Stories fonctionnaient
  normalement en admin connecté, mais restaient invisibles pour un
  visiteur non connecté testé en CLI — cause identifiée comme un réglage
  **WooCommerce "Coming soon" activé sur l'instance de dev locale**
  (`woocommerce_coming_soon`), pas un bug du plugin ; désactivé sur
  l'environnement Docker pour fiabiliser les futurs tests.
- Reste à faire avant soumission effective (non automatisable) :
  validation de `readme.txt` via le validateur officiel une fois le dépôt
  SVN attribué.

## 0.4.0

Chantier 6 : gestion native des Stories (bulles vidéo produit) — mêmes
fonctionnalités et même apparence que Navi PrestaShop, aucune dépendance à
`lst-video-story` ni à Advanced Custom Fields.

- Nouveau module **Stories** : jusqu'à 4 bulles vidéo par produit
  (YouTube ou MP4 importé), onglet dédié sur la fiche produit WooCommerce
  (`woocommerce_product_data_tabs`/`_panels`) avec aperçu vignette YouTube
  en direct, badge Vide/Configurée par emplacement, avertissement de
  taille de fichier avant envoi.
- Sauvegarde protégée par nonce dédié (`navi_story_nonce`) et
  `current_user_can('edit_product', ...)`, upload MP4 validé (extension,
  MIME, taille max 20 Mo) et stocké hors du dossier du plugin
  (`wp_upload_dir()['basedir'] . '/navi-stories/'`, conforme aux
  recommandations WordPress.org).
- Rendu front (`woocommerce_product_thumbnails`) : bulles affichées après
  la galerie produit, panneau desktop avec mockup de téléphone en CSS pur
  (aucun asset image), plein écran mobile type stories (défilement
  snap, swipe pour fermer, piège à focus), lecteur YouTube nocookie —
  moteur porté quasi verbatim depuis Navi PrestaShop.
- Réglages **Navi > Stories** : afficher le titre de la bulle, épaisseur
  de bordure, 4 couleurs (fond du mockup, icône/fond du bouton de
  fermeture, fond plein écran), curseurs padding/largeur du mockup avec
  aperçu en direct — mêmes variables CSS `--navi-story-*` et mêmes
  valeurs par défaut que la version PrestaShop.
- Stockage en `postmeta` (`_navi_stories`) plutôt qu'une table dédiée,
  plus idiomatique côté WordPress (pas de gestion d'installation/
  désinstallation de table, profite du cache objet natif).
- Deux bugs trouvés et corrigés pendant la vérification en conditions
  réelles :
  - un commentaire PHP contenant littéralement `*/` au milieu d'une
    phrase (`includes/modules/stories/appearance.php`) fermait
    prématurément le bloc de commentaire, provoquant une erreur de
    syntaxe ;
  - `global $product` n'est pas fiable à l'étape `wp_enqueue_scripts`
    (peut être une chaîne plutôt qu'un `WC_Product` selon le thème) —
    `includes/modules/stories/stories-frontend.php` utilise désormais
    `get_the_ID()` à ce stade.

## 0.3.0

Chantier 5 : parité d'apparence avec Navi PrestaShop — tout ce qui est
réglable côté PrestaShop doit avoir son équivalent côté WordPress.

- Alignement de l'API JS sur Navi PrestaShop : `window.naviHub` →
  `window.navi`, `naviHubConfig` → `naviConfig` (même nom des deux côtés).
- Logo Navi (identique à la version PrestaShop) comme icône du menu admin,
  à la place du dashicon générique.
- **Arrondis** : réglages "Arrondi des boutons"/"Arrondi de l'image
  produit" (Navi > Apparence, ex-"Couleurs du plugin"), variables CSS
  `--navi-radius-button`/`--navi-radius-image` (défaut 4px, comme
  `DEFAULT_RADIUS_BUTTON`/`DEFAULT_RADIUS_IMAGE` côté PrestaShop).
- **Visibilité par appareil** : chaque module avec un affichage propre
  (cookies, accessibilité, panier) gagne un réglage "Afficher sur
  ordinateur"/"Afficher sur mobile" dans sa page de réglages —
  mécanisme inspiré de `VISIBILITY_TOGGLES`/`getConfigStyleTag()` côté
  PrestaShop. Nouveau : `includes/modules/accessibility/admin-settings.php`
  (le module accessibilité n'avait encore aucune page de réglages).

## 0.2.0

Chantier 2 : généralisation du panneau panier (`sticky-cart`), la partie la
plus délicate — produits simples et à variations.

- Module **Panier automatique** porté depuis `hub-jozz` (implémentation la
  plus aboutie des 3 forks : cache `lastFoundVariation` pour éviter une
  race condition sur le prix affiché, lecture de couleur via WCBoost
  Variation Swatches si ce plugin est présent (repli `<select>` natif
  sinon), sélecteur de teinte personnalisé navigable au clavier (motif
  WAI-ARIA `listbox`), réouverture après fermeture manuelle via une icône
  dédiée dans le menu du bouton flottant.
- Chaîne de sélecteurs CSS (prix/nom/image) fusionnée à partir des 3 sites
  sources, généralisée (sélecteurs propres à un site précis retirés) et
  complétée par un **nouveau réglage admin** (Navi > Panier) : sélecteur
  CSS personnalisé par donnée, essayé en priorité si le thème n'est
  reconnu par aucun des sélecteurs intégrés.
- Environnement de test local enrichi : catalogue de démonstration (3
  produits simples — en stock, en rupture, en promo — et 2 produits à
  variations — couleur seule, couleur × taille), plugin WCBoost Variation
  Swatches installé pour exercer le vrai chemin de lecture de couleur.
- Deux bugs trouvés et corrigés pendant la vérification en conditions
  réelles (catalogue de test WooCommerce, Playwright) :
  - un ID mal renommé dans `core.css` (`#naviStickyVariationBar` au lieu
    de `#navi-sticky-bar`) empêchait le panneau de devenir visible dans
    quasiment tous les cas — présent depuis le premier commit du module,
    jamais déployé publiquement ;
  - la détection du pied de page réel (`checkVisibility()`,
    `assets/js/sticky-cart.js`) matchait n'importe quelle balise
    `<footer>` de la page, y compris celles internes au bloc natif
    WordPress "Derniers commentaires" (`<footer class="wp-block-latest-comments__comment-meta">`)
    — masquait le panneau dès le moindre scroll sur un site utilisant ce
    bloc. Corrigé en priorisant `[role="contentinfo"]` (repère sémantique
    réservé au vrai pied de page), avec repli sur le dernier `<footer>` du
    DOM si absent.

## 0.1.0

Chantier 1 : bootstrap du plugin public à partir des trois forks
site-spécifiques privés (`hub-lsg`, `hub-pe`, `hub-jozz`).

- Noyau : bouton flottant à 3 états (fermé/menu/détail), registre de
  modules découplé, position configurable, couleurs de marque
  personnalisables (couleur principale/secondaire).
- Module **Consentement cookies** : bannière RGPD, Google Consent Mode V2,
  modale de préférences, logo auto-détecté depuis l'identité du site
  WordPress si aucune URL n'est configurée.
- Module **Accessibilité** : sélecteur de langue (WPML, repli GTranslate si
  WPML absent), taille du texte (avec détection générique des variables
  CSS de typographie fluide `*font-size*`, pas liée à un thème précis),
  contraste élevé, curseur agrandi, soulignage des liens.
- Module **Panier automatique** (`sticky-cart`) : pas encore porté — prochain
  chantier (généralisation des sélecteurs par thème et de la gestion des
  produits à variations).
- CI GitHub Actions (lint PHP multi-versions, lint JS/CSS, build du zip),
  environnement Docker local (`docker-compose.yml` + `scripts/deploy-local.sh`).
