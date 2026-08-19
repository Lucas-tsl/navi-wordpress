(function (blocks, element, blockEditor, components, i18n, ServerSideRender) {
    var el = element.createElement;
    var Fragment = element.Fragment;
    var __ = i18n.__;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;

    // Bloc dynamique (pas de save() JS) : le rendu passe toujours par
    // navi_stories_shortcode() côté serveur (voir includes/modules/stories/block.php),
    // jamais dupliqué ici — l'aperçu dans l'éditeur (ServerSideRender) appelle
    // ce même render_callback via l'API REST du bloc-renderer.
    blocks.registerBlockType('navi/stories', {
        title: __('Navi Stories', 'saito-navi'),
        description: __('Video story bubbles for a WooCommerce product.', 'saito-navi'),
        icon: 'video-alt3',
        category: 'woocommerce',
        attributes: {
            productId: { type: 'string', default: '' }
        },
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return el(
                Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: __('Settings', 'saito-navi') },
                        el(TextControl, {
                            label: __('Product ID', 'saito-navi'),
                            help: __('Leave empty to use the current product.', 'saito-navi'),
                            value: attributes.productId,
                            onChange: function (value) {
                                setAttributes({ productId: value });
                            }
                        })
                    )
                ),
                el(ServerSideRender, {
                    block: 'navi/stories',
                    attributes: attributes
                })
            );
        },
        save: function () {
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n,
    window.wp.serverSideRender
);
