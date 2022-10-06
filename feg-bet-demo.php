<?php
/**
 * Plugin Name: feg-bet-demo
 * Plugin URI:
 * Description: Demo bet plugin
 * Version: 0.1
 * Author:
 * Author URI:
 **/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

if ( ! defined( 'FEG_BET__PLUGIN_DIR' ) ) {
	define( 'FEG_BET__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

date_default_timezone_set('Europe/Zagreb');

require_once( FEG_BET__PLUGIN_DIR . 'feg-bet-const.php' );
require_once( FEG_BET__PLUGIN_DIR . 'feg-bet-functions.php' );
require_once( FEG_BET__PLUGIN_DIR . 'feg-bet-shortcodes.php' );

require_once( FEG_BET__PLUGIN_DIR . 'class.feg-bet-offer.php' );
require_once( FEG_BET__PLUGIN_DIR . 'class.feg-bet-offer-dashboard.php' );
require_once( FEG_BET__PLUGIN_DIR . 'class.feg-bet-slips-dashboard.php' );
require_once( FEG_BET__PLUGIN_DIR . 'class.feg-bet-game.php' );

if ( ! class_exists( 'Feg_Bet_Demo' ) ) {

	class Feg_Bet_DEMO {

		private function __construct() {}

		/**
		 * On init: create tables if necessary
		 * Setup scripts (.js & .css)
		 *
		 * @return void
		 */
		public static function init_actions() {

			self::maybe_create_tables();

			add_action( 'wp_head', array( __CLASS__, 'scripts_setup' ), 1 );

		}

		private static function maybe_create_tables() {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();

			$tablename_game = FEG_BET__GAME_TABLE;
			$sql            = "CREATE TABLE $tablename_game (
  game_id mediumint(9) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  home_team tinytext NOT NULL,
  away_team tinytext NOT NULL,
  home_win float NOT NULL,
  draw float NOT NULL,
  away_win float NOT NULL,
  PRIMARY KEY  (game_id)
) $charset_collate;";

			maybe_create_table( $tablename_game, $sql );

			$tablename_slip = FEG_BET__SLIP_TABLE;
			$sql            = "CREATE TABLE $tablename_slip (
  slip_id mediumint(9) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  bet_amount float NOT NULL,
  bet_win float NOT NULL,
  PRIMARY KEY  (slip_id)
) $charset_collate;";

			maybe_create_table( $tablename_slip, $sql );

			$tablename_connect = FEG_BET__BRIDGE_TABLE;
			$sql               = "CREATE TABLE $tablename_connect (
  slip_id mediumint(9) NOT NULL,
  game_id mediumint(9) NOT NULL,
  bet_tip int NOT NULL,
  FOREIGN KEY  (slip_id) REFERENCES $tablename_slip(slip_id),
  FOREIGN KEY  (game_id) REFERENCES $tablename_game(game_id)
) $charset_collate;";

			maybe_create_table( $tablename_connect, $sql );

			$tablename_wallet = FEG_BET__WALLET_TABLE;
			$sql            = "CREATE TABLE $tablename_wallet (
    wallet_id mediumint(9) NOT NULL AUTO_INCREMENT,
  wallet_amount float NOT NULL,
  PRIMARY KEY  (wallet_id)
) $charset_collate;";

			maybe_create_table( $tablename_wallet, $sql );

		}

		public static function scripts_setup() {

			wp_enqueue_script(
				'feg-jquery',
				'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js',
				null,
				null,
				true
			);

			wp_enqueue_script(
				'feg-fomantic',
				'https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.8/semantic.min.js',
				null,
				null,
				true
			);

			wp_register_script(
				'feg-offer',
				plugins_url( '/assets/js/feg-offer.js', __FILE__ ),
				null,
				null,
				true
			);

			wp_localize_script(
				'feg-offer',
				'feg_offer_vars',
				array(
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'bet-ajax-nonce' )
				)
			);

			wp_enqueue_script( 'feg-offer' );

			wp_enqueue_style(
				'feg-fomantic-css',
				'https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.8/semantic.min.css'
			);

			wp_register_style('feg-custom-css',
				plugins_url( '/assets/css/feg.css', __FILE__ )
			);

			wp_enqueue_style('feg-custom-css');

		}

	}

	add_action( 'plugins_loaded', array( 'Feg_Bet_DEMO', 'init_actions' ) );

}




