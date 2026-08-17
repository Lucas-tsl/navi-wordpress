# Changelog

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
