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

<?php if(isset($fstables)): ?>
<style>
.dbtable{ background-color:#ccc;}
.dbtable td {border-bottom:1px solid #999;}
.dbfield{ background-color:#eee;}
#togglefields { display:none; }
#togglefields ~ label:after { content:'Hide Fields'; }
#togglefields:checked ~ label:after { content:'Show Fields'; }
#togglefields:checked ~ #dbtables .dbfield { display:none; }
</style>
<input type="checkbox" id="togglefields" name="togglefields" checked="checked" />
<label for="togglefields" class="button"></label>
<table id="dbtables">
<thead>
<tr class="dbtable">
<th>TABLE_NAME</th>
<th>ENGINE</th>
<th></th>
<th>DEFAULT COLLATION</th>
<th>COMMENT</th>
</tr>
<tr class="dbfield">
<th>COLUMN_NAME</th>
<th>COLUMN_TYPE</th>
<th>CHARACTER_SET_NAME</th>
<th>COLLATION_NAME</th>
<th>COMMENT</th>
</tr>
</thead>
<tbody>
<?php
$lasttable='';
$ti=-1; # $fstables index
foreach($fsfields as $f):
	# Show table info row if not yet for that field
	# This logic fails if there exists a table within $fstables without fields in $fsfields
	# But for our usecase this should be ok.
	if ($lasttable != $f['TABLE_NAME']): 
		$ti++;
	?>
	<tr class="dbtable">
	<td><?= Filters::noXSS($fstables[$ti]['TABLE_NAME']) ?></td>
	<td><?= $fstables[$ti]['ENGINE'] ?></td>
	<td></td>
	<td><?= $fstables[$ti]['TABLE_COLLATION'] ?></td>
	<td><?= Filters::noXSS($fstables[$ti]['TABLE_COMMENT']) ?></td>
	</tr>
	<?php endif; ?>
<tr class="dbfield">
<td><?= Filters::noXSS($f['COLUMN_NAME']) ?></td>
<td><?= $f['COLUMN_TYPE'] ?></td>
<td><?= $f['CHARACTER_SET_NAME'] ?></td>
<td><?= $f['COLLATION_NAME'] ?></td>
<td><?= Filters::noXSS($f['COLUMN_COMMENT']) ?></td>
</tr>
<?php
$lasttable=$f['TABLE_NAME'];
endforeach;
?>
</tbody>
</table>
<?php endif; ?>

</div>
