<div id="toolbox">
<div>PHP version: <?php echo PHP_VERSION; ?></div>
<?php if(isset($utf8mb4upgradable)) { echo '<div class="error">'.Filters::noXSS($utf8mb4upgradable).'</div>'; } ?>
<?php if(isset($oldmysqlversion)) { echo '<div class="error">'.Filters::noXSS($oldmysqlversion).'</div>'; } ?>
<div>ADOdb version: <?php if(isset($adodbversion)) { echo Filters::noXSS($adodbversion); } ?></div>
<div>HTMLPurifier version:<?php if(isset($htmlpurifierversion)) { echo Filters::noXSS($htmlpurifierversion); } ?></div>
<div>passwdcrypt: <?php echo Filters::noXSS($passwdcrypt); ?></div>
<?php if(isset($hashlengths)) { echo '<div>password hash lengths: '.$hashlengths.'</div>'; } ?>

<?php if(isset($registrations)): ?>
<h4><?= $regcount ?> unfinished registrations</h4>
<table>
<thead>
<tr>
<th>reg_time</th>
<th>user_name</th>
<th>email_address</th>
</tr>
</thead>
<tbody>
<?php foreach($registrations as $reg): ?>
<tr>
<td><?= formatDate($reg['reg_time']) ?></td>
<td><?= Filters::noXSS($reg['user_name']) ?></td>
<td><?= Filters::noXSS($reg['email_address']) ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

</div>
