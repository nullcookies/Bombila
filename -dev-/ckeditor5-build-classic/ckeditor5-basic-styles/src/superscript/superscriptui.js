/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module basic-styles/superscript/superscriptui
 */

import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';

import superscriptIcon from '../../theme/icons/superscript.svg';

const SUPERSCRIPT = 'superscript';

/**
 * The superscript UI feature. It introduces the Superscript button.
 *
 * @extends module:core/plugin~Plugin
 */
export default class SuperscriptUI extends Plugin {
	/**
	 * @inheritDoc
	 */
	init() {
		const editor = this.editor;
		const t = editor.t;

		// Add superscript button to feature components.
		editor.ui.componentFactory.add( SUPERSCRIPT, locale => {
			const command = editor.commands.get( SUPERSCRIPT );
			const view = new ButtonView( locale );

			view.set( {
				label: t( 'Superscript' ),
				icon: superscriptIcon,
				tooltip: true
			} );

			view.bind( 'isOn', 'isEnabled' ).to( command, 'value', 'isEnabled' );

			// Execute command.
			this.listenTo( view, 'execute', () => editor.execute( SUPERSCRIPT ) );

			return view;
		} );
	}
}
