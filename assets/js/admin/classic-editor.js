/**
 * Show the primary category features on the classic editor.
 *
 * @since      0.1
 * @package    WordPressPrimaryCategory
 */

/* global wpcData */

import { checkToggle } from './event-handler';
import { addPrimaryElement } from './helper-functions';

export default () => {
	const categories = document.getElementById( 'categorychecklist' );
	Array.prototype.forEach.call(
		categories.children,
		category => {
			const label      = category.getElementsByClassName( 'selectit' )[0];
			const input      = label.getElementsByTagName( 'input' )[0];
			const categoryId = parseInt( input.value );
			const isPrimary  = categoryId === parseInt( wpcData.primaryCategoryId );

			if ( input.checked ) {
				addPrimaryElement( label, categoryId, isPrimary );
			}

			input.addEventListener( 'change', event => {
				checkToggle( event.target,() => {
					addPrimaryElement( label, categoryId, isPrimary );
				} );
			} );
		}
	);
};
