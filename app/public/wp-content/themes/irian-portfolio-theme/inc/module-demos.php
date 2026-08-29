<?php
/**
 * Irian Portfolio - ingebouwde "live demo"-blokken voor de Modules-sectie.
 *
 * irian_module_demo( 'cursor' ) geeft de HTML terug voor een demo. De bijbehorende
 * interactie zit in assets/site.js (initModules / de losse demo-handlers).
 *
 * @package IrianPortfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function irian_module_demo( $slug ) {
	switch ( $slug ) {

		case 'palette':
			return '<div class="ipb-demo ipb-demo--palette">'
				. '<button type="button" class="ipb-btn ipb-btn-primary" data-ipb-cmdk-open>' . esc_html( irian_str( 'demo_palette_btn' ) ) . '</button>'
				. '<p class="ipb-demo-hint"><kbd>&#8984;</kbd><kbd>K</kbd> / <kbd>Ctrl</kbd><kbd>K</kbd>. ' . esc_html( irian_str( 'demo_palette_hint' ) ) . '</p>'
				. '</div>';

		case 'cursor':
			return '<div class="ipb-demo ipb-demo--cursor" aria-hidden="true">'
				. '<div class="ipb-cursor-ring"></div>'
				. '<div class="ipb-cursor-dot"></div>'
				. '<span class="ipb-demo-hint">' . esc_html( irian_str( 'demo_cursor_move' ) ) . '</span>'
				. '<button type="button" class="ipb-cursor-target">' . esc_html( irian_str( 'demo_cursor_target' ) ) . '</button>'
				. '</div>';

		case 'seo-report':
			$rows = array(
				array( 'ok', irian_str( 'seo_r1_l' ), irian_str( 'seo_r1_d' ) ),
				array( 'ok', irian_str( 'seo_r2_l' ), irian_str( 'seo_r2_d' ) ),
				array( 'warn', irian_str( 'seo_r3_l' ), irian_str( 'seo_r3_d' ) ),
				array( 'ok', irian_str( 'seo_r4_l' ), irian_str( 'seo_r4_d' ) ),
				array( 'fail', irian_str( 'seo_r5_l' ), irian_str( 'seo_r5_d' ) ),
				array( 'ok', irian_str( 'seo_r6_l' ), irian_str( 'seo_r6_d' ) ),
				array( 'warn', irian_str( 'seo_r7_l' ), irian_str( 'seo_r7_d' ) ),
			);
			$html = '<div class="ipb-demo ipb-demo--report">';
			$html .= '<div class="ipb-report-score"><strong>72</strong><span>/ 100</span></div>';
			$html .= '<ul class="ipb-report-list">';
			foreach ( $rows as $r ) {
				list( $state, $label, $detail ) = $r;
				$mark = array( 'ok' => '✓', 'warn' => '!', 'fail' => '✕' )[ $state ];
				$html .= sprintf(
					'<li class="ipb-report-row is-%s"><span class="ipb-report-mark">%s</span><span class="ipb-report-label">%s</span><span class="ipb-report-detail">%s</span></li>',
					esc_attr( $state ),
					esc_html( $mark ),
					esc_html( $label ),
					esc_html( $detail )
				);
			}
			$html .= '</ul><p class="ipb-demo-hint">' . esc_html( irian_str( 'demo_report_hint' ) ) . '</p></div>';
			return $html;
	}

	return '';
}
