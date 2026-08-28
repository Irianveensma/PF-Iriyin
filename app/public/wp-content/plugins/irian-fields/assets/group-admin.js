/**
 * Irian Fields: veldgroep-editor (definitie van velden + locatie-regels).
 *
 * Rijen worden opgebouwd uit platte templates (#irf-tpl-def / #irf-tpl-subdef /
 * #irf-tpl-layout). De placeholder __BASE__ wordt vervangen door het volledige
 * form-pad; elke rij krijgt een uniek getal als index. PHP herindexeert bij het
 * opslaan, dus de getallen hoeven niet netjes op te lopen.
 */
( function ( $ ) {
	'use strict';

	var uid = Date.now();
	function nextUid() { return ++uid; }
	function freshKey() { return 'field_' + Math.random().toString( 36 ).slice( 2, 12 ); }

	function build( tplId, base ) {
		var html = $( '#' + tplId ).html();
		if ( ! html ) { return null; }
		var $node = $( html.split( '__BASE__' ).join( base ) );
		$node.find( '.irf-def-key' ).val( freshKey() );
		return $node;
	}

	$( function () {
		var $doc = $( document );

		/* ---------- Veld / subveld toevoegen ---------- */
		$doc.on( 'click', '.irf-def-add', function ( e ) {
			e.preventDefault();
			var target = $( this ).data( 'target' ); // fields | sub | laysub
			var $list, base, tpl;

			if ( 'fields' === target ) {
				$list = $( this ).prev( '.irf-def-list' );
				base  = '_irf_fields[' + nextUid() + ']';
				tpl   = 'irf-tpl-def';
			} else if ( 'sub' === target ) {
				var $fieldRow = $( this ).closest( '.irf-def-row' );
				$list = $( this ).prev( '.irf-def-list' );
				base  = $fieldRow.data( 'base' ) + '[sub_fields][' + nextUid() + ']';
				tpl   = 'irf-tpl-subdef';
			} else { // laysub
				var $layRow = $( this ).closest( '.irf-lay-row' );
				$list = $( this ).prev( '.irf-def-list' );
				base  = $layRow.data( 'base' ) + '[sub_fields][' + nextUid() + ']';
				tpl   = 'irf-tpl-subdef';
			}

			var $row = build( tpl, base );
			if ( $row ) {
				$list.append( $row );
				sync( $row );
			}
		} );

		/* ---------- Layout-blok toevoegen ---------- */
		$doc.on( 'click', '.irf-lay-add', function ( e ) {
			e.preventDefault();
			var $fieldRow = $( this ).closest( '.irf-def-row' );
			var base = $fieldRow.data( 'base' ) + '[layouts][' + nextUid() + ']';
			var $row = build( 'irf-tpl-layout', base );
			if ( $row ) {
				$( this ).prev( '.irf-lay-list' ).append( $row );
				bindSortable();
			}
		} );

		/* ---------- Verwijderen / in-uitklappen ---------- */
		$doc.on( 'click', '.irf-def-remove', function () {
			if ( window.confirm( 'Dit veld verwijderen?' ) ) { $( this ).closest( '.irf-def-row' ).remove(); }
		} );
		$doc.on( 'click', '.irf-lay-remove', function () {
			if ( window.confirm( 'Dit layout-blok verwijderen?' ) ) { $( this ).closest( '.irf-lay-row' ).remove(); }
		} );
		$doc.on( 'click', '.irf-def-toggle', function () {
			$( this ).closest( '.irf-def-row' ).toggleClass( 'is-collapsed' );
		} );

		/* ---------- Conditionele instellingen per type ---------- */
		$doc.on( 'change keyup', '.irf-def-type', function () {
			applyTypeVisibility( $( this ).closest( '.irf-def-row' ) );
		} );

		/* ---------- Label -> naam + samenvatting ---------- */
		$doc.on( 'input', '.irf-def-label-src', function () {
			var $row = $( this ).closest( '.irf-def-row, .irf-lay-row' );
			$row.children( '.irf-def-row-head' ).find( '.irf-def-summary' ).text( $( this ).val() || 'veld' );
			var $name = $row.find( '.irf-def-name-out' ).first();
			if ( $name.length && ! $name.data( 'touched' ) ) {
				$name.val( slug( $( this ).val() ) );
			}
		} );
		$doc.on( 'input', '.irf-def-name-out', function () {
			$( this ).data( 'touched', true );
			$( this ).val( slug( $( this ).val() ) );
		} );

		/* ---------- Locatie-regels ---------- */
		$doc.on( 'click', '.irf-loc-add', function ( e ) {
			e.preventDefault();
			var tpl = $( '#irf-loc' ).children( '.irf-loc-tpl' ).html();
			var $rule = $( tpl.split( '__i__' ).join( nextUid() ) );
			$( '#irf-loc .irf-loc-list' ).append( $rule );
		} );
		$doc.on( 'click', '.irf-loc-remove', function () {
			$( this ).closest( '.irf-loc-rule' ).remove();
		} );
		$doc.on( 'change', '.irf-loc-param', function () {
			$( this ).closest( '.irf-loc-rule' ).find( '.irf-loc-value-wrap' )
				.html( '<em style="color:#646970">Bewaar de groep om de keuzes voor deze parameter te laden.</em>' );
		} );

		/* ---------- Sortable ---------- */
		function bindSortable() {
			$( '.irf-def-list' ).each( function () {
				if ( $( this ).data( 'irf-s' ) ) { return; }
				$( this ).data( 'irf-s', true ).sortable( {
					handle: '> .irf-def-row > .irf-def-row-head > .irf-def-handle',
					items: '> .irf-def-row',
					axis: 'y',
					tolerance: 'pointer'
				} );
			} );
			$( '.irf-lay-list' ).each( function () {
				if ( $( this ).data( 'irf-s' ) ) { return; }
				$( this ).data( 'irf-s', true ).sortable( { handle: '.irf-def-handle', items: '> .irf-lay-row', axis: 'y' } );
			} );
		}

		function sync( $row ) {
			applyTypeVisibility( $row );
			bindSortable();
		}

		function applyTypeVisibility( $row ) {
			var $body = $row.children( '.irf-def-row-body' );
			var type = $body.find( '> .irf-def-field .irf-def-type' ).first().val()
				|| $body.find( '.irf-def-type' ).first().val();
			$body.children( '.irf-def-extra' ).each( function () {
				var list = ( $( this ).attr( 'data-show-for' ) || '' ).split( ',' );
				$( this ).toggle( list.indexOf( type ) !== -1 );
			} );
		}

		function slug( s ) {
			s = ( s || '' ).toString().toLowerCase();
			try { s = s.normalize( 'NFD' ).replace( /[̀-ͯ]/g, '' ); } catch ( e ) {}
			return s.replace( /[^a-z0-9_]+/g, '_' ).replace( /^_+|_+$/g, '' );
		}

		bindSortable();
		$( '.irf-def-row' ).each( function () { applyTypeVisibility( $( this ) ); } );
	} );
} )( jQuery );
