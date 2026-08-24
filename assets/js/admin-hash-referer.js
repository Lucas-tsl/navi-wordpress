// Voir navi_render_hash_preserving_referer_field() (includes/core/helpers.php) :
// à appeler juste après settings_fields() dans chaque onglet de Navi > Navi,
// le champ _wp_http_referer que settings_fields() ajoute déjà ne capture que
// REQUEST_URI côté serveur, qui ne contient jamais le fragment d'URL
// (#onglet) — un navigateur ne l'envoie jamais au serveur. Ce second champ,
// de même nom, est rempli ici à la soumission du formulaire (pas au
// chargement de la page : l'onglet actif — donc le hash — peut avoir changé
// entretemps) avec l'URL complète telle que vue par le navigateur ; comme les
// deux champs partagent le même name="_wp_http_referer", PHP ne garde dans
// $_POST que la dernière valeur soumise — la nôtre, placée après. Sans ça,
// tout enregistrement ramènerait sur l'onglet "Général" (redirection
// d'options.php, qui n'a jamais connu le hash).
//
// Plusieurs formulaires (un par module) peuvent chacun avoir leur propre
// champ sur la même page : écoute déléguée sur document plutôt qu'un
// document.currentScript par instance, puisque ce script n'est chargé qu'une
// seule fois pour toute la page.
(function () {
    'use strict';

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || typeof form.querySelector !== 'function') {
            return;
        }
        var input = form.querySelector('.navi-hash-referer-input');
        if (input) {
            input.value = window.location.pathname + window.location.search + window.location.hash;
        }
    });
})();
