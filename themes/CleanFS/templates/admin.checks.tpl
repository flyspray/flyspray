<div id="toolbox">
<div>PHP version: <?php echo PHP_VERSION; ?></div>
<?php if(isset($utf8mb4upgradable)) { echo '<div class="error">'.Filters::noXSS($utf8mb4upgradable).'</div>'; } ?>
<?php if(isset($oldmysqlversion)) { echo '<div class="error">'.Filters::noXSS($oldmysqlversion).'</div>'; } ?>
<div>ADOdb version: <?php if(isset($adodbversion)) { echo Filters::noXSS($adodbversion); } ?></div>
<div>HTMLPurifier version: <?php if(isset($htmlpurifierversion)) { echo Filters::noXSS($htmlpurifierversion); } ?></div>
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
#toggledbinfo { display:none; }
#toggledbinfo ~ #toggledbinfolabel:after { content:'Hide connection info'; }
#toggledbinfo:checked ~ #toggledbinfolabel::after { content:'Show connection info'; }
#toggledbinfo:checked ~ #dbinfo { display:none; }

#togglefields { display:none; }
#togglefields ~ #togglefieldslabel:after { content:'Hide Fields'; }
#togglefields:checked ~ #togglefieldslabel::after { content:'Show Fields'; }
#togglefields:checked ~ #dbtables .dbfield { display:none; }
</style>
<div><?= $conf['database']['dbtype'] ?></div>
<div>Default character set: <?= Filters::noXSS($fsdb['default_character_set_name']) ?></div>
<div>Default collation: <?= Filters::noXSS($fsdb['default_collation_name']) ?></div>
<input type="checkbox" id="toggledbinfo" name="toggledbinfo" checked="checked" />
<label for="toggledbinfo" class="button" id="toggledbinfolabel"></label>
<div id="dbinfo">
	<pre><?php global $db; echo Filters::noXSS(print_r($db->dblink, true)); ?></pre>       
</div>
<br/>
<input type="checkbox" id="togglefields" name="togglefields" checked="checked" />
<label for="togglefields" class="button" id="togglefieldslabel"></label>
<table id="dbtables">
<thead>
<tr class="dbtable">
<th>table_name</th>
<th>table_type</th>
<th></th>
<th>default collation</th>
<th>comment</th>
</tr>
<tr class="dbfield">
<th>column_name</th>
<th>data_type</th>
<th>character_set_name</th>
<th>collation_name</th>
<th>comment</th>
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
	if ($lasttable != $f['table_name']): 
		$ti++;
	?>
	<tr class="dbtable">
	<td><?= Filters::noXSS($fstables[$ti]['table_name']) ?></td>
	<td><?= $fstables[$ti]['table_type'] ?></td>
	<td></td>
	<td><?= $fstables[$ti]['table_collation'] ?></td>
	<td><?= Filters::noXSS($fstables[$ti]['table_comment']) ?></td>
	</tr>
	<?php endif; ?>
<tr class="dbfield">
<td><?= Filters::noXSS($f['column_name']) ?></td>
<td><?= $f['column_type'] ?></td>
<td><?= $f['character_set_name'] ?></td>
<td><?= $f['collation_name'] ?></td>
<td><?= Filters::noXSS($f['column_comment']) ?></td>
</tr>
<?php
$lasttable=$f['table_name'];
endforeach;
?>
</tbody>
</table>
<?php endif; ?>

</div>
