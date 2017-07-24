<div>
    <h3 class="lemonway-method-title">
        <?= $text_card ?>
    </h3>
    <div>
        <form class="placeOrderForm" action="<?= $link_checkout ?>" method="POST">
            <?php
            if ($lemonway_oneclick_enabled == '1' && $customerId) { // Hide One-click form from guest
                if ($card) { // User already have a saved card. He can choose to use it or not
            ?>
            <div>
                <div class="radio">
                    <label>
                        <input type="radio" class="hide_cards" name="lemonway_oneclick" value="use_card" checked />
                        <?= $entry_use_card ?> (<em><?= $card['card_num'] ?></em> - <em><?= $card['card_exp'] ?></em>)
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" class="show_cards" name="lemonway_oneclick" value="register_card" />
                        <?= $entry_save_new_card ?>
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" class="show_cards" name="lemonway_oneclick" value="no_use_card" />
                        <?= $entry_not_use_card ?>
                    </label>
                </div>
            </div>
            <?php
                }
            }
            ?>

            <!-- Choose a card type -->
            <!-- If the user choose to use a saved card, he doesn't need to choose a card type -->
            <div id="card_choosing_container" style="<?= ($lemonway_oneclick_enabled == '1' && $customerId && $card) ? 'display: none' : '' ?>">
                <div class="row">
                    <div class="col-md-1 text-center">
                        <label>
                            <img alt="CB" src="catalog/view/theme/default/image/lemonway_CB.gif">
                            <input type="radio" name="cc_type" value="CB" checked />
                        </label>
                    </div>
                    <div class="col-md-1 text-center">
                        <label>
                            <img alt="VISA" src="catalog/view/theme/default/image/lemonway_VISA.gif">
                            <input type="radio" name="cc_type" value="VISA" />
                        </label>
                    </div>
                    <div class="col-md-1 text-center">
                        <label>
                            <img alt="MASTERCARD" src="catalog/view/theme/default/image/lemonway_MASTERCARD.gif">
                            <input type="radio" name="cc_type" value="MASTERCARD" />
                        </label>
                    </div>
                </div>
            </div>

            <div class="buttons">
                <div class="pull-right">
                    <?php
                    if ($lemonway_oneclick_enabled == '1' && $customerId) { // Hide One-click form from guest
                        if (!$card) { // If no saved card => Ask to save the card
                    ?>
                    <!-- User can choose to save his card -->
                    <label>
                        <?= $entry_save_card ?>
                        <input type="checkbox" name="lemonway_oneclick" value="register_card" />
                    </label>
                    <?php
                        }
                    }
                    ?>
                    <button type="submit" data-toggle="tooltip" class="btn btn-primary" data-loading-text="<?= $text_loading ?>">
                        <?= $button_continue ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(function()
    {   
        // If using a saved card, hide the card choices
        $(".hide_cards").click(function()
        {
            $('#card_choosing_container').hide(300);
        });

        // If not using a saved card, show the card choices
        $(".show_cards").click(function()
        {
            $('#card_choosing_container').show(300);
        })
    });
</script>