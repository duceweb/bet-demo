<?php

/**
 * Betting slips list
 */
class feg_bet_SLIPS_DASHBOARD {

	protected wpdb $glob_wpdb;

	protected string $html;

	protected array $games;

	public function __construct() {

		global $wpdb;

		$this->games     = array();
		$this->glob_wpdb =& $wpdb;

		$this->prepare_games();
		$this->html = '';


	}

	/**
	 * Prepare games
	 *
	 * @return void|null
	 */
	protected function prepare_games() {

		$table_name_games = FEG_BET__GAME_TABLE;

		$wpdb_games = $this->glob_wpdb->get_results(
			"SELECT * FROM $table_name_games"
		);

		if ( empty( $wpdb_games ) ) {
			return null;
		}

		foreach ( $wpdb_games as $db_game ) {
			$this->games[ $db_game->game_id ] = array(
				'home_team' => $db_game->home_team,
				'away_team' => $db_game->away_team,
				'1'         => $db_game->home_win,
				'0'         => $db_game->draw,
				'2'         => $db_game->away_win,
			);
		}

	}

	/**
	 * @return string
	 */
	public function get_html() {

		if ( ! $this->games ) {
			return $this->html;
		}

		$table_name_slips = FEG_BET__SLIP_TABLE;
		$wpdb_slips       = $this->glob_wpdb->get_results(
			"SELECT * FROM $table_name_slips ORDER BY `time` DESC"
		);

		if ( empty( $wpdb_slips ) ) {
			return $this->html;
		}

		foreach ( $wpdb_slips as $betting_slip ) {

			$table_name_bridge = FEG_BET__BRIDGE_TABLE;
			$wpdb_connections  = $this->glob_wpdb->get_results(
				"SELECT * FROM $table_name_bridge WHERE slip_id=$betting_slip->slip_id"
			);

			$this->html .= '
					<div class="ui raised segments bet-slip">
					  <div class="ui top attached segment bet-slip-top">
					    <div class="time">' . $betting_slip->time . '</div>
					    <span>Win: ' . number_format( (float) $betting_slip->bet_win, 2, '.', '' ) . '€</span>
					    <span>Bet: ' . number_format( (float) $betting_slip->bet_amount, 2, '.', '' ) . '€</span>
					  </div>
					  <div class="ui attached secondary segment bet-slip-games">';

			foreach ( $wpdb_connections as $connection ) {

				$bet_tip = $connection->bet_tip === '0' ? 'X' : $connection->bet_tip;

				$this->html .= '<div class="game">' . $this->games[ $connection->game_id ]['home_team'] . ' - ' . $this->games[ $connection->game_id ]['away_team'] . ' 
									<span>' . number_format( (float) $this->games[ $connection->game_id ][ $connection->bet_tip ], 2, '.', '' ) . '</span>
									<span>' . $bet_tip . '</span>
								</div>';
			}

			$this->html .= '</div>
						</div>';
		}

		return $this->html;

	}
}
