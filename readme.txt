=== Navi ===
Contributors: TODO-pseudo-wordpress-org
Tags: woocommerce, cookie consent, accessibility, sticky add to cart, stories
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires Plugins: woocommerce

Un seul bouton flottant pour le consentement cookies, l'accessibilité, l'ajout au panier automatique et des stories vidéo produit.

== Description ==

Navi regroupe, derrière **un seul bouton flottant** (icône engrenage, coin
de l'écran), plusieurs modules d'engagement client pour WooCommerce :

* **Consentement cookies** — bannière RGPD, Google Consent Mode V2, modale
  de préférences, logo auto-détecté depuis l'identité du site si aucune
  URL n'est configurée.
* **Accessibilité** — sélecteur de langue (compatible WPML, repli
  GTranslate si WPML absent), taille du texte, contraste élevé, curseur
  agrandi, soulignage des liens.
* **Panier automatique** (sticky add-to-cart) — panneau qui suit le
  visiteur sur la fiche produit WooCommerce, produits simples et à
  variations, sélecteur de teinte accessible au clavier, compatible
  WCBoost Variation Swatches, réglage de sélecteurs CSS personnalisés pour
  les thèmes non reconnus par la chaîne de secours intégrée.
* **Stories** — jusqu'à 4 bulles vidéo par produit (YouTube ou MP4
  importé), onglet dédié sur la fiche produit WooCommerce, panneau
  desktop avec mockup de téléphone en CSS pur (aucune image externe),
  plein écran mobile façon stories.

Conçu dès le départ pour accueillir de nouveaux modules sans toucher au
noyau : chaque module se déclare auprès d'un registre et communique avec
le bouton central via un événement générique, aucun module ne connaît les
autres.

Tout est réglable depuis le Back Office (menu **Navi**) : couleurs,
arrondis, position du bouton, et pour chaque module son propre réglage
"Afficher sur ordinateur" / "Afficher sur mobile".

Sœur du module [Navi pour PrestaShop](https://github.com/Lucas-tsl/navi-prestashop)
— même nom, même esprit (un hub plutôt que des widgets indépendants), deux
implémentations distinctes adaptées à chaque écosystème.

= Services et contenus externes =

* **Google Consent Mode V2** : le module Consentement cookies pousse les
  choix du visiteur dans `window.dataLayer` (mécanisme standard Google
  Tag Manager/gtag.js). Navi lui-même n'envoie aucune requête à un
  serveur Google — c'est au site d'avoir déjà en place gtag.js/GTM pour
  que ce signal soit exploité.
* **YouTube (mode "no-cookie")** : le module Stories affiche les vidéos
  configurées via `youtube-nocookie.com` dans une iframe, uniquement sur
  les fiches produit où une story YouTube a été configurée par
  l'administrateur du site. Voir la
  [politique de confidentialité YouTube](https://policies.google.com/privacy).

== Installation ==

1. Téléverser le dossier `navi` dans `/wp-content/plugins/`, ou installer
   directement depuis **Extensions > Ajouter**.
2. Activer le plugin depuis le menu **Extensions**.
3. WooCommerce doit être installé et activé (requis pour les modules
   Panier automatique et Stories ; les modules Cookies et Accessibilité
   fonctionnent sans).
4. Configurer les modules depuis le nouveau menu **Navi** du Back Office.

== Frequently Asked Questions ==

= WooCommerce est-il obligatoire ? =

Le noyau et les modules Consentement cookies / Accessibilité fonctionnent
sans WooCommerce. Les modules Panier automatique et Stories sont liés à la
fiche produit WooCommerce et nécessitent donc son activation — une notice
s'affiche dans le Back Office si WooCommerce est absent ou inactif.

= Où sont stockées les vidéos MP4 importées pour les stories ? =

Dans le dossier uploads standard de WordPress
(`wp-content/uploads/navi-stories/`), jamais dans le dossier du plugin —
ce dernier peut être écrasé à chaque mise à jour, contrairement au dossier
uploads.

= Le sélecteur de langue du module Accessibilité fonctionne-t-il sans WPML ? =

Oui : s'il détecte le plugin GTranslate installé, il l'utilise en repli ;
sinon le sélecteur de langue ne s'affiche simplement pas (les autres
réglages d'accessibilité restent disponibles).

= Le panier automatique fonctionne-t-il avec mon thème ? =

Une chaîne de sélecteurs CSS de secours couvre les structures WooCommerce
classiques et les thèmes les plus courants. Si votre thème a une structure
inhabituelle, un réglage "sélecteur CSS personnalisé" par donnée (prix,
nom, image) est disponible dans Navi > Panier.

== Screenshots ==

1. Le bouton flottant Navi et son menu de modules.
2. Réglages du module Consentement cookies.
3. Panneau du panier automatique sur une fiche produit à variations.
4. Onglet Stories sur la fiche produit (Back Office).
5. Bulles vidéo Stories affichées sur la fiche produit.

== Changelog ==

= 0.4.0 =
* Nouveau module Stories : bulles vidéo produit (YouTube/MP4), panneau
  desktop, plein écran mobile, réglages d'aspect dans Navi > Stories.

= 0.3.0 =
* Parité d'apparence avec Navi PrestaShop : arrondis configurables,
  visibilité par appareil par module, logo du plugin comme icône du menu
  admin.

= 0.2.0 =
* Module Panier automatique (sticky add-to-cart) : produits simples et à
  variations, sélecteur de teinte, réglages de sélecteurs CSS
  personnalisés.

= 0.1.0 =
* Version initiale : noyau (bouton flottant à 3 états), modules
  Consentement cookies et Accessibilité.

== Upgrade Notice ==

= 0.4.0 =
Ajoute le module Stories (bulles vidéo produit) — aucune action requise
lors de la mise à jour.
