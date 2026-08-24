// Onglet "Stories" de la page Navi > Navi — voir navi_stories_render_settings_panel()
// (includes/modules/stories/admin-settings.php). naviStoriesSettingsData est
// injecté par wp_localize_script().
(function () {
    'use strict';

    // Sous-onglets Bulles/Mockup, imbriqués dans l'onglet "Stories" de la
    // page Navi (voir navi-main-tabs, admin-menu.php) : le hash de l'URL est
    // partagé entre les deux niveaux au format "#<onglet-principal>/<sous-onglet>"
    // (ex. "#stories/mockup"), pour rester compatible avec le routage par
    // hash de la page plutôt que d'entrer en conflit avec lui (chacun ne
    // lit/écrit que son propre segment).
    var tabs = document.querySelectorAll('#navi-stories-tabs .nav-tab');
    var panels = document.querySelectorAll('.navi-stories-tab-panel');
    function activateTab(name) {
        tabs.forEach(function (tab) {
            tab.classList.toggle('nav-tab-active', tab.dataset.tab === name);
        });
        panels.forEach(function (panel) {
            panel.style.display = panel.dataset.tabPanel === name ? '' : 'none';
        });
    }
    function subTabFromHash() {
        var parts = window.location.hash.replace('#', '').split('/');
        activateTab('mockup' === parts[1] ? 'mockup' : 'bulles');
    }
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.hash = 'stories/' + tab.dataset.tab;
        });
    });
    window.addEventListener('hashchange', subTabFromHash);
    subTabFromHash();

    var borderRange = document.getElementById('navi_bubble_border_range');
    var borderOutput = document.getElementById('navi_bubble_border_output');
    var sizeRange = document.getElementById('navi_bubble_size_range');
    var sizeOutput = document.getElementById('navi_bubble_size_output');
    var styleSelect = document.getElementById('navi_bubble_border_style');
    var solidFields = document.getElementById('navi_bubble_border_solid_fields');
    var gradientFields = document.getElementById('navi_bubble_border_gradient_fields');
    var colorInput = document.getElementById('navi_stories_color_bubble_border');
    var angleRange = document.getElementById('navi_bubble_gradient_angle_range');
    var angleOutput = document.getElementById('navi_bubble_gradient_angle_output');
    var gradientColorInputs = document.querySelectorAll('.navi-gradient-color');
    var previewBubble = document.getElementById('naviPreviewBubble');

    function currentBorderBg() {
        if (styleSelect && 'solid' === styleSelect.value) {
            return colorInput && colorInput.value ? colorInput.value : naviStoriesSettingsData.bubbleBorderDefault;
        }
        var stops = Array.prototype.map.call(gradientColorInputs, function (input) {
            return input.value || input.getAttribute('data-default-color');
        });
        var angle = angleRange ? angleRange.value : naviStoriesSettingsData.defaultGradientAngle;
        return 'linear-gradient(' + angle + 'deg, ' + stops.join(', ') + ')';
    }

    function updateBubblePreview() {
        if (!previewBubble) return;
        previewBubble.style.borderWidth = borderRange.value + 'px';
        previewBubble.style.width = sizeRange.value + 'px';
        previewBubble.style.height = sizeRange.value + 'px';
        previewBubble.style.background = 'linear-gradient(#000,#000) padding-box, ' + currentBorderBg() + ' border-box';
    }

    if (styleSelect && solidFields && gradientFields) {
        styleSelect.addEventListener('change', function () {
            var isSolid = 'solid' === styleSelect.value;
            solidFields.style.display = isSolid ? '' : 'none';
            gradientFields.style.display = isSolid ? 'none' : '';
            updateBubblePreview();
        });
    }

    if (borderRange && sizeRange && previewBubble) {
        borderRange.addEventListener('input', function () {
            borderOutput.textContent = borderRange.value;
            updateBubblePreview();
        });
        sizeRange.addEventListener('input', function () {
            sizeOutput.textContent = sizeRange.value;
            updateBubblePreview();
        });
        if (angleRange) {
            angleRange.addEventListener('input', function () {
                angleOutput.textContent = angleRange.value;
                updateBubblePreview();
            });
        }
        // wp-color-picker remplace l'input par un widget iris ; son
        // événement 'change' (natif, redéclenché par iris à chaque
        // sélection) suffit à capter les mises à jour sans dépendre
        // de l'API jQuery interne du color picker.
        if (colorInput) {
            colorInput.addEventListener('change', updateBubblePreview);
            jQuery(colorInput).on('irischange', updateBubblePreview);
        }
        gradientColorInputs.forEach(function (input) {
            input.addEventListener('change', updateBubblePreview);
            jQuery(input).on('irischange', updateBubblePreview);
        });
    }

    var paddingRange = document.getElementById('navi_phone_padding_range');
    var widthRange = document.getElementById('navi_phone_width_range');
    var paddingOutput = document.getElementById('navi_phone_padding_output');
    var widthOutput = document.getElementById('navi_phone_width_output');
    var previewPhone = document.getElementById('naviPreviewPhone');
    var zoomRange = document.getElementById('navi_video_zoom_range');
    var zoomOutput = document.getElementById('navi_video_zoom_output');
    var previewVideo = document.getElementById('naviPreviewVideo');
    if (!paddingRange || !widthRange || !previewPhone) return;

    paddingRange.addEventListener('input', function () {
        paddingOutput.textContent = paddingRange.value;
        previewPhone.style.padding = paddingRange.value + 'px';
    });
    widthRange.addEventListener('input', function () {
        widthOutput.textContent = widthRange.value;
        previewPhone.style.width = widthRange.value + 'px';
    });
    if (zoomRange && previewVideo) {
        zoomRange.addEventListener('input', function () {
            zoomOutput.textContent = zoomRange.value;
            previewVideo.style.transform = 'scale(' + (zoomRange.value / 100) + ')';
        });
    }
})();
