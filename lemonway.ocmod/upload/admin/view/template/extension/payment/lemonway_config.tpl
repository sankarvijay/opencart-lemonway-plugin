<div>
    <!-- Account config panel -->
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-user"></i> <?= $text_account_config ?>
        </div>

        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <?= $text_login ?> *
                </label>

                <div class="col-lg-7">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        <input type="text" name="lemonway_api_login" value="<?= (!empty($lemonway_api_login)) ? $lemonway_api_login : '' ?>" class="form-control" required />
                    </div>
                    <p class="help-block">
                        <?= $text_help_login ?>
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    <?= $text_password ?> *
                </label>

                <div class="col-lg-7">
                    <div class="input-group fixed-width-lg">
                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input type="password" name="lemonway_api_password" class="form-control" <?= (empty($lemonway_api_password)) ? "required" : "placeholder='$text_masked'" ?> />
                    </div>
                    <p class="help-block">
                        <?= $text_help_password ?>
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                   <?= $text_test_mode ?>
                </label>

                <div class="col-lg-7">
                    <input type="checkbox" name="lemonway_is_test_mode" value="1" <?= $lemonway_is_test_mode ? 'checked' : '' ?> />
                    <p class="help-block">
                        <?= $text_help_test_mode ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- End Account config panel -->

    <!-- Advanced config panel -->
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-cogs"></i> <?= $text_advanced_config ?>
        </div>

        <div class="form-wrapper">
            <!-- CSS URL -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <?= $text_css ?>
                </label>

                <div class="col-lg-7">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-css3"></i>
                        </span>
                        <input type="text" name="lemonway_css_url" class="form-control" value="<?= $lemonway_css_url ?>" />
                    </div>
                    <p class="help-block">
                        <?= $text_help_css ?>
                    </p>
                </div>
            </div>
            <!-- End CSS URL -->

            <!-- Debug mode -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <?= $text_debug_mode ?>
                </label>

                <div class="col-lg-7">
                    <input type="checkbox" name="lemonway_debug" value="1" <?= $lemonway_debug ? 'checked' : '' ?> />
                    <p class="help-block">
                        <?= DIR_LOGS ?>lemonway_debug.log
                    </p>
                </div>
            </div>
            <!-- End Debug mode -->
        </div>
    </div>
    <!-- End Advanced config panel -->

    <!-- Custom environment panel -->
    <div class="panel">
        <div class="panel-heading">
            <i class="fa fa-wrench"></i> <?= $text_custom_environment ?>
        </div>
        <div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> <?= $error_custom_env ?></div>
        
        <!-- Custom environment name -->
        <div class="form-group">
            <label class="control-label col-lg-3">
               <?= $text_environment_name ?>
            </label>

            <div class="col-lg-7">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-leaf"></i>
                    </span>
                    <input type="text" name="lemonway_environment_name" value="<?= $lemonway_environment_name ?>" class="form-control" />
                </div>
            </div>
        </div>
        <!-- End Custom environment name -->

        <!-- Wallet ID -->
        <div class="form-group">
            <label class="control-label col-lg-3">
                <?= $text_wallet ?>
            </label>

            <div class="col-lg-7">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-google-wallet"></i>
                    </span>
                    <input type="hidden" name="lemonway_wallet" />
                    <input type="text" name="lemonway_custom_wallet" value="<?= $lemonway_custom_wallet ?>" class="form-control" />
                </div>
            </div>
        </div>
        <!-- End Wallet ID -->
    </div>
    <!-- End Custom environment panel -->
</div>