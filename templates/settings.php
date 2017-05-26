<?php
/**
 * @package Dev
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <div class="col-md-6 col-md-offset-2">
          <div class="checkbox">
            <label>
              <input name="settings[status]" type="checkbox"<?php echo empty($settings["status"]) ? '' : ' checked'; ?>> <?php echo $this->text('Enable toolbar'); ?>
              <div class="help-block"><?php echo $this->text('Enable developer toolbar at the bottom of every page. It shows SQL queries, script execution time and PageSpeed Insights test results'); ?></div>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('API key'); ?></label>
        <div class="col-md-6">
          <input name="settings[key]" class="form-control" value="<?php echo $this->escape($settings['key']); ?>">
          <div class="help-block"><?php echo $this->text('If you want to use PageSpeed Insights test, please specify an <a href="https://developers.google.com/console/help/generating-dev-keys">API key</a> to be included with each request'); ?></div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/module/list'); ?>" class="btn btn-default"><?php echo $this->text('Cancel'); ?></a>
            <button class="btn btn-default save" name="save" value="1"><?php echo $this->text('Save'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>