<div class="lemonway-payment-container" id="lemonway_cc_payment_container">
    <h3 class="lemonway-method-title">
        <?= $text_card; ?>
    </h3>
    <div class="lemonway_payment_form">
        <form class="placeOrderForm" id="lemonway_payment_form" action="<?= $link_checkout; ?>" method="POST">
            <?php
            if ($lemonway_oneclick_enabled == '1' && $customerId) { // Hide One-click form from guest
                if ($card) { // User already have a saved card. He can choose to use it or not
            ?>
            <div id="oneclick_container">
                <div class="radio">
                    <label for="lw_use_card">
                        <input type="radio" id="lw_use_card" name="lemonway_oneclick" value="use_card" checked />
                        <?= $entry_use_card; ?> (<em><?= $card['card_num']; ?></em> - <em><?= $card['card_exp']; ?></em>)
                    </label>
                </div>
                <div class="radio">
                    <label for="lw_register_card">
                        <input type="radio" id="lw_register_card" name="lemonway_oneclick" value="register_card" />
                        <?= $entry_save_new_card; ?>
                    </label>
                </div>
                <div class="radio">
                    <label for="lw_no_use_card">
                        <input type="radio" id="lw_no_use_card" name="lemonway_oneclick" value="no_use_card" />
                        <?= $entry_not_use_card; ?>
                    </label>
                </div>
            </div>
            <?php
                }
            }
            ?>

            <!-- Choose a card type -->
            <div id="card_choosing_container">
                <div class="radio">
                    <label>
                        <input type="radio" name="cc_type" value="CB" required />
                        <img alt="CB" src="catalog/view/theme/default/image/lemonway_CB.gif">
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="cc_type" value="VISA" required />
                        <img alt="VISA" src="catalog/view/theme/default/image/lemonway_VISA.gif">
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="cc_type" value="MASTERCARD" required />
                        <img alt="MASTERCARD" src="catalog/view/theme/default/image/lemonway_MASTERCARD.gif">
                    </label>
                </div>
            </div>

            <?php
            if ($lemonway_oneclick_enabled == '1' && $customerId) { // Hide One-click form from guest
                if (!$card) { // If no saved card => Ask to save the card
            ?>
            <!-- User can choose to save his card -->
            <div class="checkbox">
                <label>
                    <input id="lw_register_card" value="register_card" type="checkbox" name="lemonway_oneclick" />
                    <?= $entry_save_card;?>
                </label>
            </div>
            <?php
                }
            }
            ?>

            <div class="buttons">
                <div class="pull-right">
                    <button type="submit" form="lemonway_payment_form" data-toggle="tooltip" class="btn btn-primary" data-loading-text="<?= $text_loading; ?>">
                        <?= $button_continue; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
