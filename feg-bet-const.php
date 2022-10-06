<?php

if ( ! defined( 'FEG_BET__GAME_TABLE' ) ) {
	define( 'FEG_BET__GAME_TABLE', 'feg_bet_game' );
}

if ( ! defined( 'FEG_BET__SLIP_TABLE' ) ) {
	define( 'FEG_BET__SLIP_TABLE', 'feg_bet_slip' );
}

if ( ! defined( 'FEG_BET__BRIDGE_TABLE' ) ) {
	define( 'FEG_BET__BRIDGE_TABLE', 'feg_bet_connect' );
}

if ( ! defined( 'FEG_BET__WALLET_TABLE' ) ) {
	define( 'FEG_BET__WALLET_TABLE', 'feg_bet_wallet' );
}

if ( ! defined( 'FEG_BET__NUMBER_OF_TEAMS' ) ) {
	define( 'FEG_BET__NUMBER_OF_TEAMS', 20 );
}

if ( ! defined( 'FEG_BET__DEFAULT_WALLET_AMOUNT' ) ) {
	define( 'FEG_BET__DEFAULT_WALLET_AMOUNT', 120 );
}

if ( ! defined( 'FEG_BET__DUMMY_ODDS' ) ) {
	define( 'FEG_BET__DUMMY_ODDS',
		array(
			array( 1.30, 2, 3.50 ),
			array( 3, 3.50, 2.40 ),
			array( 2.50, 3, 2.50 ),
			array( 1.20, 3.80, 4.50 ),
			array( 2.20, 3.10, 2.70 ),
			array( 1.10, 5.00, 10.00 )
		)
	);
}

if ( ! defined( 'FEG_BET__ODDS_MAP' ) ) {
	define( 'FEG_BET__ODDS_MAP',
		array(
			1 => 'home_win',
			0 => 'draw',
			2 => 'away_win'
		)
	);
}
