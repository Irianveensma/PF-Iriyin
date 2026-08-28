<footer class="ipb-footer">
	<div class="ipb-footer-inner">
		<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<a href="#top" class="ipb-footer-top"><?php echo esc_html( irian_str( 'to_top' ) ); ?> &uarr;</a>
	</div>
</footer>

<div id="ipb-cmdk" class="ipb-cmdk" hidden role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( irian_str( 'palette_aria' ) ); ?>">
	<div class="ipb-cmdk-backdrop" data-ipb-cmdk-close></div>
	<div class="ipb-cmdk-panel" role="document">
		<input type="text" class="ipb-cmdk-input" placeholder="<?php echo esc_attr( irian_str( 'palette_placeholder' ) ); ?>" aria-label="<?php echo esc_attr( irian_str( 'palette_search_aria' ) ); ?>" autocomplete="off" spellcheck="false">
		<ul class="ipb-cmdk-list" role="listbox"></ul>
		<div class="ipb-cmdk-empty" hidden><?php echo esc_html( irian_str( 'palette_empty' ) ); ?></div>
		<div class="ipb-cmdk-foot">
			<span><kbd>&uarr;</kbd><kbd>&darr;</kbd> <?php echo esc_html( irian_str( 'palette_nav' ) ); ?></span>
			<span><kbd>&crarr;</kbd> <?php echo esc_html( irian_str( 'palette_open' ) ); ?></span>
			<span><kbd>esc</kbd> <?php echo esc_html( irian_str( 'palette_close' ) ); ?></span>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
