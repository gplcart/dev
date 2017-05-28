<?php
/**
 * @package Dev
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */
?>
<div id="dev-module-toolbar">
  <div class="row summary hidden-xs">
    <div class="col-md-6 queries">
      <?php echo $this->text('SQL queries: @queries Execution time: @time sec', array('@queries' => count($queries), '@time' => $time)); ?>
    </div>
    <div class="col-md-3 validator">
      <?php echo $this->text('Markup errors: <span class="result error">@count</span>', array('@count' => 0)); ?>
    </div>
    <div class="col-md-3 pagespeed">
      <?php if (empty($key)) { ?>
      <?php echo $this->text('PageSpeed Insights is not configured'); ?>
      <?php } ?>
      <span class="fa fa-bars toggler"></span>
    </div>
  </div>
  <div class="row details small hidden-xs">
    <div class="queries col-md-6">
      <ul class="scroll">
        <?php foreach ($queries as $query) { ?>
        <li class="small"><?php echo $this->e($query); ?></li>
        <?php } ?>
      </ul>
    </div>
    <div class="col-md-3 validator">
      <div class="results scroll"><?php echo $this->text('No results'); ?></div>
    </div>
    <div class="col-md-3 pagespeed">
      <div class="resources"></div>
      <div class="suggestions"></div>
    </div>
  </div>
</div>