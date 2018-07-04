<div id="toolbox">
<div>PHP version: <?php echo PHP_VERSION; ?></div>
<?php if(isset($utf8mb4upgradable)) { echo '<div class="error">'.Filters::noXSS($utf8mb4upgradable).'</div>'; } ?>
<?php if(isset($oldmysqlversion)) { echo '<div class="error">'.Filters::noXSS($oldmysqlversion).'</div>'; } ?>
<div>ADOdb version: <?php if(isset($adodbversion)) { echo Filters::noXSS($adodbversion); } ?></div>
<div>HTMLPurifier version:<?php if(isset($htmlpurifierversion)) { echo Filters::noXSS($htmlpurifierversion); } ?></div>
<div>passwdcrypt: <?php echo Filters::noXSS($passwdcrypt); ?></div>
<?php if(isset($hashlengths)) { echo '<div>password hash lengths: '.$hashlengths.'</div>'; } ?>
</div>
