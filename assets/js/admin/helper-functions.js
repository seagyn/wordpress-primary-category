/**
 * Various functions used throughout Javascript.
 *
 * @since      0.1
 * @package    WordPressPrimaryCategory
 */

/* global wpcData */

import { setPrimaryCategory } from './event-handler';

/**
 * Add or remove primary element based on checkbox
 *
 * @param element Element that we are working with and will add the primary element.
 * @param categoryId If of the category we are working with.
 * @param isPrimary If this is already a primary.
 */
export const addPrimaryElement = ( element, categoryId, isPrimary ) => {
	let primaryElement = null;

	if ( !isPrimary ) {
		const primaryElement = document.createElement( 'a' );
		const elementText    = document.createTextNode( wpcData.label );

		primaryElement.appendChild( elementText );
		primaryElement.setAttribute( 'class', 'wpc-primary-selector' );
		primaryElement.setAttribute( 'id', `wpc-primary-selector-${categoryId}` );
		primaryElement.setAttribute( 'title', wpcData.linkTitle );
		primaryElement.setAttribute( 'data-category-id', categoryId );

		primaryElement.addEventListener( 'click', event => {
			event.preventDefault();
			setPrimaryCategory( event.target, categoryId );
		} );

		element.prepend( primaryElement );
	} else {
		const primaryElement = document.createElement( 'span' );
		const elementText    = document.createTextNode( wpcData.currentLabel );

		primaryElement.setAttribute( 'class', 'wpc-primary' );
		primaryElement.setAttribute( 'id', `wpc-primary-selector-${categoryId}` );
		primaryElement.appendChild( elementText );

		element.prepend( primaryElement );
	}

	return primaryElement;
};