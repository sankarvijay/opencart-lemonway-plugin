<div>
    <!-- Account config panel -->
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-user"></i> <?= $text_configuration ?>
        </div>

        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <?= $entry_login; ?> *
                </label>

                <div class="col-lg-7">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user "></i>
                        </span>
                        <input type="text" name="lemonway_api_login" id="lemonway_api_login" <?php if (!empty($lemonway_api_login)) echo 'value="' . $lemonway_api_login . '"';?> class="form-control" required />
                    </div>

                    <p class="help-block">
                        <?= $help_login_prod; ?>
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    <?= $entry_password; ?> *
                </label>

                <div class="col-lg-7">
                    <div class="input-group fixed-width-lg">
                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>

                        <input type="password" id="lemonway_api_password" name="lemonway_api_password" class="form-control"  <?php if (empty($lemonway_api_password))  echo 'required';?> />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                   <?= $entry_test; ?>
                </label>

                <div class="col-lg-7">
                    <input type="checkbox" name="lemonway_is_test_mode" id="lemonway_is_test_mode_on" value="1" <?php if($lemonway_is_test_mode=='1') echo 'checked'; ?> />
                    <p class="help-block">
                        <?= $help_test; ?>
                    </p>
                </div>
            </div>
        </div><!-- form-wrapper -->
    </div>
    <!-- End Account config panel -->

    <!-- Advanced config panel -->
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-cogs"></i> <?= $text_advanced_configuration; ?>
        </div>

        <div class="form-wrapper">
            <!-- CSS URL -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <?= $entry_css;?>
                </label>

                <div class="col-lg-7">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-css3"></i>
                        </span>

                        <input type="text" name="lemonway_css_url" id="LEMONWAY_CSS_URL" class="form-control" <?= 'value="' . $lemonway_css_url . '"' ; ?> />
                    </div>
                </div>
            </div>
            <!-- End CSS URL -->

            <!-- Debug mode -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <?= $entry_debug; ?>
                </label>

                <div class="col-lg-7">
                    <input type="checkbox" name="lemonway_debug" id="lemonway_debug" value="1" <?php if($lemonway_debug=='1') echo 'checked'; ?> />
                    <p class="help-block">
                        <?= DIR_LOGS . 'LemonWayKit-debug.log'; ?>
                    </p>
                </div>
            </div>
            <!-- End Debug mode -->
        </div><!-- /.form-wrapper -->
    </div>
    <!-- End Advanced config panel -->

    <!-- Custom environment panel -->
    <div class="panel">
        <!-- Panel title -->
        <div class="panel-heading">
            <i class="fa fa-leaf"></i> <?= $text_custom_environment; ?>
        </div>
        <!-- End Panel title -->

        <!-- Custom environment name -->
        <div class="form-group">
            <label class="control-label col-lg-3">
               <?= $entry_environment_name; ?>
            </label>

            <div class="col-lg-7">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-leaf"></i>
                    </span>

                    <input type="text" name="lemonway_environment_name" id="lemonway_environment_name" <?php if(!empty($lemonway_environment_name)) echo 'value="' . $lemonway_environment_name . '"' ; ?> class="form-control" />
                </div>

                <p class="help-block">
                   <?= $help_leave_empty; ?>
                </p>
            </div>
        </div>
        <!-- End Custom environment name -->

        <!-- Wallet ID -->
        <div class="form-group">
            <label class="control-label col-lg-3">
                <?= $entry_wallet; ?>
            </label>

            <div class="col-lg-7">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-google-wallet"></i>
                    </span>

                    <input type="text" name="lemonway_merchant_id" id="lemonway_merchant_id" <?php if(!empty($lemonway_merchant_id)) echo 'value="' . $lemonway_merchant_id . '"'; ?> class="form-control" required />
                </div>

                <p class="help-block">
                  <?= $help_wallet; ?>
                </p>
            </div>
        </div>
        <!-- End Wallet ID -->
    </div>
    <!-- End Custom environment panel -->
</div>