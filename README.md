# Navi (Saito Navi)

Plugin WordPress/WooCommerce : un seul bouton flottant (icône engrenage)
qui regroupe plusieurs modules d'engagement client — consentement
cookies, panier automatique, accessibilité, stories vidéo produit.
Architecture découplée : chaque module s'enregistre auprès d'un
registre et communique avec le bouton central via un événement générique
(`navi:action`), sans connaître les autres modules.

> En-tête `Plugin Name` : "Saito Navi" ("Navi" seul est trop court pour
> WordPress.org, minimum 5 lettres). Slug/text-domain/fonctions/classes
> restent `navi`.

Sœur du module [Navi pour PrestaShop](https://github.com/Lucas-tsl/navi-prestashop)
— même esprit, deux implémentations adaptées à chaque écosystème.

## Modules

Réglages regroupés sur une seule page (Navi > Navi), un onglet par module —
chaque onglet porte à la fois l'activation du module et ses réglages
propres (voir `settings_panel_callback`, `class-navi-module-registry.php`).

- **Consentement cookies** — bannière RGPD, Google Consent Mode V2.
- **Accessibilité** — langue (WPML ou repli GTranslate), taille du
  texte, contraste, curseur, soulignage des liens.
- **Panier automatique** (`sticky-cart`) — produits simples et à
  variations, sélecteur de teinte, sélecteurs CSS personnalisables
  pour les thèmes non reconnus.
- **Stories** — jusqu'à 4 bulles vidéo par produit (YouTube ou MP4),
  onglet dédié sur la fiche produit, panneau desktop + plein écran
  mobile. Affichage automatique après la galerie ou via le shortcode
  `[navi_stories]` (`id="123"` pour cibler un produit précis). Sous-onglets
  Bulles / Mockup imbriqués dans son propre onglet.

## Architecture

```
navi.php                # Bootstrap
includes/
  core/                  # Registre de modules, i18n, menu admin, frontend
  modules/<nom>/         # Un dossier par module (module.php, admin-settings.php...)
assets/
  css/, js/              # Un fichier par module + core.css/core.js (bouton)
```

Ajouter un module : l'enregistrer via `Navi_Module_Registry::register()`
dans `includes/modules/<nom>/module.php`, require dans `navi.php`, écouter
`navi:action` en JS et appeler `window.navi.showDetail('<nom>', applyFn)`
pour afficher son panneau dans `#navi-fab-detail`.

## Traduction

Toutes les chaînes passent par `__()`/`_e()` (text-domain `navi`).
`includes/core/i18n.php` intercepte le filtre `gettext` et traduit depuis
un dictionnaire PHP embarqué (détection WPML sinon `get_locale()`, ou
langue forcée manuellement dans Navi > Navi > Langue du plugin) — pas
de fichier `.mo` à compiler. Ajouter une langue : dupliquer
`navi_dictionary_en()` et l'ajouter à `$dictionaries`.

## Développement

```bash
composer install && npm install
vendor/bin/phpcs && npm run lint
```

### Docker local

```bash
docker compose up -d

docker exec navi_wp_cli wp core install --path=/var/www/html \
  --url=http://localhost:8082 --title="Navi Dev" \
  --admin_user=admin --admin_password=admin \
  --admin_email=vous@exemple.com --skip-email

docker exec navi_wp_cli wp plugin install woocommerce --activate --path=/var/www/html
docker exec navi_wp_cli wp theme install storefront --activate --path=/var/www/html
docker exec navi_wp_cli wp plugin install wcboost-variation-swatches --activate --path=/var/www/html

# Sinon WooCommerce reste en mode "Coming soon" et bloque les visiteurs
# non connectés (curl, Playwright sans login) sur les pages produit.
docker exec navi_wp_cli wp option update woocommerce_coming_soon no --path=/var/www/html
```

Déployer après chaque changement : `./scripts/deploy-local.sh` (assemble
via `.distignore`, copie dans le conteneur, `chown www-data`).

`navi.php` (`Version:`/`NAVI_VERSION`) et `package.json` doivent rester
synchronisés.

## Soumission WordPress.org

`readme.txt` et `.wordpress-org/assets/` (bannière, icônes, captures
d'écran) sont prêts, exclus du zip du plugin. Une fois le dépôt SVN
attribué :

```bash
svn co https://plugins.svn.wordpress.org/navi navi-svn
cp .wordpress-org/assets/*.png navi-svn/assets/
svn add navi-svn/assets/*.png && svn commit -m "Assets"
```

Le code va dans `navi-svn/trunk/` puis `navi-svn/tags/<version>/`. Avant
soumission : passer `readme.txt` au
[validateur officiel](https://wordpress.org/plugins/developers/readme-validator/).

## Licence

GPL-2.0-or-later — voir [`LICENSE`](LICENSE).
