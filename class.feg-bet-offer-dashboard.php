<?php

class feg_bet_OFFER_DASHBOARD{

	protected Feg_Bet_OFFER $offer_data;

	protected string $html;

	public function __construct() {

		$this->offer_data = new Feg_Bet_OFFER;

	}

	/**
	 * @return string
	 */
	public function get_html() {

		$games_html = '';
		foreach ( $this->offer_data->get_offer() as $game ) {

			$games_html .= '<tr data-game="'.$game->game_id.'">';

			$games_html .= '<td>' . $game->home_team . ' - ' . $game->away_team . '</td>';

			$games_html .= '<td>
				<button class="ui button tip-button" data-tip="1">' . number_format( (float) $game->home_win, 2, '.', '' ) . '</button>
			</td>';

			$games_html .= '<td>
				<button class="ui button tip-button" data-tip="0">' . number_format( (float) $game->draw, 2, '.', '' ) . '</button>
			</td>';

			$games_html .= '<td>
				<button class="ui button tip-button" data-tip="2">' . number_format( (float) $game->away_win, 2, '.', '' ) . '</button>
			</td>';

			$games_html .= '</tr>';
		}

		$this->html = '

<div class="feg-offer-container">
<button class="ui mini button wallet-reload">
  Reload Wallet (demo)
</button>
	<div class="ui two column stackable grid">
		<div class="ten wide column">
			<div class="ui segment">
				<table id="table-offer" class="ui compact celled unstackable table offer">
				 	<thead>
					    <tr>
						    <th>Game</th>
						    <th>1</th>
						    <th>x</th>
						    <th>2</th>
					  	</tr>
					  </thead>
					  <tbody>
					    ' . $games_html . '
					  </tbody>
				</table>
			</div>
		</div>
		
		<div class="six wide column">
			<div class="ui segment">
				<div class="wallet-container">
					<p class="wallet">Wallet: <span>' . number_format( (float) feg_bet_get_wallet_amount(), 2, '.', '' ) . '</span>€</p>
					<a class="ui basic red label bet-label" style="display: none">label</a>
				</div>
				<div class="offer-bets"></div>
				<div class="offer-input">
					<div class="ui right labeled input">
						<input id="bet-size" type="number" placeholder="Enter Amount...">
						<div class="ui basic label">€</div>
					</div>
					<div class="win-amount">Est. Return: <span>0.00</span>€</div>
				</div>
				<div class="offer-place-bet">
					<button class="ui button disabled">
						<i class="euro sign icon"></i>
						Place Bet
					</button>
					</div>
			</div>
		</div>
		
	</div>
</div>
		';

		return $this->html;
	}

}