(function () {
    'use strict';

    // :focus-visible seul ne suffit pas partout : certains navigateurs
    // affichent quand même l'anneau de focus après un clic souris sur nos
    // boutons (croix de fermeture, etc.). On détecte nous-mêmes la dernière
    // modalité utilisée (souris vs clavier) pour le masquer de façon fiable
    // (voir la règle html.navi-mouse-user dans assets/css/core.css).
    var htmlEl = document.documentElement;
    document.addEventListener('mousedown', function () {
        htmlEl.classList.add('navi-mouse-user');
    }, true);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Tab') {
            htmlEl.classList.remove('navi-mouse-user');
        }
    }, true);

    var config = window.naviHubConfig || { items: [], isProduct: false };
    var fab = document.getElementById('navi-fab');
    var toggle = document.getElementById('navi-fab-toggle');
    var menu = document.getElementById('navi-fab-menu');
    var detail = document.getElementById('navi-fab-detail');

    if (!fab || !toggle || !menu || !detail) return;

    // #navi-fab est UN SEUL objet qui traverse 3 états (voir assets/css/core.css) :
    // 'closed' (engrenage), 'menu' (choix des icônes), 'detail' (contenu du
    // module choisi). Ce n'est jamais deux blocs distincts qui se suivent.
    var state = 'closed';
    var activeDetail = null;
    var scrollPercent = 0;

    // Les panneaux de cookie-consent et accessibilité sont rendus par PHP
    // ailleurs dans la page (wp_footer) : on les déplace une fois dans le
    // slot partagé #navi-fab-detail, pour qu'ils deviennent littéralement une
    // partie du même objet plutôt que des éléments fixed indépendants. Le
    // panneau sticky-cart, lui, est créé plus tard par assets/js/sticky-cart.js
    // directement à l'intérieur de #navi-fab-detail (rien à déplacer).
    ['navi-cookie-modal-overlay', 'navi-a11y-panel'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) detail.appendChild(el);
    });

    function setState(newState) {
        state = newState;
        fab.setAttribute('data-state', newState);
        toggle.setAttribute('aria-expanded', newState === 'closed' ? 'false' : 'true');
        if (newState === 'closed') {
            fab.removeAttribute('data-detail');
        }
    }

    function updateScrollPercent() {
        var doc = document.documentElement;
        var scrollTop = window.pageYOffset || doc.scrollTop;
        var height = (doc.scrollHeight - doc.clientHeight) || 1;
        scrollPercent = Math.min(100, Math.max(0, (scrollTop / height) * 100));
        fab.style.setProperty('--navi-scroll', String(scrollPercent));
    }

    function visibleItems() {
        return config.items.filter(function (item) {
            if (item.condition === 'is_product') return !!config.isProduct;
            if (item.condition === 'scroll') return scrollPercent >= (item.scrollThreshold || 100);
            return true;
        });
    }

    function renderMenu() {
        var items = visibleItems();
        menu.innerHTML = '';
        items.forEach(function (item, index) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'navi-fab-item';
            btn.style.setProperty('--navi-index', String(index));
            btn.setAttribute('role', 'menuitem');
            btn.setAttribute('title', item.label);
            btn.setAttribute('aria-label', item.label);
            // Icône dans un span dédié, séparé du fond du bouton. Un SVG
            // (currentColor) est préféré à l'emoji : le rendu des emojis
            // varie trop d'un système à l'autre pour rester lisible une fois
            // désaturé — voir includes/core/class-navi-module-registry.php.
            var icon = document.createElement('span');
            icon.setAttribute('aria-hidden', 'true');
            if (item.iconSvg) {
                icon.className = 'navi-fab-item-icon navi-fab-item-icon--svg';
                icon.innerHTML = item.iconSvg;
            } else {
                icon.className = 'navi-fab-item-icon navi-fab-item-icon--emoji';
                icon.textContent = item.icon;
            }

            // Anneau qui porte le fond/la bordure (icône inchangée à
            // l'intérieur) + légende visible sous l'icône : le bouton
            // lui-même n'est plus qu'un simple conteneur en colonne.
            var ring = document.createElement('span');
            ring.className = 'navi-fab-item-ring';
            ring.appendChild(icon);
            btn.appendChild(ring);

            var label = document.createElement('span');
            label.className = 'navi-fab-item-label';
            // aria-hidden : le nom accessible du bouton est déjà porté par
            // son propre aria-label ci-dessus, pas la peine de le répéter.
            label.setAttribute('aria-hidden', 'true');
            label.textContent = item.shortLabel || item.label;
            btn.appendChild(label);
            btn.addEventListener('click', function () {
                if (item.action === 'scroll-top') {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    forceClose();
                    return;
                }
                // Le module concerné écoute cet événement pour afficher son
                // propre contenu dans le slot détail (naviHub.showDetail), sans
                // que le noyau ait besoin de connaître ce contenu.
                document.dispatchEvent(new CustomEvent('navi:action', { detail: item }));
            });
            menu.appendChild(btn);
        });

        // Croix de sortie, à droite des bulles : referme entièrement et
        // retrouve l'engrenage de départ (état 1), sans devoir cliquer en
        // dehors ou faire Échap.
        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'navi-fab-menu-close';
        closeBtn.style.setProperty('--navi-index', String(items.length));
        closeBtn.setAttribute('aria-label', config.closeLabel || 'Fermer');
        closeBtn.textContent = '✕';
        closeBtn.addEventListener('click', function () {
            forceClose();
        });
        menu.appendChild(closeBtn);
    }

    function openMenu() {
        renderMenu();
        setState('menu');
    }

    // Fermeture complète (état 1) : utilisée pour le clic en dehors, Échap,
    // et un nouveau clic sur l'engrenage pendant que menu/détail est ouvert.
    function forceClose() {
        if (state === 'closed') return;
        var closingId = activeDetail;
        activeDetail = null;
        setState('closed');
        if (closingId) {
            // Le module qui était affiché doit remettre à jour son propre
            // indicateur interne (ex. classe .visible), même s'il n'est pas
            // celui qui a demandé cette fermeture (clic extérieur, Échap...).
            document.dispatchEvent(new CustomEvent('navi:closed', { detail: { id: closingId } }));
        }
    }

    toggle.addEventListener('click', function () {
        if (state === 'closed') {
            openMenu();
        } else {
            forceClose();
        }
    });

    document.addEventListener('click', function (event) {
        if (state === 'closed' || fab.contains(event.target)) return;

        // Exception pour le panneau panier : contrairement aux modales
        // cookies/accessibilité (qu'un clic en dehors doit bien fermer,
        // comportement standard), le panier suit son propre cycle de vie
        // piloté par le scroll et sa croix de fermeture explicite
        // (assets/js/sticky-cart.js, handleStickyVisibility). Sans cette
        // exception, cliquer n'importe où sur la page en consultant la fiche
        // produit — une vignette de la galerie photo, par exemple — le
        // refermait alors que rien ne l'exigeait.
        if (state === 'detail' && activeDetail === 'sticky-cart') return;

        forceClose();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && state !== 'closed') forceClose();
    });

    var ticking = false;
    window.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(function () {
            updateScrollPercent();
            if (state === 'menu') renderMenu();
            ticking = false;
        });
    }, { passive: true });

    updateScrollPercent();

    // API exposée aux modules (cookie, sticky cart, accessibilité...) pour
    // afficher/masquer LEUR contenu dans le slot #navi-fab-detail. Le noyau ne
    // connaît pas ce contenu : chaque module bascule sa propre classe
    // d'affichage via applyFn, le noyau se charge uniquement de faire
    // grandir/rétrécir l'objet partagé et de choisir quel contenu montrer.
    // Déplace le focus clavier dans le contenu qui vient de s'afficher : le
    // premier enfant de #navi-fab-detail dont le CSS le rend visible (voir les
    // sélecteurs par data-detail dans assets/css/core.css) porte lui-même un
    // tabindex="-1" (cookie-consent, sticky-cart, accessibility), ce fichier
    // n'a donc pas besoin de connaître la structure interne de chaque module.
    function focusActiveDetail() {
        var children = detail.children;
        for (var i = 0; i < children.length; i++) {
            var child = children[i];
            if (window.getComputedStyle(child).display !== 'none') {
                if (typeof child.focus === 'function') child.focus();
                return;
            }
        }
    }

    window.naviHub = {
        // Affiche le contenu du module `id` (état 3). À utiliser aussi bien
        // pour une ouverture manuelle (icône cliquée) qu'automatique (ex. le
        // panier qui apparaît au scroll sur une fiche produit).
        showDetail: function (id, applyFn) {
            activeDetail = id;
            fab.setAttribute('data-detail', id);
            setState('detail');
            if (typeof applyFn === 'function') applyFn();
            focusActiveDetail();
        },
        // Fermeture AUTOMATIQUE (ex. la barre panier qui se masque au scroll,
        // sans action explicite de l'utilisateur) : retour direct à l'état
        // fermé, pas d'intérêt à réafficher le choix des icônes.
        hideDetail: function (id, applyFn) {
            if (activeDetail !== id) return;
            activeDetail = null;
            setState('closed');
            if (typeof applyFn === 'function') applyFn();
        },
        // Croix de fermeture À L'INTÉRIEUR du contenu détaillé (état 3) :
        // revient au choix des icônes (état 2).
        backToMenu: function (id, applyFn) {
            if (activeDetail !== id) return;
            activeDetail = null;
            renderMenu();
            setState('menu');
            if (typeof applyFn === 'function') applyFn();
        },
        // Fermeture complète depuis L'EXTÉRIEUR du hub (ex. le mini-panier
        // WooCommerce qui s'ouvre, voir watchWooCommerceMiniCart plus bas) :
        // simple alias de la fermeture interne forceClose, exposé ici pour
        // qu'un autre script du plugin puisse fermer le hub sans dupliquer
        // sa logique (activeDetail, évènement 'navi:closed'...).
        forceClose: forceClose
    };

    // Le tiroir du mini-panier WooCommerce a un z-index bien plus bas que
    // celui du FAB (nécessairement très élevé pour rester au-dessus du
    // contenu de n'importe quel thème) : sans ce correctif, le panneau du
    // hub encore ouvert (typiquement le panier, juste après un ajout qui
    // déclenche l'ouverture automatique du mini-panier) recouvre le
    // mini-panier natif au lieu de lui laisser la priorité. On referme le
    // hub dès que le tiroir s'ouvre plutôt que de renoncer au z-index élevé
    // du FAB, qui reste nécessaire pour le reste de la navigation.
    var miniCartWatched = false;
    var miniCartCloseTimer = null;
    function watchWooCommerceMiniCart() {
        if (miniCartWatched || typeof MutationObserver === 'undefined') return;
        var drawer = document.querySelector('.wc-block-components-drawer');
        if (!drawer) return;
        miniCartWatched = true;
        var observer = new MutationObserver(function () {
            if (drawer.getAttribute('aria-hidden') === 'false' && state !== 'closed') {
                // Le panier sticky affiche sa confirmation "✓ Ajouté" pendant 900ms
                // (assets/js/sticky-cart.js, showAddedConfirmation) juste après le
                // même évènement WooCommerce qui ouvre ce tiroir : sans délai, le
                // hub se refermait ~1ms après l'apparition de la confirmation,
                // la rendant invisible. On laisse donc ce cas précis le temps de
                // s'afficher avant de céder la priorité au tiroir ; les autres
                // panneaux (cookies, accessibilité) n'ont pas cette contrainte et
                // se ferment donc toujours immédiatement.
                if (activeDetail === 'sticky-cart') {
                    if (miniCartCloseTimer) clearTimeout(miniCartCloseTimer);
                    miniCartCloseTimer = setTimeout(function () {
                        miniCartCloseTimer = null;
                        forceClose();
                    }, 900);
                } else {
                    forceClose();
                }
            }
        });
        observer.observe(drawer, { attributes: true, attributeFilter: ['aria-hidden'] });
    }
    // Tenté tout de suite (le bloc mini-panier est en général déjà rendu
    // côté serveur à ce stade, seule son interactivité s'hydrate en JS), puis
    // une seconde fois si besoin une fois toute la page chargée — idempotent
    // (miniCartWatched), pas de double observation possible.
    watchWooCommerceMiniCart();
    window.addEventListener('load', watchWooCommerceMiniCart);
})();
