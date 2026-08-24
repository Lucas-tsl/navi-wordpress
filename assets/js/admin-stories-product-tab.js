// Onglet "Stories (Navi)" de la fiche produit WooCommerce — voir
// navi_stories_render_product_panel() (includes/modules/stories/admin-product-tab.php).
// naviStoryAdminData est injecté par wp_localize_script().
(function () {
    'use strict';

    function extractYoutubeId(input) {
        input = (input || '').trim();
        if (!input) return '';
        var urlMatch = input.match(/(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
        if (urlMatch) return urlMatch[1];
        if (/^[A-Za-z0-9_-]{11}$/.test(input)) return input;
        return '';
    }

    function updatePreview(slot) {
        var input = document.getElementById('navi_story_youtube_' + slot);
        var thumb = document.getElementById('navi-story-admin-thumb-' + slot);
        var placeholder = document.getElementById('navi-story-admin-placeholder-' + slot);
        var badge = document.getElementById('navi-story-badge-' + slot);
        if (!input || !thumb || !placeholder || !badge) return;

        var videoId = extractYoutubeId(input.value);
        if (videoId) {
            thumb.src = 'https://img.youtube.com/vi/' + videoId + '/mqdefault.jpg';
            thumb.style.display = 'block';
            placeholder.style.display = 'none';
            badge.textContent = naviStoryAdminData.labelConfigured;
            badge.classList.add('is-filled');
        } else {
            thumb.style.display = 'none';
            placeholder.style.display = 'block';
            badge.textContent = naviStoryAdminData.labelEmpty;
            badge.classList.remove('is-filled');
        }
    }

    function openMediaPicker(slot) {
        if (typeof wp === 'undefined' || !wp.media) return;

        var frame = wp.media({
            title: naviStoryAdminData.mediaTitle,
            library: { type: 'video/mp4' },
            multiple: false,
            button: { text: naviStoryAdminData.mediaButton }
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var info = document.getElementById('navi-story-admin-file-info-' + slot);
            if (!info) return;

            if ('video/mp4' !== attachment.mime) {
                info.textContent = naviStoryAdminData.labelInvalidType;
                info.classList.add('is-warning');
                return;
            }

            var previewInput = document.getElementById('navi_story_preview_' + slot);
            if (previewInput) {
                previewInput.value = attachment.url;
            }

            var sizeMb = attachment.filesizeInBytes ? (attachment.filesizeInBytes / 1048576).toFixed(1) + ' Mo' : '';
            info.textContent = attachment.filename + (sizeMb ? ' — ' + sizeMb : '');
            info.classList.remove('is-warning');
        });

        frame.open();
    }

    var youtubeInputs = document.querySelectorAll('.navi-story-admin-youtube-input');
    for (var i = 0; i < youtubeInputs.length; i++) {
        youtubeInputs[i].addEventListener('input', function (event) {
            updatePreview(event.target.getAttribute('data-slot'));
        });
    }

    var mediaButtons = document.querySelectorAll('.navi-story-admin-media-button');
    for (var k = 0; k < mediaButtons.length; k++) {
        mediaButtons[k].addEventListener('click', function (event) {
            event.preventDefault();
            openMediaPicker(event.currentTarget.getAttribute('data-slot'));
        });
    }
})();
