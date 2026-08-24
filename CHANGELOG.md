# Changelog

## 0.7.2

- Stories s'intègre au panneau "Navi" partagé du plugin compagnon Navi FAQ
  quand celui-ci est actif : les deux fonctionnalités deviennent des
  onglets internes d'un seul panneau (sous l'éditeur de description) au
  lieu d'avoir chacune sa propre entrée dans "Données produit". Sans Navi
  FAQ, Stories garde son onglet WooCommerce autonome comme avant — aucune
  dépendance dure ajoutée entre les deux plugins.

## 0.7.1

- Retour de revue WordPress.org : tous les `<script>`/`<style>` imprimés
  en dur en PHP sont remplacés par `wp_register_script()`/
  `wp_enqueue_script()`/`wp_localize_script()` et `wp_enqueue_style()`/
  `wp_add_inline_style()` (champ referer hash-preserving, injection du
  Consent Mode, onglets du dashboard admin, aperçu live des réglages
  Stories, onglet Stories de la fiche produit). Aucun changement de
  comportement.
- Documenté dans le readme l'appel `wp_remote_head()` vers
  `img.youtube.com` (vérification de disponibilité de la vignette HD
  YouTube).

## 0.7.0

- Story MP4 : la vidéo de prévisualisation se choisit désormais directement
  dans la médiathèque WordPress (`wp.media`, restreinte aux fichiers
  `video/mp4`) plutôt que via un champ fichier brut — plus besoin de gérer
  son propre dossier d'upload, la validation et le nettoyage à la
  désinstallation ; l'ancien pipeline (`navi_validate_mp4_upload()` et son
  dossier dédié) est retiré.
- Corrigé : vignette YouTube de secours qui pouvait rester cassée pour les
  Shorts (format vertical, sans `maxresdefault.jpg`) — repli automatique
  sur `hqdefault.jpg` si le format haute résolution n'existe pas.
- Suite PHPUnit étendue : fonctions de validation Stories (extraction
  d'ID YouTube, réglages d'apparence), repli de langue de l'Accessibilité
  (WPML/GTranslate), `Navi_Module_Registry` — 37 tests au total.

## 0.6.0

- Bloc Gutenberg "Navi Stories" (`navi/stories`), équivalent visuel du
  shortcode `[navi_stories]` (réglage "Product ID" dans l'inspecteur,
  aperçu en direct via ServerSideRender) — bloc dynamique, rendu toujours
  délégué à `navi_stories_shortcode()`, jamais dupliqué.
- Onboarding : redirection unique vers Navi > Navi après l'activation
  (jamais lors d'une activation groupée), carte "Premiers pas" sur
  l'onglet Général tant qu'elle n'est pas masquée.
- Compatibilité WooCommerce Blocks vérifiée empiriquement (thème FSE,
  Single Product en blocs) : le formulaire de variations classique
  persiste sous les blocs, la détection existante fonctionne sans
  changement. Seul le bloc expérimental "Add to Cart + Options (Beta)"
  de WooCommerce (entièrement piloté en JS, sans formulaire classique)
  n'est pas pris en charge — documenté dans le FAQ (readme.txt).

## 0.5.0

- Préparation à la soumission WordPress.org : `readme.txt` en anglais,
  en-tête `Saito Navi`, icônes/bannière/captures d'écran
  (`.wordpress-org/assets/`), conformité **Plugin Check** (scripts de dev
  exclus du zip, upload MP4 via `wp_handle_upload()`, en-têtes de
  compatibilité).
- Module Stories : shortcode `[navi_stories]` pour un affichage manuel en
  plus du hook automatique, réglage "Afficher automatiquement".
- Aspect des bulles Stories (Navi > Stories > Bulles, nouvel onglet) :
  taille réglable, bordure unie ou en dégradé (dégradé par défaut),
  aperçu en direct.
- Corrigé : flash de fond blanc à l'ouverture du panneau Stories ;
  libellés qui se chevauchaient dans l'onglet Stories (conflit avec le
  CSS WooCommerce) ; bandes noires en haut/bas de la vidéo (conflit
  `max-width` avec certains thèmes, dont Storefront) ; icône de menu
  admin décalée par un padding WordPress par défaut ; centrage de
  l'engrenage renforcé pour Safari/iOS ; mélange français/anglais dans le
  BO sur les sites en anglais (dictionnaire de traduction incomplet).
- BO redessiné (cartes, cases à cocher modernes, onglets en pastilles)
  plutôt que l'apparence WordPress par défaut.
- Langue du plugin réglable manuellement (Navi > Navi), indépendamment de
  la détection automatique (WPML ou langue du site).
- Réglages regroupés sur une seule page à onglets (Navi > Navi : Général,
  Cookies, Accessibilité, Panier, Stories), chaque onglet portant sa
  propre activation — remplace les anciennes sous-pages séparées.
- Sécurité : faille XSS corrigée dans le panneau plein écran Stories
  (libellé de story injecté sans échappement).
- Corrigé : bordure bleue résiduelle sur le sélecteur de couleur, logo
  Navi désormais à fond transparent, interrupteurs remplacés par des
  cases à cocher (rendu incohérent sur certaines configurations
  GPU/pilote Windows).
- Onglet Cookies : instructions concrètes pour configurer Google Tag
  Manager (Consent Mode, événement dataLayer poussé par le plugin) à la
  place d'une simple description.
- Corrigé : `Text Domain` (navi.php) et domaine de toutes les chaînes
  traduisibles alignés sur le slug WordPress.org réel (`saito-navi`,
  pas `navi`) — remonté par Plugin Check en `textdomain_mismatch`.
  Dossier de déploiement local, job de build CI et instructions SVN mis
  à jour en conséquence.

## 0.4.0

Module **Stories** natif : jusqu'à 4 bulles vidéo par produit (YouTube ou
MP4 importé), onglet dédié sur la fiche produit, panneau desktop avec
mockup de téléphone en CSS pur, plein écran mobile — mêmes
fonctionnalités que Navi PrestaShop, sans dépendance à `lst-video-story`.
Stockage en postmeta, upload validé et hors du dossier du plugin.

## 0.3.0

Parité d'apparence avec Navi PrestaShop : arrondis réglables (boutons,
image produit), visibilité par appareil par module (Navi > Cookies /
Accessibilité / Panier), logo Navi comme icône de menu admin, API JS
alignée (`window.navi`, `naviConfig`).

## 0.2.0

Module **Panier automatique** (sticky add-to-cart) : produits simples et
à variations, sélecteur de teinte accessible au clavier, compatible
WCBoost Variation Swatches, sélecteurs CSS personnalisables (Navi >
Panier) pour les thèmes non reconnus par la détection intégrée.

## 0.1.0

Version initiale : noyau (bouton flottant à 3 états), modules
Consentement cookies et Accessibilité, CI GitHub Actions.
