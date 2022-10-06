<?php

class Feg_Bet_GAME {

	public string $home_team;

	public string $away_team;

	public array $odds;

	public function __construct( $home_team, $away_team ) {

		$this->home_team = $home_team;
		$this->away_team = $away_team;
		$this->odds      = FEG_BET__DUMMY_ODDS[ array_rand( FEG_BET__DUMMY_ODDS, 1 ) ];

	}

}
