<?php

/**
 * Set wallet amount
 *
 * @param $wallet_amount
 *
 * @return void
 */
function feg_bet_set_wallet_amount( $wallet_amount = null ) {

	global $wpdb;

	$new_amount = $wallet_amount ? $wallet_amount : FEG_BET__DEFAULT_WALLET_AMOUNT;

	$data = array(
		'wallet_id'     => 1,
		'wallet_amount' => $new_amount
	);

	$format = array(
		'%d',
		'%f'
	);
	$wpdb->insert( FEG_BET__WALLET_TABLE, $data, $format );
}

/**
 * Update wallet amount
 *
 * @param $wallet_amount
 *
 * @return void
 */
function feg_bet_update_wallet_amount( $wallet_amount ) {

	global $wpdb;

	$data  = array( 'wallet_amount' => $wallet_amount );
	$where = array( 'wallet_id' => 1 );

	$wpdb->update( FEG_BET__WALLET_TABLE, $data, $where );
}

/**
 * Reload wallet amount, ajax
 *
 * @return void
 */
function feg_bet__ajax_set_wallet_amount() {

	if ( ! wp_verify_nonce( $_POST['nonce'], 'bet-ajax-nonce' ) ) {
		die();
	}

	$wallet_amount = FEG_BET__DEFAULT_WALLET_AMOUNT;

	feg_bet_update_wallet_amount( $wallet_amount );

	echo json_encode(
		array(
			'wallet_amount' => $wallet_amount
		)
	);

	die();
}

add_action( 'wp_ajax_feg_bet__ajax_set_wallet_amount', 'feg_bet__ajax_set_wallet_amount' );
add_action( 'wp_ajax_nopriv_feg_bet__ajax_set_wallet_amount', 'feg_bet__ajax_set_wallet_amount' );

/**
 * Get wallet amount
 *
 * @return mixed
 */
function feg_bet_get_wallet_amount() {

	global $wpdb;

	$table_name  = FEG_BET__WALLET_TABLE;

	$wpdb_result  = $wpdb->get_results(
		"SELECT * FROM $table_name WHERE wallet_id=1"
	);

	return $wpdb_result[0]->wallet_amount;
}

/**
 * Add betting slip to db
 *
 * @param $slip_data
 * @param $amount
 *
 * @return array
 */
function feg_bet__add_bet_slip( $slip_data, $amount ) {

	$estimated_return = feg_bet__get_estimated_return( $slip_data, $amount );

	if ( $estimated_return['allow_bet'] === 0 ) {
		return array(
			'status'        => 'error',
			'message'       => 'Bet Not Allowed',
			'wallet_amount' => feg_bet_get_wallet_amount()
		);
	}

	global $wpdb;

	$data   = array(
		'time'       => date( "Y-m-d H:i:s" ),
		'bet_amount' => $amount,
		'bet_win'    => $estimated_return['estimated_return']
	);

	$format = array(
		'%s',
		'%f',
		'%f',
	);

	$wpdb->insert( FEG_BET__SLIP_TABLE, $data, $format );

	$slip_id = $wpdb->insert_id;

	$wpdb_result = feg_bet__get_slip_information( $slip_data );

	foreach ( $wpdb_result as $db_game ) {

		$slip_data_key = array_search( $db_game->game_id, array_column( $slip_data, 'game' ) );

		$data   = array(
			'slip_id' => $slip_id,
			'game_id' => $db_game->game_id,
			'bet_tip' => $slip_data[ $slip_data_key ]['tip']
		);

		$format = array(
			'%d',
			'%d',
			'%s',
		);

		$wpdb->insert( FEG_BET__BRIDGE_TABLE, $data, $format );
	}

	$wallet_amount = (float) feg_bet_get_wallet_amount();

	$new_amount    = $wallet_amount - $amount;

	feg_bet_update_wallet_amount( $new_amount );

	return array(
		'status'        => 'success',
		'message'       => 'Bet Slip Accepted',
		'wallet_amount' => feg_bet_get_wallet_amount()
	);
}

/**
 * Add betting slip to db, ajax
 *
 * @return void
 */
function feg_bet__ajax_add_bet_slip() {

	if ( ! wp_verify_nonce( $_POST['nonce'], 'bet-ajax-nonce' ) ) {
		die();
	}

	echo json_encode(
		feg_bet__add_bet_slip(
			array_key_exists( 'slip_data', $_POST ) ? $_POST["slip_data"] : array(),
			$_POST["amount"]
		)
	);

	die();
}

