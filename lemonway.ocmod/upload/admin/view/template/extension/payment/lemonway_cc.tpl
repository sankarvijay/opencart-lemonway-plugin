<div class="panel">
    <div class="panel-heading">
        <i class="fa fa-cog"></i>
        <?= $text_method_configuration ?>
    </div>

    <div class="form-wrapper">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <?= $entry_status ?>
            </label>

            <div class="col-lg-7">
                <input type="checkbox" name="lemonway_status" id="lemonway_status" value="1" <?= ($lemonway_status == '1') ? 'checked' : '' ?> />
            </div>
        </div>
        
        <div class="form-group">
            <label class="control-label col-lg-3">
                <?= $entry_oneclick ?>
            </label>

            <div class="col-lg-7">
                <input type="checkbox" name="lemonway_oneclick_enabled" id="lemonway_cc_oneclick_enabled_on" value="1" <?= ($lemonway_oneclick_enabled == '1') ? 'checked' : '' ?> />
                <p class="help-block">
                  <?= $help_oneclick ?>
                </p>
            </div>
        </div>
    </div>
</div>
