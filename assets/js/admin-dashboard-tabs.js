// Onglets de premier niveau de la page Navi > Navi (voir navi_render_dashboard_page(),
// includes/core/admin-menu.php) : afficher/masquer par hash d'URL
// ("#cookie-consent", "#stories"...), pour permettre les liens directs (ex.
// "Réglages" sur une carte de l'onglet Général) sans rechargement de page.
// Le hash peut porter un second segment pour un sous-onglet imbriqué (ex.
// "#stories/mockup", voir includes/modules/stories/admin-settings.php) :
// seul le premier segment est utilisé ici, le reste appartient au module.
(function () {
    'use strict';

    var tabs = document.querySelectorAll('#navi-main-tabs .nav-tab');
    var panels = document.querySelectorAll('.navi-admin-tab-panel');

    function activateTab(name) {
        var found = false;
        tabs.forEach(function (tab) {
            var match = tab.dataset.tab === name;
            tab.classList.toggle('nav-tab-active', match);
            if (match) found = true;
        });
        panels.forEach(function (panel) {
            panel.style.display = panel.dataset.tabPanel === name ? '' : 'none';
        });
        return found;
    }

    function fromHash() {
        var name = window.location.hash.replace('#', '').split('/')[0];
        if (!name || !activateTab(name)) activateTab('general');
    }

    window.addEventListener('hashchange', fromHash);
    fromHash();
})();
