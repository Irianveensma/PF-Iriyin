/* ---------------------------------------------------------
   Irian Portfolio - front-end interacties
   - ⌘K / Ctrl+K command palette
   - verborgen console-bericht
--------------------------------------------------------- */
( function () {
	'use strict';

	var I18N = window.irianI18n || {};

	/* ---------- Verborgen console-bericht ---------- */
	try {
		console.log(
			'%c' + ( I18N.consoleHi || 'Aangenaam, developer' ) + ' 👋',
			'font-family: monospace; font-size: 14px; color: #9a9da3;'
		);
		console.log(
			'%c' + ( I18N.consoleSub || 'Je bent hier niet per ongeluk. Stuur een berichtje via het formulier onderaan.' ),
			'font-family: monospace; font-size: 12px; color: #5c5d61;'
		);
	} catch ( e ) {}

	/* ---------- Stack: klikbare skill-tags met visueel beeld + uitleg ---------- */
	( function initStack() {
		var row = document.querySelector( '.ipb-stack-row' );
		var panelsWrap = document.querySelector( '.ipb-stack-panels' );
		if ( ! row || ! panelsWrap ) { return; }

		var buttons = Array.prototype.slice.call( row.querySelectorAll( '.ipb-stack-tag--btn' ) );
		var panels = Array.prototype.slice.call( panelsWrap.querySelectorAll( '.ipb-stack-panel' ) );

		function panelFor( target ) {
			return panels.filter( function ( p ) { return p.getAttribute( 'data-panel' ) === target; } )[ 0 ];
		}

		function close() {
			buttons.forEach( function ( b ) { b.setAttribute( 'aria-expanded', 'false' ); } );
			panels.forEach( function ( p ) { p.hidden = true; } );
			panelsWrap.classList.remove( 'is-open' );
		}

		function open( btn ) {
			var target = btn.getAttribute( 'data-target' );
			buttons.forEach( function ( b ) { b.setAttribute( 'aria-expanded', String( b === btn ) ); } );
			panels.forEach( function ( p ) { p.hidden = p.getAttribute( 'data-panel' ) !== target; } );
			panelsWrap.classList.add( 'is-open' );
		}

		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				if ( btn.getAttribute( 'aria-expanded' ) === 'true' ) { close(); }
				else { open( btn ); }
			} );
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && panelsWrap.classList.contains( 'is-open' ) ) { close(); }
		} );
	} )();

	/* ---------- Modules: klikbare tegels met demo / code / beeld ---------- */
	( function initModules() {
		var grid = document.querySelector( '.ipb-lab-grid' );
		var detail = document.querySelector( '.ipb-modules-detail' );
		if ( ! grid || ! detail ) { return; }

		var tiles = Array.prototype.slice.call( grid.querySelectorAll( '.ipb-lab-tile--btn' ) );
		var panels = Array.prototype.slice.call( detail.querySelectorAll( '.ipb-module-panel' ) );

		function close() {
			tiles.forEach( function ( t ) { t.setAttribute( 'aria-expanded', 'false' ); } );
			panels.forEach( function ( p ) { p.hidden = true; } );
			detail.classList.remove( 'is-open' );
		}
		function open( tile ) {
			var target = tile.getAttribute( 'data-target' );
			tiles.forEach( function ( t ) { t.setAttribute( 'aria-expanded', String( t === tile ) ); } );
			panels.forEach( function ( p ) { p.hidden = p.getAttribute( 'data-panel' ) !== target; } );
			detail.classList.add( 'is-open' );
			var shown = panels.filter( function ( p ) { return ! p.hidden; } )[ 0 ];
			if ( shown ) {
				initCursorDemo( shown );
			}
		}

		tiles.forEach( function ( tile ) {
			tile.addEventListener( 'click', function () {
				if ( tile.getAttribute( 'aria-expanded' ) === 'true' ) { close(); }
				else { open( tile ); }
			} );
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && detail.classList.contains( 'is-open' ) ) { close(); }
		} );

		// Deeplink: ?module=<index> of #module-<index> opent dat paneel.
		( function deeplink() {
			var m = new URLSearchParams( window.location.search ).get( 'module' );
			if ( ! m && /^#module-(\d+)$/.test( window.location.hash ) ) {
				m = window.location.hash.replace( '#module-', '' );
			}
			if ( m === null || m === '' ) { return; }
			var tile = tiles.filter( function ( t ) { return t.getAttribute( 'data-target' ) === String( m ); } )[ 0 ];
			if ( tile ) {
				open( tile );
				requestAnimationFrame( function () { tile.scrollIntoView( { block: 'center' } ); } );
			}
		} )();

		/* --- Custom-cursor demo --- */
		function initCursorDemo( scope ) {
			var box = scope.querySelector( '.ipb-demo--cursor' );
			if ( ! box || box.dataset.bound ) { return; }
			box.dataset.bound = '1';
			var ring = box.querySelector( '.ipb-cursor-ring' );
			var dot = box.querySelector( '.ipb-cursor-dot' );
			var target = box.querySelector( '.ipb-cursor-target' );
			var mx = 0, my = 0, rx = 0, ry = 0, inside = false, raf = null;

			box.addEventListener( 'pointerenter', function () { inside = true; box.classList.add( 'is-live' ); tick(); } );
			box.addEventListener( 'pointerleave', function () { inside = false; box.classList.remove( 'is-live' ); } );
			box.addEventListener( 'pointermove', function ( e ) {
				var r = box.getBoundingClientRect();
				mx = e.clientX - r.left;
				my = e.clientY - r.top;
				dot.style.transform = 'translate(' + mx + 'px,' + my + 'px)';
			} );
			if ( target ) {
				target.addEventListener( 'pointerenter', function () { ring.classList.add( 'is-grown' ); } );
				target.addEventListener( 'pointerleave', function () { ring.classList.remove( 'is-grown' ); } );
			}
			function tick() {
				rx += ( mx - rx ) * 0.16;
				ry += ( my - ry ) * 0.16;
				ring.style.transform = 'translate(' + rx + 'px,' + ry + 'px)';
				if ( inside ) { raf = requestAnimationFrame( tick ); }
			}
		}

	} )();

	/* ---------- FAQ: vloeiend open/dicht in plaats van de harde <details>-snap ---------- */
	( function initFaqAccordion() {
		var items = Array.prototype.slice.call( document.querySelectorAll( '.ipb-faq-item' ) );
		if ( ! items.length ) { return; }
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }
		if ( ! items[ 0 ].animate ) { return; } // geen Web Animations API -> native <details>-gedrag laten staan.

		items.forEach( function ( item ) {
			var summary = item.querySelector( 'summary' );
			var animation = null;
			var isClosing = false;
			var isExpanding = false;

			summary.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				item.style.overflow = 'hidden';
				if ( isClosing || ! item.open ) {
					openItem();
				} else if ( isExpanding || item.open ) {
					shrink();
				}
			} );

			function openItem() {
				item.style.height = item.offsetHeight + 'px';
				item.open = true;
				requestAnimationFrame( expand );
			}

			function expand() {
				isExpanding = true;
				var startHeight = item.offsetHeight + 'px';
				var endHeight = summary.offsetHeight + item.querySelector( '.ipb-faq-answer' ).offsetHeight + 'px';
				runAnimation( startHeight, endHeight, true );
			}

			function shrink() {
				isClosing = true;
				var startHeight = item.offsetHeight + 'px';
				var endHeight = summary.offsetHeight + 'px';
				runAnimation( startHeight, endHeight, false );
			}

			function runAnimation( startHeight, endHeight, opening ) {
				if ( animation ) { animation.cancel(); }
				animation = item.animate(
					{ height: [ startHeight, endHeight ] },
					{ duration: 240, easing: 'ease-out' }
				);
				animation.onfinish = function () { onFinish( opening ); };
				animation.oncancel = function () { isClosing = false; isExpanding = false; };
			}

			function onFinish( opening ) {
				item.open = opening;
				animation = null;
				isClosing = false;
				isExpanding = false;
				item.style.height = '';
				item.style.overflow = '';
			}
		} );
	} )();

	/* ---------- Command palette ---------- */
	var root = document.getElementById( 'ipb-cmdk' );
	if ( ! root ) { return; }

	var input = root.querySelector( '.ipb-cmdk-input' );
	var list = root.querySelector( '.ipb-cmdk-list' );
	var empty = root.querySelector( '.ipb-cmdk-empty' );

	var goTo = I18N.goTo || 'Ga naar %s';
	var sec = I18N.hintSection || 'sectie';
	var ext = I18N.hintExternal || 'extern';
	function go( name ) { return goTo.replace( '%s', name ); }

	var actions = [
		{ label: go( I18N.navWork || 'Work' ), hint: sec, run: function () { scrollToId( 'work' ); } },
		{ label: go( I18N.navPlatforms || 'Platforms' ), hint: sec, run: function () { scrollToId( 'platforms' ); } },
		{ label: go( I18N.navModules || 'Modules' ), hint: sec, run: function () { scrollToId( 'lab' ); } },
		{ label: go( I18N.navFaq || 'FAQ' ), hint: sec, run: function () { scrollToId( 'faq' ); } },
		{ label: go( I18N.navContact || 'Contact' ), hint: sec, run: function () { scrollToId( 'contact' ); } },
		{ label: I18N.top || 'Naar boven', hint: sec, run: function () { window.scrollTo( { top: 0, behavior: 'smooth' } ); } },
		{ label: 'pedicure-paulina.nl', hint: ext, run: function () { openExt( 'https://pedicure-paulina.nl' ); } },
		{ label: 'sitadesign.nl', hint: ext, run: function () { openExt( 'https://sitadesign.nl' ); } }
	];

	var filtered = actions.slice();
	var selected = 0;

	function scrollToId( id ) {
		var el = document.getElementById( id );
		if ( el ) { el.scrollIntoView( { behavior: 'smooth', block: 'start' } ); }
	}
	function openExt( url ) { window.open( url, '_blank', 'noopener' ); }

	function isOpen() { return ! root.hasAttribute( 'hidden' ); }

	function open() {
		root.removeAttribute( 'hidden' );
		input.value = '';
		filter( '' );
		document.documentElement.style.overflow = 'hidden';
		setTimeout( function () { input.focus(); }, 0 );
	}

	function close() {
		root.setAttribute( 'hidden', '' );
		document.documentElement.style.overflow = '';
	}

	function render() {
		list.innerHTML = '';
		empty.hidden = filtered.length > 0;
		filtered.forEach( function ( action, i ) {
			var li = document.createElement( 'li' );
			li.className = 'ipb-cmdk-item';
			li.setAttribute( 'role', 'option' );
			li.setAttribute( 'aria-selected', i === selected ? 'true' : 'false' );
			li.innerHTML =
				'<span class="ipb-cmdk-item-label"></span>' +
				'<span class="ipb-cmdk-item-hint"></span>';
			li.querySelector( '.ipb-cmdk-item-label' ).textContent = action.label;
			li.querySelector( '.ipb-cmdk-item-hint' ).textContent = action.hint || '';
			li.addEventListener( 'mouseenter', function () { selected = i; updateSelection(); } );
			li.addEventListener( 'click', function () { activate(); } );
			list.appendChild( li );
		} );
	}

	function updateSelection() {
		Array.prototype.forEach.call( list.children, function ( li, i ) {
			li.setAttribute( 'aria-selected', i === selected ? 'true' : 'false' );
		} );
		var active = list.children[ selected ];
		if ( active ) { active.scrollIntoView( { block: 'nearest' } ); }
	}

	function filter( q ) {
		q = q.trim().toLowerCase();
		filtered = q
			? actions.filter( function ( a ) {
				return ( a.label + ' ' + ( a.hint || '' ) ).toLowerCase().indexOf( q ) !== -1;
			} )
			: actions.slice();
		selected = 0;
		render();
	}

	function activate() {
		var action = filtered[ selected ];
		if ( action ) { close(); action.run(); }
	}

	document.addEventListener( 'keydown', function ( e ) {
		var meta = e.metaKey || e.ctrlKey;
		if ( meta && ( e.key === 'k' || e.key === 'K' ) ) {
			e.preventDefault();
			isOpen() ? close() : open();
			return;
		}
		if ( ! isOpen() ) { return; }
		if ( e.key === 'Escape' ) { e.preventDefault(); close(); }
		else if ( e.key === 'ArrowDown' ) { e.preventDefault(); selected = Math.min( selected + 1, filtered.length - 1 ); updateSelection(); }
		else if ( e.key === 'ArrowUp' ) { e.preventDefault(); selected = Math.max( selected - 1, 0 ); updateSelection(); }
		else if ( e.key === 'Enter' ) { e.preventDefault(); activate(); }
	} );

	input.addEventListener( 'input', function () { filter( input.value ); } );

	root.querySelectorAll( '[data-ipb-cmdk-close]' ).forEach( function ( el ) {
		el.addEventListener( 'click', close );
	} );

	document.querySelectorAll( '[data-ipb-cmdk-open]' ).forEach( function ( el ) {
		el.addEventListener( 'click', function ( e ) { e.preventDefault(); open(); } );
	} );
} )();
