# Changelog

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
