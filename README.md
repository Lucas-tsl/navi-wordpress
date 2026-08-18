# Navi (Saito Navi)

> En-tête `Plugin Name` du plugin : "Saito Navi" — "Navi" seul est trop
> court pour WordPress.org (minimum 5 lettres latines). Slug/text-domain,
> noms de fonctions/classes et dépôt GitHub restent `navi`.

Plugin WordPress/WooCommerce qui regroupe, derrière **un seul bouton
flottant** (icône engrenage, coin de l'écran), plusieurs modules
d'engagement client : consentement cookies (Google Consent Mode V2), ajout
au panier automatique sur fiche produit, accessibilité (langue, taille du
texte, contraste, curseur, soulignage des liens). Conçu dès le départ pour
accueillir de nouveaux modules sans toucher au noyau.

Sœur du module [Navi pour PrestaShop](https://github.com/Lucas-tsl/navi-prestashop)
— même nom, même esprit (un hub plutôt que des widgets indépendants), deux
implémentations distinctes adaptées à chaque écosystème.

Née de la fusion de trois déploiements site-spécifiques (`hub-lsg`,
`hub-pe`, `hub-jozz`, privés) : ce dépôt en reprend l'architecture commune
et généralise ce qui était propre à un site (couleurs de marque, sélecteurs
CSS liés à un thème précis) pour une distribution publique, à terme
soumise au [répertoire officiel WordPress.org](https://wordpress.org/plugins/).

## État actuel

- Noyau (bouton flottant à 3 états, registre de modules) : fonctionnel.
- Module **Consentement cookies** : fonctionnel (bannière RGPD, Google
  Consent Mode V2, modale de préférences, logo auto-détecté depuis
  l'identité du site).
- Module **Accessibilité** : fonctionnel (langue via WPML ou repli
  GTranslate, taille du texte, contraste élevé, curseur agrandi,
  soulignage des liens).
- Module **Panier automatique** (`sticky-cart`) : fonctionnel (produits
  simples et à variations, sélecteur de teinte accessible au clavier,
  lecture de couleur via WCBoost Variation Swatches si présent, réglage de
  sélecteurs CSS personnalisés pour les thèmes non reconnus par la chaîne
  de secours intégrée — voir Navi > Panier dans le Back Office).
- Module **Stories** : fonctionnel (jusqu'à 4 bulles vidéo par produit,
  YouTube ou MP4 importé, onglet dédié sur la fiche produit WooCommerce,
  panneau desktop avec mockup de téléphone en CSS pur, plein écran mobile
  avec défilement type stories — voir Navi > Stories dans le Back Office).
  Affichage par défaut après la galerie produit, ou via le shortcode
  `[navi_stories]` (désactiver "Afficher automatiquement" dans Navi >
  Stories pour ne positionner les bulles que via le shortcode) —
  `[navi_stories]` seul utilise le produit de la page courante,
  `[navi_stories id="123"]` cible un produit précis. Réglages répartis en
  deux onglets (Navi > Stories) : "Bulles" (bordure — unie ou dégradée —
  et taille de la bulle, avec aperçu en direct) et "Mockup" (couleurs,
  dimensions du panneau desktop, et "Zoom de la vidéo" — YouTube affiche
  toujours son propre bandeau titre/chaîne par-dessus la vidéo, réglable
  de 100 % (aucun zoom) à 150 % pour le pousser hors du cadre visible).
- Suite de tests Playwright automatisée : pas encore mise en place (prochain
  chantier — vérification faite manuellement jusqu'ici contre un catalogue
  de démonstration, voir Développement ci-dessous).
- Préparation à la soumission WordPress.org : `readme.txt`
  (`Contributors: lucastsl`), bannière/icône et les 8 captures d'écran
  (`.wordpress-org/assets/`) prêts. Reste à faire avant soumission
  effective : passer `readme.txt` au validateur officiel une fois le
  dépôt SVN attribué (voir Soumission WordPress.org ci-dessous).

## Architecture

```
navi.php                          # Bootstrap : constantes, charge le noyau puis les modules
uninstall.php
includes/
  core/
    class-navi-module-registry.php  # Registre : chaque module s'y déclare (icône, condition, option d'activation…)
    helpers.php                     # Petits helpers partagés (enqueue, permissions, sanitisation, couleurs)
    i18n.php                        # Traduction auto-suffisante (voir plus bas)
    admin-menu.php                  # Menu top-level "Navi" : active/désactive les modules, couleurs, position
    frontend.php                    # Enqueue des assets du noyau + construit la config JS du bouton flottant
  modules/
    cookie-consent/    # Consentement cookies + Google Consent Mode V2
    accessibility/      # Panneau langue, contraste, curseur agrandi, soulignage des liens
    sticky-cart/         # Panneau produit WooCommerce (image, variation, ajout au panier)
assets/
  css/core.css, js/core.js        # Bouton engrenage + menu
  css/*, js/*                     # Un fichier par module actif
languages/                         # .pot/.po pour un traducteur qui préfère l'outillage gettext standard
```

### Comment les modules communiquent avec le bouton central

Le noyau ne connaît **aucun détail** des modules. Il lit juste
`Navi_Module_Registry` pour savoir quelles icônes afficher, et quand l'une
est cliquée il envoie un événement générique :

```js
document.dispatchEvent(new CustomEvent('navi:action', { detail: item }));
```

Chaque module écoute cet événement et réagit s'il reconnaît l'action (voir
`assets/js/cookie-consent.js`, `assets/js/accessibility.js`). Ce
découplage est ce qui permet d'ajouter un module sans modifier le noyau.

Le noyau expose aussi une petite API (`assets/js/core.js`) pour afficher le
contenu d'un module dans le slot partagé `#navi-fab-detail`, seul endroit où
`#navi-fab` grandit jusqu'à l'état 3 :

```js
window.navi.showDetail('mon-module', applyFn);   // affiche le module, fait grandir #navi-fab (état 3)
window.navi.backToMenu('mon-module', applyFn);   // fermeture MANUELLE (croix) : revient au choix des icônes (état 2)
window.navi.hideDetail('mon-module', applyFn);   // fermeture AUTOMATIQUE (ex. scroll) : referme entièrement (état 1)
// applyFn bascule la classe d'affichage propre au module (ex. .visible) ; à écouter aussi : 'navi:closed'
// (le hub s'est refermé alors que ce module était actif, ex. clic en dehors) pour remettre à jour cette classe.
```

### Ajouter un nouveau module

1. Créer `includes/modules/mon-module/module.php`, y appeler
   `Navi_Module_Registry::register('mon-module', [...])` avec une `icon`,
   une `fab_action`, et `'available' => true`.
2. Ajouter le require dans `navi.php`.
3. Créer les assets `assets/css/mon-module.css` / `assets/js/mon-module.js`,
   enqueués depuis le module.
4. Le contenu du panneau (rendu en PHP dans `wp_footer`) est un simple bloc
   qui remplit son conteneur (`width: 100%; height: 100%;`, voir
   `assets/css/accessibility.css` pour le patron) : position, taille et
   habillage viennent de `#navi-fab` lui-même, pas du module.
   `assets/js/core.js` déplace automatiquement tout élément rendu en PHP
   vers `#navi-fab-detail` au chargement (voir la liste d'ids dans ce
   fichier) ; un panneau créé dynamiquement en JS doit s'y injecter
   directement.
5. Dans le JS du module, écouter `document.addEventListener('navi:action', ...)`
   pour afficher le panneau (`window.navi.showDetail('mon-module', applyFn)`),
   avec un bouton de fermeture qui appelle
   `window.navi.backToMenu('mon-module', applyFn)`.

## Apparence

Depuis le menu **Navi** (Back Office WordPress) : couleur principale et
couleur secondaire, arrondi des boutons (bannière cookies, panier sticky)
et de l'image produit (miniature du panier sticky) — variables CSS
`--navi-color-ink`/`--navi-color-ink-soft`/`--navi-radius-button`/
`--navi-radius-image` (voir `assets/css/core.css`). Une surcharge n'est
injectée en `<style>` inline que pour les propriétés qui s'écartent des
valeurs par défaut du plugin (voir `includes/core/frontend.php`) — sinon
les valeurs par défaut (`:root`) suffisent.

## Visibilité par appareil

Chaque module ayant un affichage propre sur le site (cookies,
accessibilité, panier) a son propre réglage "Afficher sur ordinateur" /
"Afficher sur mobile" dans sa page de réglages (Navi > Cookies/
Accessibilité/Panier). Mécanisme : `visibility_selector` déclaré par le
module lors de son enregistrement (`Navi_Module_Registry::register()`),
options `navi_show_desktop_<id>`/`navi_show_mobile_<id>`, règles
`@media (max-width:480px)`/`(min-width:481px)` injectées par
`includes/core/frontend.php` (même seuil de 480px que le reste du hub).

## Traduction du plugin (auto-suffisante, sans plugin supplémentaire)

Toutes les chaînes du plugin passent par `__()`/`_e()`/`esc_html__()`/`esc_html_e()`/`esc_attr__()`/`esc_attr_e()`
avec le text-domain `navi`. Plutôt que de dépendre d'un fichier `.mo`
compilé (WordPress Coding Standards habituelles, mais qui demande un outil
externe — WP-CLI, Poedit, ou un plugin comme Loco Translate),
`includes/core/i18n.php` intercepte directement ces chaînes via le filtre
`gettext` de WordPress et les traduit depuis un dictionnaire PHP embarqué
dans le plugin :

- La langue active est détectée via l'API officielle de WPML
  (`wpml_current_language`), qui gère elle-même l'URL quel que soit le
  format configuré (sous-dossier, sous-domaine, paramètre) — le plugin n'a
  pas besoin de parser l'URL lui-même.
- Sans WPML, la détection retombe sur la locale WordPress (`get_locale()`),
  et si aucun dictionnaire ne correspond, les chaînes françaises d'origine
  s'affichent normalement : rien ne casse sur un site sans WPML.
- Aucune installation supplémentaire, aucune compilation : le dictionnaire
  anglais (`navi_dictionary_en()`) est actif dès l'activation du plugin.

`languages/navi.pot` (à générer via `wp i18n make-pot .` une fois WP-CLI
i18n disponible) reste prévu en complément pour un traducteur qui préfère
l'outillage gettext standard, mais n'est pas le mécanisme utilisé en
pratique par le plugin lui-même.

**Ajouter une langue** : dupliquer le motif de `navi_dictionary_en()` dans
`includes/core/i18n.php` (ex. `navi_dictionary_de()`), l'ajouter au tableau
`$dictionaries` dans `navi_translate_strings()` avec la clé de langue WPML
correspondante (ex. `'de'`), et traduire les valeurs.

## Développement

```bash
composer install   # PHP_CodeSniffer + WordPress Coding Standards
npm install         # ESLint + Stylelint

vendor/bin/phpcs    # Lint PHP
npm run lint        # Lint JS + CSS
```

### Environnement local (Docker)

```bash
docker compose up -d
```

Démarre trois conteneurs : `navi_wp_db` (MySQL), `navi_wp_web` (WordPress +
Apache, http://localhost:8082), `navi_wp_cli` (WP-CLI, resté actif pour des
appels `docker exec navi_wp_cli wp ...` ponctuels). Amorçage initial (une
seule fois) :

```bash
docker exec navi_wp_cli wp core install --path=/var/www/html \
  --url=http://localhost:8082 --title="Navi Dev" \
  --admin_user=admin --admin_password=admin \
  --admin_email=vous@exemple.com --skip-email

docker exec navi_wp_cli wp plugin install woocommerce --activate --path=/var/www/html
docker exec navi_wp_cli wp theme install storefront --activate --path=/var/www/html
docker exec navi_wp_cli wp plugin install wcboost-variation-swatches --activate --path=/var/www/html

# Important pour tester en visiteur non connecté (curl, Playwright sans
# login) : l'installation WooCommerce active "Coming soon" par défaut sur
# les pages boutique, qui contourne les templates produit (et donc les
# hooks utilisés par Navi) pour les visiteurs déconnectés — invisible en
# étant soi-même connecté en admin, d'où un faux négatif si non désactivé.
docker exec navi_wp_cli wp option update woocommerce_coming_soon no --path=/var/www/html
```

**Catalogue de démonstration** (utile pour tester le module Panier —
produits simples et à variations) :

```bash
# Simple, en stock
docker exec navi_wp_cli wp wc product create --path=/var/www/html --user=admin \
  --name="T-shirt Uni" --type=simple --regular_price=25.00 \
  --manage_stock=true --stock_quantity=50 --backorders=no --status=publish

# Variable (attribut Couleur), une variation créée par couleur
PARENT=$(docker exec navi_wp_cli wp wc product create --path=/var/www/html --user=admin --porcelain \
  --name="Mug Céramique" --type=variable --status=publish \
  --attributes='[{"name":"Couleur","options":["Rouge","Bleu"],"visible":true,"variation":true}]')
docker exec navi_wp_cli wp wc product_variation create $PARENT --path=/var/www/html --user=admin \
  --regular_price=12.00 --manage_stock=true --stock_quantity=15 --backorders=no \
  --attributes='[{"name":"Couleur","option":"Rouge"}]'
```

**Déployer le plugin** (à chaque changement) :

```bash
./scripts/deploy-local.sh
```

Assemble le dossier du plugin via `.distignore` (même mécanisme que le job
`build` de la CI — on teste ce qui serait réellement distribué), le copie
dans le conteneur, corrige les permissions (`chown www-data`), vérifie que
le site répond. Variables d'environnement :
`NAVI_DEPLOY_CONTAINER` (défaut `navi_wp_web`), `NAVI_DEPLOY_BASE_URL`
(défaut `http://localhost:8082`).

### Synchronisation de version

`navi.php` (en-tête `Version:` et constante `NAVI_VERSION`) et
`package.json` doivent rester cohérents.

### CI

`.github/workflows/ci.yml` : lint PHP (`php -l` + WordPress Coding
Standards, matrice PHP 7.4/8.1/8.3), lint JS/CSS (ESLint/Stylelint), build
du zip (artefact + attaché à une Release GitHub).

## Soumission WordPress.org

`readme.txt` (racine du dépôt, format strict WordPress.org) et
`.wordpress-org/assets/` (bannière 772×250, icônes 128×128/256×256,
captures d'écran `screenshot-1.png` à `screenshot-8.png`, numérotées dans
l'ordre de la section `== Screenshots ==` de `readme.txt`) sont prêts pour
la soumission. **Important** : ce dossier `.wordpress-org/` n'est
distribué ni dans le zip du plugin (exclu via `.distignore`) ni sur
WordPress.org de la même façon que les fichiers du plugin — une fois le
dépôt SVN attribué par l'équipe WordPress.org (après acceptation), son
contenu doit être copié manuellement dans le dossier `assets/` du **SVN**
(distinct du dossier `assets/` du plugin lui-même, qui contient le CSS/JS
réel) :

```bash
svn co https://plugins.svn.wordpress.org/navi navi-svn
cp .wordpress-org/assets/*.png navi-svn/assets/
svn add navi-svn/assets/*.png
svn commit -m "Ajout bannière, icônes et captures d'écran"
```

Le code du plugin lui-même va dans `navi-svn/trunk/` (assemblé via le même
mécanisme `.distignore` que `scripts/deploy-local.sh` et le job `build` de
la CI), puis `navi-svn/tags/0.4.0/` etc. à chaque version publiée.

Avant la soumission effective (formulaire sur
[wordpress.org/plugins/developers/add/](https://wordpress.org/plugins/developers/add/)) :
passer `readme.txt` au
[validateur officiel](https://wordpress.org/plugins/developers/readme-validator/).

## Licence

GPL-2.0-or-later — voir [`LICENSE`](LICENSE).
