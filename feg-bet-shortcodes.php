<?php

/**
 * Add shortcode [feg_offer] to be placed inside page content to display "Offer dashboard"
 *
 * @return string
 */
function feg_demo_offer_shortcode() {

	$dashboard = new feg_bet_OFFER_DASHBOARD;

	return $dashboard->get_html();

}

add_shortcode( 'feg_offer', 'feg_demo_offer_shortcode' );

/**
 * Add shortcode [feg_slips_list] to be placed inside page content to display "List of betting slips"
 *
 * @return string
 */
function feg_demo_slips_shortcode() {

	$dashboard = new feg_bet_SLIPS_DASHBOARD;

	return $dashboard->get_html();

}

add_shortcode( 'feg_slips_list', 'feg_demo_slips_shortcode' );
