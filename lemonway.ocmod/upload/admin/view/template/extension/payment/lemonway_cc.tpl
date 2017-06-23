<div class="panel">
    <div class="panel-heading">
        <i class="fa fa-cog"></i>
        <?php echo $text_method_configuration;?>
    </div>

    <div class="form-wrapper">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <?php echo $entry_status; ?>
            </label>

            <div class="col-lg-7">
                <input type="checkbox" name="lemonway_cc_status" id="lemonway_cc_status" value="1" <?php if($lemonway_cc_status == '1') echo 'checked';?> />
            </div>
        </div>
        
        <div class="form-group">
            <label class="control-label col-lg-3">
                <?php echo $entry_one_click;?>
            </label>

            <div class="col-lg-7">
                <input type="checkbox" name="lemonway_oneclick_enabled" id="lemonway_cc_oneclic_enabled_on" value="1" <?php if($lemonway_oneclick_enabled=='1') echo 'checked';?> />
                <p class="help-block">
                  <?php echo $help_oneclick;?>
                </p>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">
                <?php echo $entry_css;?>
            </label>

            <div class="col-lg-7">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-css3"></i>
                    </span>

                    <input type="text" name="lemonway_css_url"   id="LEMONWAY_CSS_URL" class="form-control"  <?php echo 'value="'.$lemonway_css_url.'"' ;?> />
                </div>
            </div>
        </div>
    </div>
</div>
