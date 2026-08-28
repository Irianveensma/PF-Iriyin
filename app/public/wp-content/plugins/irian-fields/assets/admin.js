/**
 * Irian Fields: waarde-editor op post-schermen.
 * Repeater / flexible content / media / wysiwyg.
 *
 * Rij-indexen worden NIET hernummerd: nieuwe rijen krijgen een uniek getal als
 * key en de PHP-kant (array_values) herindexeert bij het opslaan. De DOM-volgorde
 * bepaalt de opslagvolgorde.
 */
( function ( $ ) {
	'use strict';

	var uid = Date.now();
	function nextUid() { return ++uid; }

	function materialize( tplHtml ) {
		return tplHtml.replace( /__i__/g, nextUid() );
	}

	function initWysiwyg( $scope ) {
		if ( typeof wp === 'undefined' || ! wp.editor ) { return; }
		$scope.find( 'textarea.irf-wysiwyg' ).each( function () {
			var id = this.id;
			if ( ! id ) { id = this.id = 'irf-wys-' + nextUid(); }
			try { wp.editor.remove( id ); } catch ( e ) {}
			wp.editor.initialize( id, {
				tinymce: { wpautop: true, toolbar1: 'bold italic bullist numlist link' },
				quicktags: true,
				mediaButtons: false
			} );
		} );
	}

	function initMedia( $scope ) {
		$scope.find( '.irf-media' ).each( function () {
			var $m = $( this );
			if ( $m.data( 'irf-media-bound' ) ) { return; }
			$m.data( 'irf-media-bound', true );

			$m.on( 'click', '.irf-media-pick', function ( e ) {
				e.preventDefault();
				var isImg = $m.data( 'media-type' ) === 'image';
				var frame = wp.media( {
					title: isImg ? 'Kies een afbeelding' : 'Kies een bestand',
					library: isImg ? { type: 'image' } : {},
					multiple: false
				} );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					$m.find( '.irf-media-id' ).val( a.id );
					var prev = isImg
						? '<img class="irf-media-img" src="' + ( a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url ) + '" alt="">'
						: '<span class="irf-media-name">' + a.filename + '</span>';
					$m.find( '.irf-media-preview' ).html( prev ).prop( 'hidden', false );
					$m.find( '.irf-media-clear' ).prop( 'hidden', false );
				} );
				frame.open();
			} );

			$m.on( 'click', '.irf-media-clear', function ( e ) {
				e.preventDefault();
				$m.find( '.irf-media-id' ).val( '' );
				$m.find( '.irf-media-preview' ).empty().prop( 'hidden', true );
				$( this ).prop( 'hidden', true );
			} );
		} );
	}

	function makeSortable( $list ) {
		if ( ! $list.length || $list.data( 'irf-sortable' ) ) { return; }
		$list.data( 'irf-sortable', true );
		$list.sortable( { handle: '.irf-row-handle', axis: 'y', tolerance: 'pointer' } );
	}

	$( function () {
		var $doc = $( document );

		$( '.irf-repeater-rows, .irf-flex-rows' ).each( function () { makeSortable( $( this ) ); } );
		initMedia( $( '.irf-metabox' ) );
		initWysiwyg( $( '.irf-metabox' ) );

		// Repeater: rij toevoegen
		$doc.on( 'click', '.irf-repeater-add', function () {
			var $rep = $( this ).closest( '.irf-repeater' );
			var tpl = $rep.children( '.irf-repeater-tpl' ).html();
			if ( ! tpl ) { return; }
			var $row = $( materialize( tpl ) );
			$rep.children( '.irf-repeater-rows' ).append( $row );
			initMedia( $row );
			initWysiwyg( $row );
		} );

		// Flexible: blok toevoegen
		$doc.on( 'click', '.irf-flex-add', function () {
			var $flex = $( this ).closest( '.irf-flex' );
			var layout = $( this ).data( 'layout' );
			var tpl = $flex.children( '.irf-flex-tpl[data-layout="' + layout + '"]' ).html();
			if ( ! tpl ) { return; }
			var $row = $( materialize( tpl ) );
			$flex.children( '.irf-flex-rows' ).append( $row );
			initMedia( $row );
			initWysiwyg( $row );
		} );

		// Rij verwijderen
		$doc.on( 'click', '.irf-row-remove', function () {
			if ( ! window.confirm( 'Deze rij verwijderen?' ) ) { return; }
			$( this ).closest( '.irf-row' ).remove();
		} );
	} );
} )( jQuery );
