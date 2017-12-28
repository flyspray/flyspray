<div id="toolbox">
<?php if(isset($utf8mb4upgradable)) { echo '<div class="error">'.Filters::noXSS($utf8mb4upgradable).'</div>'; } ?>
<?php if(isset($oldmysqlversion)) { echo '<div class="error">'.Filters::noXSS($oldmysqlversion).'</div>'; } ?>
<?php if(isset($adodbversion)) { echo '<div>ADODB version: '.Filters::noXSS($adodbversion).'</div>'; } ?>
<div>PHP version: <?php echo PHP_VERSION; ?></div>
</div>
