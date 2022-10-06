jQuery(document).ready(function ($) {

  const ButtonTip = '.tip-button',
    ButtonWalletReload = '.wallet-reload',
    DivBettingList = '.offer-bets',
    class_tipActive = 'tip-active',
    SpanWalletAmount = '.wallet > span',
    SpanWinAmount = '.win-amount > span',
    InputBetAmount = '#bet-size',
    ButtonPlaceBet = '.offer-place-bet > button',
    LabelBet = '.bet-label';

  let slip_data = [];

  /**
   * Add game tip to betting slip
   */
  $(document).on('click', ButtonTip, function (e) {
    e.preventDefault();
    resolve_tip($(this));
  });

  /**
   * Input bet amount
   */
  $(document).on('input', InputBetAmount, function (e) {
    e.preventDefault();
    calculate_return();
  });

  /**
   * Place bet
   */
  $(document).on('click', ButtonPlaceBet, function (e) {
    e.preventDefault();
    add_bet_slip();
  });

  /**
   * Reload wallet (demo)
   */
  $(document).on('click', ButtonWalletReload, function (e) {
    e.preventDefault();
    reload_wallet();
  });

  /**
   * Resolve selected tip
   * @param element
   */
  function resolve_tip(element) {

    const game_parent = element.parents('tr'),
      parent = element.parents('td'),
      game_values = {
        game: game_parent.data('game'),
        tip: element.data('tip')
      };

    parent.siblings().find('button').removeClass(class_tipActive);

    element.toggleClass(class_tipActive);

    refresh_slip_data(game_values);

    get_betting_list_information();

  }

  /**
   * Add or remove game with selected tip
   * @param game_values
   */
  function refresh_slip_data(game_values) {

    const game_index = slip_data.findIndex(
      x => x.game === game_values.game
    );

    // if no game -> add game
    if (game_index === -1) {
      slip_data.push(game_values);

      return;
    }

    // game exists with same tip -> remove game
    if (slip_data[game_index].tip === game_values.tip) {
      slip_data.splice(game_index, 1);

      return;
    }

    // game exists with different tip -> change tip
    slip_data[game_index].tip = game_values.tip;
  }

  /**
   * Get betting list info
   */
  function get_betting_list_information() {

    jQuery.ajax({
      url: feg_offer_vars.url,
      dataType: "json",
      method: "GET",
      data: {
        'action': 'feg_bet__ajax_get_slip_information',
        'nonce': feg_offer_vars.nonce,
        'slip_data': slip_data
      },
      success: function (data) {

        refresh_betting_list(data);

        calculate_return();
      },
      error: function (errorThrown) {

        console.log(errorThrown);

        $(SpanWinAmount).html(0.00);
      }
    });
  }

  /**
   * Add betting slip to db
   */
  function add_bet_slip() {

    jQuery.ajax({
      url: feg_offer_vars.url,
      dataType: "json",
      method: "POST",
      data: {
        'action': 'feg_bet__ajax_add_bet_slip',
        'nonce': feg_offer_vars.nonce,
        'slip_data': slip_data,
        'amount': $(InputBetAmount).val()
      },
      success: function (data) {

        $('.feg-offer-container').toast({
          position: 'bottom right',
          displayTime: 8000,
          class: data.status,
          title: data.message,
          message: '',
          showProgress: 'bottom',
        });

        $(SpanWalletAmount).html(parseFloat(data.wallet_amount).toFixed(2));

        calculate_return();
      },
      error: function (errorThrown) {

        console.log(errorThrown);
      }
    });

  }

  /**
   * Calculate return based on bet size & selected odds
   */
  function calculate_return() {

    if (slip_data.length === 0 ||
      !$(InputBetAmount).val() ||
      parseFloat($(InputBetAmount).val()) < 0.1) {
      $(SpanWinAmount).html(0.00);

      return;
    }

    $(LabelBet).hide();

    jQuery.ajax({
      url: feg_offer_vars.url,
      dataType: "json",
      method: "GET",
      data: {
        'action': 'feg_bet__ajax_get_estimated_return',
        'nonce': feg_offer_vars.nonce,
        'slip_data': slip_data,
        'amount': $(InputBetAmount).val()
      },
      success: function (data) {

        resolve_allow_bet(data);
      },
      error: function (errorThrown) {

        console.log(errorThrown);

        $(SpanWinAmount).html(0.00);

        $(ButtonPlaceBet).addClass('disabled');
      }
    });
  }

  /**
   * Reload wallet
   */
  function reload_wallet() {

    jQuery.ajax({
      url: feg_offer_vars.url,
      dataType: "json",
      method: "POST",
      data: {
        'action': 'feg_bet__ajax_set_wallet_amount',
        'nonce': feg_offer_vars.nonce
      },
      success: function (data) {

        $(SpanWalletAmount).html(parseFloat(data.wallet_amount).toFixed(2));

        calculate_return();
      },
      error: function (errorThrown) {

        console.log(errorThrown);
      }
    });
  }

  /**
   * Show estimated return & if bet is allowed
   *
   * @param bet_information
   */
  function resolve_allow_bet(bet_information) {

    $(SpanWinAmount).html(bet_information.estimated_return.toFixed(2));

    $(ButtonPlaceBet).addClass('disabled');

    $(LabelBet).hide();

    if (bet_information.allow_bet > 0) {
      $(ButtonPlaceBet).removeClass('disabled');
    }

    if (bet_information.message) {
      $(LabelBet).html(bet_information.message).show();
    }
  }

  /**
   * Refresh list of selected bets
   *
   * @param betting_list_information
   */
  function refresh_betting_list(betting_list_information) {

    $(DivBettingList).html('');

    $.each(slip_data, function (index, game_element) {
      const db_response_game_index = betting_list_information.findIndex(x => parseInt(x.game_id) === game_element.game),
        betting_list_game = betting_list_information[db_response_game_index],
        offer_bet = $('<div>')
          .addClass('offer-bet')
          .append(
            $('<div>').addClass('offer-bet-game')
              .html(
                betting_list_game.home_team +
                ' - ' +
                betting_list_game.away_team
              )
          ).append(
            $('<div>').addClass('offer-bet-info')
              .append(
                $('<div>').addClass('tip').html(game_element.tip === 0 ? "X" : game_element.tip)
              )
              .append(
                $('<div>').addClass('odds').html(parseFloat(betting_list_game.selected_odds).toFixed(2))
              )
          );

      $(DivBettingList).append(offer_bet);
    });
  }

});