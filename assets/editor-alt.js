( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.hooks || ! wp.compose || ! wp.element || ! wp.data ) {
		return;
	}

	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var createElement = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var select = wp.data.select;

	var withAutomaticImageAlt = createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			useEffect( function () {
				if ( 'core/image' !== props.name ) {
					return;
				}

				var attributes = props.attributes || {};
				var hasImage = !! ( attributes.id || attributes.url );
				var currentAlt = 'string' === typeof attributes.alt ? attributes.alt.trim() : '';

				if ( ! hasImage || currentAlt ) {
					return;
				}

				var editorStore = select( 'core/editor' );
				if ( ! editorStore || 'function' !== typeof editorStore.getEditedPostAttribute ) {
					return;
				}

				var title = editorStore.getEditedPostAttribute( 'title' );
				title = 'string' === typeof title ? title.replace( /<[^>]*>/g, '' ).trim() : '';

				if ( ! title ) {
					return;
				}

				props.setAttributes( { alt: title } );
			}, [ props.name, props.attributes && props.attributes.id, props.attributes && props.attributes.url, props.attributes && props.attributes.alt ] );

			return createElement( BlockEdit, props );
		};
	}, 'withAutomaticImageAlt' );

	addFilter(
		'editor.BlockEdit',
		'alt-auto-insert/automatic-image-alt',
		withAutomaticImageAlt
	);
} )( window.wp );
