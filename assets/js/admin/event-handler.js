/**
 * Handle the different events.
 *
 * @since      0.1
 * @package    WordPressPrimaryCategory
 */

/* global wpcData, wpApiSettings */

import apiFetch from '@wordpress/api-fetch';
import { addPrimaryElement } from './helper-functions';

/**
 * Add or remove primary element based on checkbox. Don't forget, this is a post event listener.
 *
 * @param element The element that was (un)checked.
 * @param addPrimaryElement A callback function to add a new primary element.
 */
export const checkToggle = ( element, addPrimaryElement ) => {
	const categoryId = element.value;
	if ( element.checked ) {
		addPrimaryElement();
	} else {
		document.getElementById( `wpc-primary-selector-${categoryId}` ).remove();
	}
};

/**
 * Add or remove primary element based on checkbox. Don't forget, this is a post event listener.
 *
 * @param primaryElement
 * @param categoryId
 */
export const setPrimaryCategory = ( primaryElement, categoryId ) => {
	primaryElement.setAttribute( 'class', 'wpc-primary-selector updating' );
	const currentCategoryId = parseInt( wpcData.primaryCategoryId );
	let currentPrimaryElement = null;

	if ( !isNaN( currentCategoryId ) ) {
		currentPrimaryElement = document.getElementById( `wpc-primary-selector-${currentCategoryId}` );
	}

	const postId   = parseInt( document.getElementById( 'post_ID' ).value );
	const postData = {
		'category_id': categoryId,
		'post_id': postId,
		'old_category_id': currentCategoryId
	};

	apiFetch( {
		url: `${wpApiSettings.root}wpc/v1/set`,
		data: postData,
		headers: {
			'X-WP-Nonce': wpApiSettings.nonce
		},
		method: 'POST'
	} ).then( data => {
		if ( data.success ) {
			wpcData.primaryCategoryId = categoryId;
			addPrimaryElement( primaryElement.parentElement, categoryId, true );
			primaryElement.remove();
			if ( !isNaN( currentCategoryId ) ) {
				const parentElement = currentPrimaryElement.parentElement;
				addPrimaryElement( parentElement, currentCategoryId, false );
				currentPrimaryElement.remove();
			}
		} else {
			alert( 'Could not set primary category. Please try again.' ); // Would like suggestions from a UX perspective on how to handle this error message.
			primaryElement.setAttribute( 'class', 'wpc-primary-selector' );
		}
	} ).catch( () => {
		alert( 'Could not set primary category. Please try again.' ); // Would like suggestions from a UX perspective on how to handle this error message.
		primaryElement.setAttribute( 'class', 'wpc-primary-selector' );
	} );
};