<?php

class Feg_Bet_OFFER {

	protected array $teams;

	protected array $games;

	public function __construct() {

		if ( ! $this->get_offer() ) {

			$this->set_up();

			feg_bet_set_wallet_amount();
		}

	}

	/**
	 * Generate dummy offer data and save to DB
	 *
	 * @return void
	 */
	protected function set_up() {

		// setup dummy teams
		for ( $i = 0; $i < FEG_BET__NUMBER_OF_TEAMS; $i ++ ) {
			$this->teams[] = 'Team ' . $i;
		}

		// setup random games from available teams
		for ( $i = 0; $i < FEG_BET__NUMBER_OF_TEAMS / 2; $i ++ ) {

			$home_team_key = array_rand( $this->teams, 1 );
			$home_team     = $this->teams[ $home_team_key ];
			unset( $this->teams[ $home_team_key ] );

			$away_team_key = array_rand( $this->teams, 1 );
			$away_team     = $this->teams[ $away_team_key ];
			unset( $this->teams[ $away_team_key ] );

			$this->games[ $i ] = new Feg_Bet_GAME( $home_team, $away_team );

		}

		$this->save_offer();

	}

	/**
	 * Save offer to DB
	 *
	 * @return void
	 */
	protected function save_offer() {

		global $wpdb;

		foreach ( $this->games as $game ) {
			$data   = array(
				'time'      => date( "Y-m-d H:i:s" ),
				'home_team' => $game->home_team,
				'away_team' => $game->away_team,
				'home_win'  => $game->odds[0],
				'draw'      => $game->odds[1],
				'away_win'  => $game->odds[2],
			);
			$format = array(
				'%s',
				'%s',
				'%s',
				'%f',
				'%f',
				'%f',
			);
			$wpdb->insert( FEG_BET__GAME_TABLE, $data, $format );
		}
	}

	/**
	 * Get complete offer data
	 *
	 * @return array|object|stdClass[]|null
	 */
	public function get_offer() {

		global $wpdb;

		$table_name = FEG_BET__GAME_TABLE;

		return $wpdb->get_results( "SELECT * FROM $table_name" );
	}
}
