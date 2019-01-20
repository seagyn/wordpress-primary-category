import domReady from '@wordpress/dom-ready';
import ClassicEditor from './classic-editor';
import BlockEditor from './block-editor';

/* global wp */

domReady( () => {
	if ( wp.blocks ) {
		BlockEditor();
	} else {
		ClassicEditor();
	}
} );
