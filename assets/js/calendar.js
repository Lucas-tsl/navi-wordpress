(function () {
    'use strict';

    // Panneau rendu par PHP en fin de page (includes/modules/calendar/public-display.php) :
    // déplacé dans le slot partagé du bouton flottant, comme les panneaux
    // cookies et accessibilité (voir assets/js/core.js).
    var panel = document.getElementById('navi-calendar-panel');
    var detail = document.getElementById('navi-fab-detail');
    if (!panel || !detail) return;
    detail.appendChild(panel);

    var closeBtn = panel.querySelector('.navi-calendar-close');

    function openPanel() {
        function apply() {
            panel.classList.add('navi-calendar-panel-open');
        }
        if (window.navi) {
            window.navi.showDetail('calendar', apply);
        } else {
            apply();
        }
    }

    // Croix : referme entièrement le bouton (le calendrier s'ouvre
    // directement depuis le bouton, pas depuis le menu).
    function closePanel() {
        function apply() {
            panel.classList.remove('navi-calendar-panel-open');
        }
        if (window.navi) {
            window.navi.hideDetail('calendar', apply);
        } else {
            apply();
        }
    }

    document.addEventListener('navi:action', function (event) {
        if (event.detail && event.detail.action === 'open-calendar-panel') {
            openPanel();
        }
    });

    document.addEventListener('navi:closed', function (event) {
        if (event.detail && event.detail.id === 'calendar') {
            panel.classList.remove('navi-calendar-panel-open');
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closePanel);
    }
})();
