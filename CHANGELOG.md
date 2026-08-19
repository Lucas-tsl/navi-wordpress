# Changelog

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
- BO redessiné (cartes, interrupteurs, onglets en pastilles) plutôt que
  l'apparence WordPress par défaut.
- Langue du plugin réglable manuellement (Navi > Navi), indépendamment de
  la détection automatique (WPML ou langue du site).

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