add_action( 'wp_ajax_feg_bet__ajax_add_bet_slip', 'feg_bet__ajax_add_bet_slip' );
add_action( 'wp_ajax_nopriv_feg_bet__ajax_add_bet_slip', 'feg_bet__ajax_add_bet_slip' );

/**
 * Get win amount based on betting amount and selected odds
 *
 * @param $slip_data
 * @param $amount
 *
 * @return array
 */
function feg_bet__get_estimated_return( $slip_data, $amount ) {

	$estimated_return = array(
		'estimated_return' => 0,
		'wallet_check'     => 0,
		'allow_bet'        => 0,
		'message'          => ''
	);

	if ( empty( $slip_data ) || $amount < 0.1 ) {

		return $estimated_return;
	}

	$estimated_return['wallet_check'] = (float) feg_bet_get_wallet_amount() < $amount ? 0 : 1;
	if ( ! $estimated_return['wallet_check'] ) {
		$estimated_return['message'] = 'Insufficient funds';

		return $estimated_return;
	}

	$estimated_return['estimated_return'] = $amount;
	$wpdb_result                          = feg_bet__get_slip_information( $slip_data );

	foreach ( $wpdb_result as $db_game ) {
		$estimated_return['estimated_return'] *= $db_game->selected_odds;
	}

	$estimated_return['allow_bet'] = 1;

	return $estimated_return;
}

/**
 * Get win amount based on betting amount and selected odds, ajax
 *
 * @return void
 */
function feg_bet__ajax_get_estimated_return() {

	if ( ! wp_verify_nonce( $_GET['nonce'], 'bet-ajax-nonce' ) ) {
		die();
	}

	echo json_encode(
		feg_bet__get_estimated_return(
			array_key_exists( 'slip_data', $_GET ) ? $_GET["slip_data"] : array(),
			$_GET["amount"]
		)
	);

	die();
}

add_action( 'wp_ajax_feg_bet__ajax_get_estimated_return', 'feg_bet__ajax_get_estimated_return' );
add_action( 'wp_ajax_nopriv_feg_bet__ajax_get_estimated_return', 'feg_bet__ajax_get_estimated_return' );

/**
 * Get data for selected games
 *
 * @param $slip_data
 *
 * @return array|object|stdClass[]|null
 */
function feg_bet__get_slip_information( $slip_data ) {

	if ( empty( $slip_data ) ) {
		return array();
	}

	global $wpdb;

	$game_ids     = array_column( $slip_data, 'game' );
	$game_ids_sql = implode( ',', array_map( 'intval', $game_ids ) );
	$table_name   = FEG_BET__GAME_TABLE;
	$game_id_key  = 'game_id';
	$wpdb_result  = $wpdb->get_results(
		"SELECT * FROM $table_name WHERE $game_id_key IN ($game_ids_sql)"
	);

	foreach ( $wpdb_result as &$db_game ) {

		$slip_data_key          = array_search( $db_game->game_id, array_column( $slip_data, 'game' ) );
		$db_game->selected_tip  = FEG_BET__ODDS_MAP[ $slip_data[ $slip_data_key ]['tip'] ];

		switch (FEG_BET__ODDS_MAP[ $slip_data[ $slip_data_key ]['tip'] ]) {
			case 'home_win':
				$db_game->selected_odds = $db_game->home_win;
				break;
			case 'draw':
				$db_game->selected_odds = $db_game->draw;
				break;
			case 'away_win':
				$db_game->selected_odds = $db_game->away_win;
				break;
		}
	}

	return $wpdb_result;
}

/**
 * Get data for selected games, ajax
 *
 * @return void
 */
function feg_bet__ajax_get_slip_information() {

	if ( ! wp_verify_nonce( $_GET['nonce'], 'bet-ajax-nonce' ) ) {
		die ();
	}

	echo json_encode(
		feg_bet__get_slip_information(
			array_key_exists( 'slip_data', $_GET ) ? $_GET["slip_data"] : array()
		)
	);

	die();
}

add_action( 'wp_ajax_feg_bet__ajax_get_slip_information', 'feg_bet__ajax_get_slip_information' );
add_action( 'wp_ajax_nopriv_feg_bet__ajax_get_slip_information', 'feg_bet__ajax_get_slip_information' );
