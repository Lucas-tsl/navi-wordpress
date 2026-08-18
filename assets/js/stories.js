(function () {
    'use strict';

    // Même précaution que assets/js/core.js : l'ordre d'enqueue WordPress
    // peut placer ce script avant le HTML des bulles de stories dans la
    // page finale.
    document.addEventListener('DOMContentLoaded', function () {

    var config = window.naviStoriesI18n || { closeLabel: 'Fermer', prevLabel: 'Story précédente', nextLabel: 'Story suivante', replayLabel: 'Relancer la vidéo' };

    // Bulles rendues nativement par Navi (voir includes/modules/stories/public-display.php)
    // — ce script reste inerte tant qu'aucune bulle n'est présente sur la
    // page (produit sans story, ou module stories désactivé).
    var hasBubbles = !!document.querySelector('.navi-story-bubble[data-video-id]');

    if (!hasBubbles) return;

    // En dessous de ce seuil (même breakpoint que le reste du hub, voir
    // assets/css/core.css), le mockup de téléphone dans le petit panneau du
    // hub n'a plus de sens — l'écran EST déjà un téléphone : ouverture en
    // plein écran façon stories (défilement vertical, sans mockup), le
    // panneau ancré au bouton flottant restant réservé au
    // desktop/laptop/tablette.
    function isMobile() {
        return window.matchMedia('(max-width: 480px)').matches;
    }

    // Ajoute aux paramètres youtube-nocookie standard ceux qui masquent le
    // plus possible l'habillage natif YouTube. `controls=0` ne suffit pas
    // seul sur l'UI Shorts (titre/chaîne en haut, filigrane "Shorts" en bas
    // restent visibles, YouTube ne permet pas de les retirer via l'URL
    // d'embed ni depuis aucun CSS/JS — iframe cross-origin, Same-Origin
    // Policy du navigateur) : ils disparaissent d'eux-mêmes après quelques
    // secondes. `loop`/`playlist` volontairement absents : fait apparaître
    // des flèches de navigation, pire que l'écran de fin qu'ils évitaient.
    function buildVideoUrl(videoId) {
        var origin = encodeURIComponent(window.location.origin || '');

        return 'https://www.youtube-nocookie.com/embed/'
            + encodeURIComponent(videoId)
            + '?autoplay=1&rel=0&modestbranding=1&playsinline=1&enablejsapi=1'
            + '&controls=0&disablekb=1&fs=0&iv_load_policy=3'
            + '&origin=' + origin;
    }

    // ============================================================
    // Masque anti-chrome YouTube. Le bandeau titre/chaîne et le filigrane
    // "Shorts" (voir buildVideoUrl() ci-dessus) ne sont PAS affichés en
    // continu : YouTube les montre au chargement puis les efface tout
    // seul une fois la lecture active, avant de les remontrer une fois la
    // vidéo terminée (écran de fin, bouton de reprise natif). On masque
    // donc précisément ces deux fenêtres (chargement → PLAYING, puis
    // ENDED) avec un cache à notre design plutôt que de recadrer la
    // vidéo en permanence pendant toute sa durée (essayé, abandonné :
    // voir assets/css/stories.css) — repose sur l'API IFrame officielle
    // de YouTube, disponible grâce à `enablejsapi=1` déjà dans l'URL.
    // ============================================================
    var youTubeApiPromise = null;
    function loadYouTubeApi() {
        if (youTubeApiPromise) return youTubeApiPromise;
        youTubeApiPromise = new Promise(function (resolve) {
            if (window.YT && window.YT.Player) { resolve(window.YT); return; }
            var previousCallback = window.onYouTubeIframeAPIReady;
            window.onYouTubeIframeAPIReady = function () {
                if (typeof previousCallback === 'function') previousCallback();
                resolve(window.YT);
            };
            if (!document.querySelector('script[src*="youtube.com/iframe_api"]')) {
                var tag = document.createElement('script');
                tag.src = 'https://www.youtube.com/iframe_api';
                document.head.appendChild(tag);
            }
        });
        return youTubeApiPromise;
    }

    // Filet de sécurité à 6s : si l'API ne se charge pas (réseau,
    // bloqueur) ou ne renvoie jamais PLAYING pour une raison quelconque,
    // le masque ne doit pas rester affiché indéfiniment — mieux vaut
    // revenir au comportement d'origine (bandeau parfois visible) que de
    // cacher la vidéo en permanence derrière notre propre cache.
    function attachVideoMask(iframeEl, maskEl, videoId) {
        if (!maskEl) return;
        maskEl.classList.add('is-visible');
        maskEl.classList.remove('is-ended');

        // Jeton de génération : le panneau desktop réutilise le MÊME
        // élément iframe/masque à chaque bulle cliquée (buildPanel() ne le
        // recrée pas), donc un YT.Player ou un setTimeout hérité d'un
        // appel précédent pourrait sinon modifier l'état bien après coup
        // (ex. l'utilisateur ferme puis rouvre une autre story avant que
        // l'ancien délai de révélation n'ait eu le temps de se déclencher).
        // Chaque appel invalide les callbacks asynchrones des appels
        // précédents en comparant ce compteur avant d'agir.
        var generation = ( maskEl._naviGen || 0 ) + 1;
        maskEl._naviGen = generation;

        var fallbackTimer = window.setTimeout(function () {
            if (maskEl._naviGen !== generation) return;
            maskEl.classList.remove('is-visible');
        }, 9000);
        // Le passage à l'état PLAYING ne signifie pas que le bandeau a
        // déjà fini de s'effacer (constaté à l'usage : le chrome YouTube
        // reste visible plusieurs secondes après le premier PLAYING) — un
        // court délai supplémentaire avant de révéler la vidéo laisse le
        // temps à ce fondu natif de se terminer. Annulé si l'état change
        // de nouveau avant (ex. vidéo très courte déjà terminée) pour ne
        // jamais révéler après coup une vidéo qui a déjà atteint ENDED.
        var revealTimer = null;

        loadYouTubeApi().then(function (YT) {
            if (!iframeEl.isConnected || maskEl._naviGen !== generation) return;
            try {
                new YT.Player(iframeEl, {
                    events: {
                        onStateChange: function (event) {
                            if (maskEl._naviGen !== generation) return;
                            if (event.data === YT.PlayerState.PLAYING) {
                                window.clearTimeout(revealTimer);
                                revealTimer = window.setTimeout(function () {
                                    if (maskEl._naviGen !== generation) return;
                                    window.clearTimeout(fallbackTimer);
                                    maskEl.classList.remove('is-visible', 'is-ended');
                                }, 5000);
                            } else if (event.data === YT.PlayerState.ENDED) {
                                window.clearTimeout(revealTimer);
                                window.clearTimeout(fallbackTimer);
                                maskEl.classList.add('is-visible', 'is-ended');
                            }
                        }
                    }
                });
            } catch {
                // Le filet de sécurité ci-dessus prend le relais.
            }
        });

        var replayBtn = maskEl.querySelector('.navi-story-video-mask-replay');
        if (replayBtn) {
            replayBtn.onclick = function () {
                iframeEl.src = buildVideoUrl(videoId);
                attachVideoMask(iframeEl, maskEl, videoId);
            };
        }
    }

    var videoMaskMarkup = '<div class="navi-story-video-mask" aria-hidden="true">'
        + '<button type="button" class="navi-story-video-mask-replay" aria-label="' + config.replayLabel + '" tabindex="-1">⟳</button>'
        + '</div>';

    // ============================================================
    // Mode desktop/laptop/tablette — panneau ancré au bouton flottant,
    // mockup de téléphone en CSS pur (voir assets/css/stories.css). Construit
    // une seule fois, réutilisé à chaque bulle cliquée (même patron que le
    // panier sticky, assets/js/sticky-cart.js).
    // ============================================================
    var panelDetail = document.getElementById('navi-fab-detail');
    var panel = null;
    var panelIframe = null;
    var panelMask = null;

    function buildPanel() {
        if (panel || !panelDetail) return;

        panel = document.createElement('div');
        panel.id = 'navi-story-panel';
        panel.className = 'navi-story-panel';
        panel.tabIndex = -1;
        panel.innerHTML =
            '<button type="button" class="navi-story-close" aria-label="' + config.closeLabel + '">✕</button>' +
            '<div class="navi-story-scroll">' +
            '<div class="navi-story-phone">' +
            '<div class="navi-story-phone-screen">' +
            '<iframe id="navi-story-iframe" src="" title="" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture"></iframe>' +
            '<div class="navi-story-guard-top" aria-hidden="true"></div>' +
            '<div class="navi-story-guard-bottom" aria-hidden="true"></div>' +
            videoMaskMarkup +
            '</div>' +
            '</div>' +
            '</div>';
        panelDetail.appendChild(panel);

        panelIframe = panel.querySelector('#navi-story-iframe');
        panelMask = panel.querySelector('.navi-story-video-mask');

        var closeBtn = panel.querySelector('.navi-story-close');
        closeBtn.addEventListener('click', function () {
            panelIframe.src = '';
            if (window.navi) {
                window.navi.backToMenu('stories', function () {});
            }
        });

        document.addEventListener('navi:closed', function (event) {
            if (event.detail && event.detail.id === 'stories') {
                panelIframe.src = '';
            }
        });
    }

    function openPanelStory(videoId, label) {
        buildPanel();
        if (!panel) return;

        panelIframe.src = buildVideoUrl(videoId);
        panelIframe.title = label || '';
        attachVideoMask(panelIframe, panelMask, videoId);

        if (window.navi) {
            window.navi.showDetail('stories', function () {});
        }
    }

    // ============================================================
    // Disparition automatique en approchant du pied de page. Basé sur la
    // hauteur du document plutôt que sur un sélecteur `footer` : certains
    // thèmes ont plusieurs éléments `<footer>` sur une fiche produit (même
    // seuil de 200px que le panier sticky, assets/js/sticky-cart.js, pour
    // rester cohérent). `hideDetail` (pas `backToMenu`) : une disparition
    // automatique ne doit pas laisser le menu du bouton flottant ouvert
    // sans que l'utilisateur ne l'ait demandé.
    // ============================================================
    function checkFooterProximity() {
        if (!panel) return;
        var fab = document.getElementById('navi-fab');
        if (!fab || fab.getAttribute('data-detail') !== 'stories' || fab.getAttribute('data-state') !== 'detail') return;

        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var windowBottom = scrollTop + window.innerHeight;
        var docHeight = document.documentElement.scrollHeight;

        if (docHeight - windowBottom <= 200) {
            if (panelIframe) panelIframe.src = '';
            if (window.navi) window.navi.hideDetail('stories', function () {});
        }
    }

    var footerCheckTicking = false;
    window.addEventListener('scroll', function () {
        if (footerCheckTicking) return;
        footerCheckTicking = true;
        window.requestAnimationFrame(function () {
            checkFooterProximity();
            footerCheckTicking = false;
        });
    }, { passive: true });

    // ============================================================
    // Mode mobile — plein écran indépendant du bouton flottant (pas le
    // même objet à 3 états que le reste du hub) : une vraie prise de
    // contrôle de l'écran, comme les stories des réseaux sociaux).
    // Défilement vertical en scroll-snap entre toutes les stories DU MÊME
    // PRODUIT — un seul chargé/actif à la fois (IntersectionObserver) pour
    // ne jamais lire deux audios en même temps.
    // ============================================================
    var fullscreenEl = null;
    var fullscreenTrack = null;
    var fullscreenObserver = null;
    var fullscreenPrevBtn = null;
    var fullscreenCloseBtn = null;
    var fullscreenNextBtn = null;
    var fullscreenSlideEls = [];
    var currentSlideIndex = 0;
    var lastFocusedBeforeFullscreen = null;

    // Barre du bas plutôt qu'une croix isolée en haut à droite : plus
    // proche des codes "stories" habituels, et permet d'ajouter la
    // navigation précédent/suivant entre les stories du même produit au
    // même endroit que la fermeture, sans geste de swipe obligatoire pour
    // les découvrir.
    function buildFullscreen() {
        if (fullscreenEl) return;

        fullscreenEl = document.createElement('div');
        fullscreenEl.id = 'navi-story-fullscreen';
        fullscreenEl.className = 'navi-story-fullscreen';
        fullscreenEl.setAttribute('aria-hidden', 'true');
        var chevronUp = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><polyline points="18 15 12 9 6 15"></polyline></svg>';
        var chevronDown = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><polyline points="6 9 12 15 18 9"></polyline></svg>';

        fullscreenEl.innerHTML =
            '<div class="navi-story-fullscreen-track" id="navi-story-track"></div>' +
            '<div class="navi-story-fullscreen-sidebar">' +
            '<button type="button" class="navi-story-fullscreen-nav navi-story-fullscreen-prev" aria-label="' + config.prevLabel + '">' + chevronUp + '</button>' +
            '<button type="button" class="navi-story-fullscreen-close" aria-label="' + config.closeLabel + '">✕</button>' +
            '<button type="button" class="navi-story-fullscreen-nav navi-story-fullscreen-next" aria-label="' + config.nextLabel + '">' + chevronDown + '</button>' +
            '</div>';
        document.body.appendChild(fullscreenEl);

        fullscreenTrack = fullscreenEl.querySelector('#navi-story-track');
        fullscreenPrevBtn = fullscreenEl.querySelector('.navi-story-fullscreen-prev');
        fullscreenCloseBtn = fullscreenEl.querySelector('.navi-story-fullscreen-close');
        fullscreenNextBtn = fullscreenEl.querySelector('.navi-story-fullscreen-next');
        fullscreenCloseBtn.addEventListener('click', closeFullscreen);
        fullscreenPrevBtn.addEventListener('click', function () { goToSlide(currentSlideIndex - 1); });
        fullscreenNextBtn.addEventListener('click', function () { goToSlide(currentSlideIndex + 1); });

        document.addEventListener('keydown', handleFullscreenKeydown);

        fullscreenObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                var iframe = entry.target.querySelector('iframe');
                if (!iframe) return;
                var mask = entry.target.querySelector('.navi-story-video-mask');
                var videoId = entry.target.getAttribute('data-video-id');
                // getAttribute (pas la propriété .src) : un attribut src=""
                // vide se lit, via la PROPRIÉTÉ .src, comme l'URL de la
                // page courante résolue (jamais une chaîne vide).
                if (entry.isIntersecting && entry.intersectionRatio > 0.6) {
                    if (!iframe.getAttribute('src')) {
                        iframe.src = buildVideoUrl(videoId);
                        attachVideoMask(iframe, mask, videoId);
                    }
                } else {
                    iframe.src = '';
                    if (mask) {
                        mask.classList.add('is-visible');
                        mask.classList.remove('is-ended');
                    }
                }
            });
        }, { root: fullscreenTrack, threshold: [0, 0.6] });

        // Index courant déduit du scroll (pas seulement de
        // l'IntersectionObserver, qui suit chaque slide indépendamment) —
        // sert à activer/désactiver les boutons précédent/suivant de la
        // barre du bas.
        var navScrollTicking = false;
        fullscreenTrack.addEventListener('scroll', function () {
            if (navScrollTicking) return;
            navScrollTicking = true;
            window.requestAnimationFrame(function () {
                updateCurrentIndexFromScroll();
                navScrollTicking = false;
            });
        }, { passive: true });

        setupSwipeToDismiss();
    }

    // Piège à focus clavier — le plein écran mobile n'a que 3 boutons
    // interactifs possibles (précédent/fermer/suivant, précédent/suivant
    // parfois masqués s'il n'y a qu'une story). Sans ce piège, Tab
    // continuerait vers le contenu de la page EN DESSOUS du plein écran
    // (visuellement masqué mais toujours présent dans le DOM), perdant
    // l'utilisateur clavier hors de l'overlay qu'il croit fermé.
    // `!el.disabled` : sur la première/dernière story, précédent/suivant
    // est désactivé (voir updateNavButtons()) — un bouton disabled ne peut
    // pas recevoir le focus, le laisser dans la liste faisait boucler le
    // piège sur le même élément sans jamais y arriver.
    function getFullscreenFocusableElements() {
        return [fullscreenPrevBtn, fullscreenCloseBtn, fullscreenNextBtn].filter(function (el) {
            return el && !el.disabled && getComputedStyle(el).display !== 'none';
        });
    }

    function handleFullscreenKeydown(event) {
        if (!fullscreenEl || !fullscreenEl.classList.contains('is-open')) return;

        if (event.key === 'Escape') {
            closeFullscreen();
            return;
        }
        if (event.key !== 'Tab') return;

        var focusable = getFullscreenFocusableElements();
        if (!focusable.length) return;
        var first = focusable[0];
        var last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    // Swipe horizontal pour sortir de la vidéo. Sur l'axe HORIZONTAL, pas
    // vertical : l'axe vertical sert déjà au défilement scroll-snap entre
    // les stories du même produit — un swipe vertical pour fermer serait
    // ambigu avec cette navigation déjà en place. Suivi en simple delta
    // début/fin (pas de suivi visuel du doigt), distance horizontale nette
    // au relâché, nettement plus grande que le déplacement vertical (pour
    // ne pas se déclencher sur un scroll vertical légèrement oblique).
    function setupSwipeToDismiss() {
        var startX = 0;
        var startY = 0;
        var tracking = false;

        fullscreenEl.addEventListener('touchstart', function (event) {
            if (event.touches.length !== 1) { tracking = false; return; }
            startX = event.touches[0].clientX;
            startY = event.touches[0].clientY;
            tracking = true;
        }, { passive: true });

        fullscreenEl.addEventListener('touchend', function (event) {
            if (!tracking) return;
            tracking = false;

            var touch = event.changedTouches[0];
            var dx = touch.clientX - startX;
            var dy = touch.clientY - startY;

            if (Math.abs(dx) > 80 && Math.abs(dx) > Math.abs(dy) * 1.5) {
                closeFullscreen();
            }
        }, { passive: true });
    }

    function updateNavButtons() {
        var total = fullscreenSlideEls.length;
        if (total <= 1) {
            fullscreenPrevBtn.style.display = 'none';
            fullscreenNextBtn.style.display = 'none';
            return;
        }
        fullscreenPrevBtn.style.display = '';
        fullscreenNextBtn.style.display = '';
        fullscreenPrevBtn.disabled = currentSlideIndex <= 0;
        fullscreenNextBtn.disabled = currentSlideIndex >= total - 1;
    }

    function updateCurrentIndexFromScroll() {
        if (!fullscreenSlideEls.length) return;
        var slideHeight = fullscreenTrack.clientHeight || 1;
        var idx = Math.round(fullscreenTrack.scrollTop / slideHeight);
        idx = Math.max(0, Math.min(fullscreenSlideEls.length - 1, idx));
        if (idx !== currentSlideIndex) {
            currentSlideIndex = idx;
            updateNavButtons();
        }
    }

    function goToSlide(index) {
        if (index < 0 || index >= fullscreenSlideEls.length) return;
        fullscreenSlideEls[index].scrollIntoView({ block: 'start', behavior: 'smooth' });
    }

    function collectStoriesForProduct(productId) {
        var seen = {};
        var stories = [];

        document.querySelectorAll(
            '.navi-story-bubble[data-video-id][data-product-id="' + productId + '"]'
        ).forEach(function (b) {
            var videoId = b.getAttribute('data-video-id');
            if (!videoId || seen[videoId]) return;
            seen[videoId] = true;
            stories.push({ videoId: videoId, label: b.getAttribute('data-label') || '' });
        });

        return stories;
    }

    function openFullscreen(bubble, videoId, label) {
        buildFullscreen();
        if (!fullscreenEl) return;

        var productId = bubble.getAttribute('data-product-id') || '';
        var stories = collectStoriesForProduct(productId);
        if (!stories.length) stories = [{ videoId: videoId, label: label }];

        fullscreenTrack.innerHTML = '';
        var activeSlide = null;

        // .is-open (donc display != none) AVANT d'observer les slides : un
        // élément caché (display:none) a une intersection nulle pour
        // l'IntersectionObserver au moment de observe() — l'y ajouter en
        // premier garantit un premier calcul correct dès l'ouverture,
        // plutôt que de dépendre d'un recalcul ultérieur (scroll/resize)
        // pas toujours déclenché dans tous les navigateurs.
        fullscreenEl.classList.add('is-open');
        fullscreenEl.setAttribute('aria-hidden', 'false');
        document.body.classList.add('navi-story-fullscreen-lock');

        // Focus déplacé vers la croix de fermeture, restauré sur l'élément
        // d'origine (la bulle cliquée) à la fermeture — même patron que
        // openModal()/closeModal() dans cookie-consent.js.
        lastFocusedBeforeFullscreen = document.activeElement;
        if (fullscreenCloseBtn) fullscreenCloseBtn.focus();

        var activeIndex = 0;

        stories.forEach(function (story, index) {
            var slide = document.createElement('div');
            slide.className = 'navi-story-fullscreen-slide';
            slide.setAttribute('data-video-id', story.videoId);
            // tabindex="-1" sur l'iframe : sans ça, Tab peut y entrer et le
            // focus se retrouve piégé dans le document (cross-origin) de la
            // vidéo YouTube, hors de portée de handleFullscreenKeydown
            // ci-dessous — aucun moyen d'en ressortir au clavier.
            slide.innerHTML =
                '<iframe src="" title="' + story.label + '" tabindex="-1" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture"></iframe>' +
                '<div class="navi-story-fullscreen-guard-top" aria-hidden="true"></div>' +
                '<div class="navi-story-fullscreen-guard-bottom" aria-hidden="true"></div>' +
                videoMaskMarkup;
            fullscreenTrack.appendChild(slide);
            if (story.videoId === videoId) {
                activeSlide = slide;
                activeIndex = index;
            }
        });

        fullscreenSlideEls = Array.prototype.slice.call(
            fullscreenTrack.querySelectorAll('.navi-story-fullscreen-slide')
        );
        currentSlideIndex = activeIndex;
        updateNavButtons();

        if (activeSlide) {
            // Défilement instantané (pas d'animation) : on ouvre déjà DANS
            // la bonne story, ce n'est pas un scroll utilisateur. Fait
            // AVANT observe() : la position de scroll doit être stable
            // avant le premier calcul d'intersection, sinon la slide
            // active peut être évaluée à sa position de départ (avant
            // scroll) et rater le seuil de 0.6.
            activeSlide.scrollIntoView({ block: 'start', behavior: 'auto' });
        }

        fullscreenSlideEls.forEach(function (slide) {
            fullscreenObserver.observe(slide);
        });
    }

    function closeFullscreen() {
        if (!fullscreenEl) return;

        fullscreenEl.classList.remove('is-open');
        fullscreenEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('navi-story-fullscreen-lock');

        fullscreenTrack.querySelectorAll('iframe').forEach(function (iframe) {
            iframe.src = '';
        });
        fullscreenSlideEls.forEach(function (slide) {
            fullscreenObserver.unobserve(slide);
        });
        fullscreenTrack.innerHTML = '';
        fullscreenSlideEls = [];

        if (lastFocusedBeforeFullscreen && typeof lastFocusedBeforeFullscreen.focus === 'function') {
            lastFocusedBeforeFullscreen.focus();
        }
        lastFocusedBeforeFullscreen = null;
        currentSlideIndex = 0;
    }

    // ============================================================
    // Clic sur une bulle : Navi rend ses propres bulles nativement (voir
    // includes/modules/stories/public-display.php), un simple écouteur
    // délégué en phase de bulle standard suffit — pas de listener
    // concurrent à préempter, contrairement à une intégration qui lirait
    // le DOM d'un module tiers.
    // ============================================================
    document.addEventListener('click', function (event) {
        var bubble = event.target.closest ? event.target.closest('.navi-story-bubble[data-video-id]') : null;
        if (!bubble) return;

        var videoId = bubble.getAttribute('data-video-id');
        if (!videoId) return;

        var label = bubble.getAttribute('data-label') || '';
        if (isMobile()) {
            openFullscreen(bubble, videoId, label);
        } else {
            openPanelStory(videoId, label);
        }
    });

    });
})();
