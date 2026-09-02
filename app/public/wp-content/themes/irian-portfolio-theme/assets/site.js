/* ---------------------------------------------------------
   Irian Portfolio - front-end interacties
   - ⌘K / Ctrl+K command palette
   - verborgen console-bericht
--------------------------------------------------------- */
( function () {
	'use strict';

	var I18N = window.irianI18n || {};

	/* ---------- Toetshint: cmd-K alleen op Mac, elders Ctrl+K ---------- */
	( function fixKbdHint() {
		var isMac = /Mac|iP(hone|ad|od)/.test( navigator.platform || navigator.userAgent || '' );
		if ( isMac ) { return; }
		document.querySelectorAll( '.ipb-kbd-keys kbd' ).forEach( function ( kbd ) {
			if ( kbd.textContent.trim() === '⌘' ) {
				kbd.textContent = 'Ctrl';
				kbd.style.fontSize = '11px';
			}
		} );
	} )();

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

	/* ---------- Contactformulier: custom dropdown i.p.v. het native <select>-menu ---------- */
	( function initCustomSelect() {
		var selects = Array.prototype.slice.call( document.querySelectorAll( '.ipb-form-field select' ) );
		if ( ! selects.length ) { return; }

		selects.forEach( function ( select ) {
			// Een <label> met meerdere labelable descendants (de native select
			// EN straks onze eigen trigger-knop) forwardt een klik op andere
			// inhoud daarbinnen (de listbox-items, of het label-tekstje zelf)
			// automatisch ook naar de eerste labelable control. Dat opent het
			// menu meteen weer nadat selectOption() het net gesloten heeft.
			// Fix: het omringende <label> vervangen door een gewone <div>;
			// aria-labelledby hieronder herstelt de koppeling voor schermlezers.
			var field = select.closest( '.ipb-form-field' );
			if ( field && 'LABEL' === field.tagName ) {
				var div = document.createElement( 'div' );
				div.className = field.className;
				while ( field.firstChild ) { div.appendChild( field.firstChild ); }
				field.parentNode.replaceChild( div, field );
				field = div;
			}

			var wrap = document.createElement( 'div' );
			wrap.className = 'ipb-select';
			select.parentNode.insertBefore( wrap, select );

			var uid = 'ipb-select-' + Math.random().toString( 36 ).slice( 2, 8 );

			var trigger = document.createElement( 'button' );
			trigger.type = 'button';
			trigger.className = 'ipb-select-trigger';
			trigger.setAttribute( 'role', 'combobox' );
			trigger.setAttribute( 'aria-haspopup', 'listbox' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			trigger.setAttribute( 'aria-controls', uid );
			trigger.innerHTML =
				'<span class="ipb-select-value"></span>' +
				'<span class="ipb-select-chevron" aria-hidden="true">' +
				'<svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8l5 5 5-5"/></svg>' +
				'</span>';

			var list = document.createElement( 'ul' );
			list.className = 'ipb-select-list';
			list.setAttribute( 'role', 'listbox' );
			list.id = uid;
			list.hidden = true;

			var options = Array.prototype.slice.call( select.options ).map( function ( opt ) {
				var li = document.createElement( 'li' );
				li.className = 'ipb-select-option';
				li.setAttribute( 'role', 'option' );
				li.setAttribute( 'data-value', opt.value );
				li.tabIndex = -1;
				li.textContent = opt.textContent;
				if ( opt.selected ) { li.setAttribute( 'aria-selected', 'true' ); }
				list.appendChild( li );
				return li;
			} );

			wrap.appendChild( trigger );
			wrap.appendChild( list );
			wrap.appendChild( select );
			select.classList.add( 'ipb-select-native' );
			select.setAttribute( 'tabindex', '-1' );
			select.setAttribute( 'aria-hidden', 'true' );

			var valueEl = trigger.querySelector( '.ipb-select-value' );
			valueEl.textContent = ( select.options[ select.selectedIndex ] || {} ).textContent || '';

			// Herstel de label-koppeling die verloren ging toen het <label> een <div>
			// werd: klik op het label-tekstje opent de trigger, en aria-labelledby
			// zorgt dat schermlezers 'm nog steeds "Type project" noemen.
			var labelSpan = field ? field.querySelector( 'span' ) : null;
			if ( labelSpan ) {
				if ( ! labelSpan.id ) { labelSpan.id = uid + '-label'; }
				trigger.setAttribute( 'aria-labelledby', labelSpan.id );
				labelSpan.addEventListener( 'click', function () {
					if ( list.hidden ) { open(); } else { trigger.focus(); }
				} );
			}

			function close() {
				list.hidden = true;
				trigger.setAttribute( 'aria-expanded', 'false' );
				document.removeEventListener( 'click', onDocClick );
			}
			function open() {
				list.hidden = false;
				trigger.setAttribute( 'aria-expanded', 'true' );
				// setTimeout: als de klik die open() aanroept van buiten wrap komt
				// (bv. het label-tekstje), bubbelt diezelfde klik nog door naar
				// document. Een listener die je tijdens die bubbel toevoegt wordt
				// nog in dezelfde dispatch aangeroepen zodra de bubbel 'm bereikt -
				// dan sluit onDocClick het menu meteen weer. Een tick later
				// toevoegen laat 'm alleen op de VOLGENDE klik reageren.
				setTimeout( function () { document.addEventListener( 'click', onDocClick ); }, 0 );
				var active = options.filter( function ( o ) { return o.getAttribute( 'aria-selected' ) === 'true'; } )[ 0 ] || options[ 0 ];
				if ( active ) { active.focus(); }
			}
			function onDocClick( e ) {
				if ( ! wrap.contains( e.target ) ) { close(); }
			}
			function selectOption( li ) {
				options.forEach( function ( o ) { o.removeAttribute( 'aria-selected' ); } );
				li.setAttribute( 'aria-selected', 'true' );
				select.value = li.getAttribute( 'data-value' );
				valueEl.textContent = li.textContent;
				select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				close();
				trigger.focus();
			}

			trigger.addEventListener( 'click', function () {
				if ( list.hidden ) { open(); } else { close(); }
			} );
			trigger.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					if ( list.hidden ) { open(); }
				} else if ( e.key === 'Escape' ) {
					close();
				}
			} );

			options.forEach( function ( li ) {
				// preventDefault: zonder dit forwardt de omringende <label class="ipb-form-field">
				// de klik ook naar zijn eerste labelable descendant (de trigger-knop), die dan
				// meteen weer open() aanroept omdat de lijst net dicht is gegaan.
				li.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					selectOption( li );
				} );
			} );
			list.addEventListener( 'keydown', function ( e ) {
				var idx = options.indexOf( document.activeElement );
				if ( e.key === 'ArrowDown' ) {
					e.preventDefault();
					( options[ idx + 1 ] || options[ 0 ] ).focus();
				} else if ( e.key === 'ArrowUp' ) {
					e.preventDefault();
					( options[ idx - 1 ] || options[ options.length - 1 ] ).focus();
				} else if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					if ( idx !== -1 ) { selectOption( options[ idx ] ); }
				} else if ( e.key === 'Escape' ) {
					e.preventDefault();
					close();
					trigger.focus();
				} else if ( e.key === 'Tab' ) {
					close();
				}
			} );
		} );
	} )();

	/* ---------- Mobiele nav: hamburger klapt de sectielinks uit ---------- */
	( function initNavToggle() {
		var nav = document.querySelector( '.ipb-nav' );
		var toggle = nav && nav.querySelector( '.ipb-nav-toggle' );
		var links = nav && nav.querySelector( '.ipb-nav-links' );
		if ( ! nav || ! toggle || ! links ) { return; }

		function setOpen( open ) {
			nav.classList.toggle( 'ipb-nav--open', open );
			toggle.setAttribute( 'aria-expanded', String( open ) );
		}

		toggle.addEventListener( 'click', function () {
			setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		// Klik op een sectielink: de pagina scrollt weg, dus het paneel weer dicht.
		links.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( 'a' ) ) { setOpen( false ); }
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && nav.classList.contains( 'ipb-nav--open' ) ) {
				setOpen( false );
				toggle.focus();
			}
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( nav.classList.contains( 'ipb-nav--open' ) && ! nav.contains( e.target ) ) {
				setOpen( false );
			}
		} );

		// Terug naar een breed scherm: open-staat resetten zodat de desktoprij klopt.
		if ( window.matchMedia ) {
			var wide = window.matchMedia( '(min-width: 781px)' );
			var reset = function ( e ) { if ( e.matches ) { setOpen( false ); } };
			if ( wide.addEventListener ) { wide.addEventListener( 'change', reset ); }
			else if ( wide.addListener ) { wide.addListener( reset ); }
		}
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
