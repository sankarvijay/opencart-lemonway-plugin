<div class="panel">
    <div class="panel-heading">
        <i class="fa fa-cog"></i>
        <?= $text_method_config ?>
    </div>

    <div class="form-wrapper">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <?= $text_enabled ?>
            </label>

            <div class="col-lg-7">
                <input type="checkbox" name="lemonway_status" id="lemonway_status" value="1" <?= $lemonway_status ? 'checked' : '' ?> />
            </div>
        </div>
        
        <div class="form-group">
            <label class="control-label col-lg-3">
                <?= $text_oneclick ?>
            </label>

            <div class="col-lg-7">
                <input type="checkbox" name="lemonway_oneclick_enabled" id="lemonway_cc_oneclick_enabled_on" value="1" <?= $lemonway_oneclick_enabled ? 'checked' : '' ?> />
                <p class="help-block">
                  <?= $text_help_oneclick ?>
                </p>
            </div>
        </div>
    </div>
</div>