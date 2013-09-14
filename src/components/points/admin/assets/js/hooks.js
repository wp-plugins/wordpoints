/**
 * Points Types hooks UI.
 *
 * Based on the widgets UI, obviously.
 *
 * @package WordPoints\Points\Administration
 * @since 1.0.0
 */

/**
 * @var object WordPointsHooks
 */
var WordPointsHooks;

(function ( $ ) {

WordPointsHooks = {

	/**
	 * Initialize.
	 */
	init : function() {

		var rem,
			points_types = $( 'div.hooks-sortables' ),
			isRTL = !! ( 'undefined' != typeof isRtl && isRtl ),
			margin = ( isRtl ? 'marginRight' : 'marginLeft' ),
			the_id;

		// Require confirmation for points type delete.
		$( '.points-settings .delete' ).click( function( event ) {

			if ( ! confirm( WordPointsHooksL10n.confirmDelete ) ) {

				event.preventDefault();
			}
		});

		$( '#hooks-right' ).children( '.hooks-holder-wrap' ).children( '.points-type-name' ).click( function () {

			var c = $( this ).siblings( '.hooks-sortables' ),
				p = $( this ).parent();

			if ( ! p.hasClass( 'closed' ) ) {

				c.sortable( 'disable' );
				p.addClass( 'closed' );

			} else {

				p.removeClass( 'closed' );
				c.sortable( 'enable' ).sortable( 'refresh' );
			}
		});

		// Open/close points type on click.
		$( '#hooks-left' ).children( '.hooks-holder-wrap' ).children( '.points-type-name' ).click( function () {

			$( this ).parent().toggleClass( 'closed' );
		});

		// Set the height of the points types.
		points_types.each( function () {

			if ( $( this ).parent().hasClass( 'inactive' ) )
				return true;

			var h = 50,
				H = $( this ).children( '.hook' ).length;

			h = h + parseInt( H * 48, 10 );
			$( this ).css( 'minHeight', h + 'px' );
		});

		// Let hooks toggle.
		$( document.body ).bind( 'click.hooks-toggle', function ( e ) {

			var target = $( e.target ),
				css = {},
				hook,
				inside,
				w;

			if ( target.parents( '.hook-top' ).length && ! target.parents( '#available-hooks' ).length ) {

				hook = target.closest( 'div.hook' );
				inside = hook.children( '.hook-inside' );
				w = parseInt( hook.find( 'input.hook-width' ).val(), 10 );

				if ( inside.is( ':hidden' ) ) {

					if ( w > 250 && inside.closest( 'div.hooks-sortables' ).length ) {

						css['width'] = w + 30 + 'px';

						if ( inside.closest( 'div.hook-liquid-right' ).length )
							css[margin] = 235 - w + 'px';

						hook.css( css );
					}

					WordPointsHooks.fixLabels( hook );
					inside.slideDown( 'fast' );

				} else {

					inside.slideUp( 'fast', function () {

						hook.css( { 'width':'', margin:'' } );
					});
				}

				e.preventDefault();

			} else if ( target.hasClass( 'hook-control-save' ) ) {

				if ( ! target.parent().parent().parent().parent().hasClass( 'wordpoints-points-add-new' ) ) {

					WordPointsHooks.save( target.closest( 'div.hook' ), 0, 1, 0 );
					e.preventDefault();
				}

			} else if ( target.hasClass( 'hook-control-remove' ) ) {

				WordPointsHooks.save( target.closest( 'div.hook' ), 1, 1, 0 );
				e.preventDefault();

			} else if ( target.hasClass( 'hook-control-close' ) ) {

				WordPointsHooks.close( target.closest( 'div.hook' ) );
				e.preventDefault();
			}
		});

		// Append titles to hook names when provided.
		points_types.children( '.hook' ).each( function () {

			WordPointsHooks.appendTitle( this );

			if ( $( 'p.hook-error', this ).length )
				$( 'a.hook-action', this ).click();
		});

		// Make hooks draggable.
		$( '#hook-list' ).children( '.hook' ).draggable({
			connectToSortable: 'div.hooks-sortables',
			handle: '> .hook-top > .hook-title',
			distance: 2,
			helper: 'clone',
			zIndex: 100,
			containment: 'document',
			start: function ( e, ui ) {

				ui.helper.find( 'div.hook-description' ).hide();
				the_id = this.id;
			},
			stop: function ( e, ui ) {

				if ( rem )
					$( rem ).hide();

				rem = '';
			}
		});

		// Make hooks sortable.
		points_types.sortable({
			placeholder: 'hook-placeholder',
			items: '> .hook:not( .points-settings )',
			handle: '> .hook-top > .hook-title',
			cursor: 'move',
			distance: 2,
			containment: 'document',
			start: function ( e, ui ) {

				ui.item.children( '.hook-inside' ).hide();
				ui.item.css( { margin:'', 'width':'' } );
			},
			stop: function ( e, ui ) {

				if ( ui.item.hasClass( 'ui-draggable' ) && ui.item.data( 'draggable' ) )
					ui.item.draggable( 'destroy' );

				if ( ui.item.hasClass( 'deleting' ) ) {

					WordPointsHooks.save( ui.item, 1, 0, 1 ); // delete hook
					ui.item.remove();
					return;
				}

				var add = ui.item.find( 'input.add_new' ).val(),
					n = ui.item.find( 'input.multi_number' ).val(),
					id = the_id,
					sb = $( this ).attr( 'id' );

				ui.item.css( { margin:'', 'width':'' } );
				the_id = '';

				if ( add ) {

					// - This is a brand new hook.

					if ( 'multi' == add ) {

						ui.item.html( ui.item.html().replace( /<[^<>]+>/g, function ( m ) { return m.replace( /__i__|%i%/g, n ); } ) );
						ui.item.attr( 'id', id.replace( '__i__', n ) );
						n++;
						$( 'div#' + id ).find( 'input.multi_number' ).val( n );

					} else if ( 'single' == add ) {

						ui.item.attr( 'id', 'new-' + id );
						rem = 'div#' + id;
					}

					WordPointsHooks.save( ui.item, 0, 0, 1 );
					ui.item.find( 'input.add_new' ).val( '' );
					ui.item.find( 'a.hook-action' ).click();
					return;
				}

				WordPointsHooks.saveOrder( sb );
			},
			receive: function ( e, ui ) {

				var sender = $( ui.sender );

				if ( ! $( this ).is( ':visible' ) || this.id.indexOf( 'orphaned_hooks' ) != -1 )
					sender.sortable( 'cancel' );

				if ( sender.attr( 'id' ).indexOf( 'orphaned_hooks' ) != -1 && ! sender.children( '.hook' ).length ) {

					sender.parents( '.orphan-points-type' ).slideUp( 400, function () { $( this ).remove(); } );
				}
			}
		}).sortable( 'option', 'connectWith', 'div.hooks-sortables' ).parent().filter( '.closed' ).children( '.hooks-sortables' ).sortable( 'disable' );

		// Make available hooks droppable.
		$( '#available-hooks' ).droppable({
			tolerance: 'pointer',
			accept: function ( o ) {

				return $( o ).parent().attr( 'id' ) != 'hook-list';
			},
			drop: function ( e, ui ) {

				ui.draggable.addClass( 'deleting' );
				$( '#removing-hook' ).hide().children( 'span' ).html( '' );
			},
			over: function ( e, ui ) {

				ui.draggable.addClass( 'deleting' );
				$( 'div.hook-placeholder' ).hide();

				if ( ui.draggable.hasClass( 'ui-sortable-helper' ) )
					$( '#removing-hook' ).show().children( 'span' )
						.html( ui.draggable.find( 'div.hook-title' ).children( 'h4' ).html() );
			},
			out: function ( e, ui ) {

				ui.draggable.removeClass( 'deleting' );
				$( 'div.hook-placeholder' ).show();
				$( '#removing-hook' ).hide().children( 'span' ).html( '' );
			}
		});
	},

	/**
	 * Save hook display order.
	 */
	saveOrder : function ( sb ) {
		if ( sb )
			$( '#' + sb ).closest( 'div.hooks-holder-wrap' ).find( '.spinner' ).css( 'display', 'inline-block' );

		var a = {
			action: 'wordpoints-points-hooks-order',
			savehooks: $( '#_wpnonce_hooks' ).val(),
			points_types: []
		};

		$( 'div.hooks-sortables' ).each( function () {

			if ( $( this ).sortable )
				a['points_types[' + $( this ).attr( 'id' ) + ']'] = $( this ).sortable( 'toArray' ).join( ',' );
		});

		$.post( ajaxurl, a, function() {

			$( '.spinner ').hide();
		});

		this.resize();
	},

	/**
	 * Save hook settings.
	 */
	save : function ( hook, del, animate, order ) {
		var sb = hook.closest( 'div.hooks-sortables' ).attr( 'id' ),
			data = hook.find( 'form' ).serialize(),
			a;

		hook = $( hook );
		$( '.spinner', hook ).show();

		a = {
			action: 'save-wordpoints-points-hook',
			savehooks: $( '#_wpnonce_hooks' ).val(),
			points_type: sb
		};

		if ( del )
			a['delete_hook'] = 1;

		data += '&' + $.param( a );

		$.post( ajaxurl, data, function ( r ) {

			var id;

			if ( del ) {

				if ( ! $( 'input.hook_number', hook ).val() ) {

					id = $( 'input.hook-id', hook ).val();
					$( '#available-hooks' ).find( 'input.hook-id' ).each( function () {

						if ( $( this ).val() == id )
							$( this ).closest( 'div.hook' ).show();
					});
				}

				if ( animate ) {

					order = 0;
					hook.slideUp( 'fast', function () {

						$( this ).remove();
						WordPointsHooks.saveOrder();
					});

				} else {

					hook.remove();
					WordPointsHooks.resize();
				}

			} else {

				$( '.spinner' ).hide();

				if ( r && r.length > 2 ) {

					$( 'div.hook-content', hook ).html( r );
					WordPointsHooks.appendTitle( hook );
					WordPointsHooks.fixLabels( hook );
				}
			}

			if ( order )
				WordPointsHooks.saveOrder();
		});
	},

	/**
	 * Append the hook title.
	 */
	appendTitle : function ( hook ) {

		var title = $( 'input[id*="-title"]', hook ).val() || '';

		if ( title )
			title = ': ' + title.replace( /<[^<>]+>/g, '' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );

		$( hook ).children( '.hook-top' ).children( '.hook-title' ).children()
			.children( '.in-hook-title' ).html( title );
	},

	/**
	 * Resize the hook box.
	 */
	resize : function () {

		$( 'div.hooks-sortables' ).each( function () {

			if ( $( this ).parent().hasClass( 'inactive' ) )
				return true;

			var h = 50, H = $( this ).children( '.hook' ).length;
			h = h + parseInt( H * 48, 10 );
			$( this ).css( 'minHeight', h + 'px' );
		});
	},

	/**
	 * Fix label element 'for' attributes.
	 */
	fixLabels : function ( hook ) {

		hook.children( '.hook-inside' ).find( 'label' ).each( function () {

			var f = $( this ).attr( 'for' );
			if ( f && f == $( 'input', this ).attr( 'id' ) )
				$( this ).removeAttr( 'for' );
		});
	},

	/**
	 * Close the hook box.
	 */
	close : function ( hook ) {

		hook.children( '.hook-inside' ).slideUp( 'fast', function () {

			hook.css( { 'width':'', margin:'' } );
		});
	}
};

$( document ).ready( function ( $ ) { WordPointsHooks.init(); } );

})(jQuery);
