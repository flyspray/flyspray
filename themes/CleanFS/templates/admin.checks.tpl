<div id="toolbox">
<style>
#toolbox div.div {
	border:1px solid #999;
	margin-top:1em;
	margin-bottom:1em;
}
</style>
<div>PHP version: <?php echo PHP_VERSION; ?></div>
<?php if(isset($utf8mb4upgradable)) { echo '<div class="error">'.Filters::noXSS($utf8mb4upgradable).'</div>'; } ?>
<?php if(isset($oldmysqlversion)) { echo '<div class="error">'.Filters::noXSS($oldmysqlversion).'</div>'; } ?>
<div>ADOdb version: <?php if(isset($adodbversion)) { echo Filters::noXSS($adodbversion); } ?></div>
<div>Swiftmailer version: <?php if(isset($swiftmailerversion)) { echo Filters::noXSS($swiftmailerversion); } ?></div>
<div>HTMLPurifier version: <?php if(isset($htmlpurifierversion)) { echo Filters::noXSS($htmlpurifierversion); } ?></div>

<div class="div">
<div>passwdcrypt: <?php echo Filters::noXSS($passwdcrypt); ?></div>
<?php if(isset($hashlengths)) { echo '<div>password hash lengths: '.$hashlengths.'</div>'; } ?>
</div>

<?php if(isset($registrations)): ?>
<div class="div">
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
</tbody>
</table>
</div>
<?php endif; ?>

<?php if(isset($xmppmessagecount)): ?>
<div class="div">
<p><?= $xmppmessagecount.' unsent xmpp messages' ?></p>

<?php echo tpl_form(Filters::noXSS(createUrl($baseurl))); ?>
<input type="hidden" name="action" value="admin.xmppcleanup"/>
<?php if(isset($olderyear) && $olderyear>0): ?>
<button type="submit" name="xmppcleanup" value="year">delete <?= $olderyear ?> unsent xmpp notifications older 1 year</button> 
<?php endif; ?>
<?php if(isset($oldermonth) && $oldermonth>0): ?>
<button type="submit" name="xmppcleanup" value="month">delete <?= $oldermonth ?> unsent xmpp notifications older 1 month</button> 
<?php endif; ?>
</form>
<table>
<thead>
<tr>
<th>message_id</th>
<th>Recipients</th>
<th>time_created</th>
<th>message_subject</th>
</tr>
</thead>
<tbody>
<?php foreach($xmppmessages as $xmppm): ?>
<tr>
<td><?= $xmppm['message_id'] ?></td>
<td><?= $xmppm['rcount'] ?></td>
<td><?= formatDate($xmppm['time_created'], true) ?></td>
<td><?= Filters::noXSS($xmppm['message_subject']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php if(isset($legacyusernamessummary)): ?>
<?= $legacyusernamessummary ?>
<?php endif; ?>

<div class="div">
<?php if (isset($cattreelftrgt) or isset($cattreenonunique) or isset($cattreeerrors)): ?>
<div class="error">Category errors detected:</div>
<?php else: ?>
<p>No category tree errors found.</p>
<?php endif; ?>

<?php if(isset($cattreelftrgt)): ?>
<div class="error"><?= $cattreelftrgt ?> rgt not greater lft value errors detected.</div>
<?php endif; ?>

<?php if(isset($cattreenonunique)): ?>
<div class="error">lft-rgt nonunique detected.</div>
<table>
<thead>
<tr>
<th>project_id</th>
<th>bad lft/rgt values</th>
<th>count</th>
</tr>
</thead>
<tbody>
<?php foreach($cattreenonunique as $nonunique): ?>
<tr>
<td><?= $nonunique['project_id'] ?></td>
<td><?= $nonunique['lft'] ?></td>
<td><?= $nonunique['c'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<?php if(isset($cattreeerrors)): ?>
<table>
<thead>
<tr>
<th>project_id</th>
<th>Category Tree Anomalies</th>
</tr>
</thead>
<tbody>
<?php foreach($cattreeerrors as $caterr): ?>
<tr>
<td><?= $caterr['project_id'] ?></td>
<td><?= $caterr['count'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>

<?php if ($wrongtaskcategoriescount) : ?>
<div>
<div class="error"><p>There are <?= $wrongtaskcategoriescount ?> tasks with a bad product_category value, either having a category id thats from another project or there is no category with this id anymore.</p>
<?php if ($wrongtaskcategoriescount >20): ?>
<span>This shows 20 of that as example.</span>
<?php endif; ?>
</div>

<table>
<thead>
<tr>
<th>task_id</th>
<th>task project_id</th>
<th>category project_id</th>
<th>task cat_id</th>
<th>closed</th>
</tr>
</thead>
<tbody>
<?php foreach ($wrongtaskcategories as $wtc): ?>
<tr>
<td><?= $wtc['task_id'] ?></td>
<td><?= $wtc['tpid'] ?></td>
<td><?= $wtc['cpid'] ?></td>
<td><?= $wtc['product_category'] ?></td>
<td><?= $wtc['is_closed'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

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
<th></th>
<th></th>
<th>default collation</th>
<th>comment</th>
</tr>
<tr class="dbfield">
<th>column_name</th>
<th>data_type</th>
<th>column_default</th>
<th>is_nullable</th>
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
	<td></td>
	<td></td>
	<td><?= $fstables[$ti]['table_collation'] ?></td>
	<td><?= Filters::noXSS($fstables[$ti]['table_comment']) ?></td>
	</tr>
	<?php endif; ?>
<tr class="dbfield">
<td><?= Filters::noXSS($f['column_name']) ?></td>
<td><?= $f['column_type'] ?></td>
<td><?= is_null($f['column_default']) ? '<em>NULL</em>' : Filters::noXSS($f['column_default']) ?></td>
<td><?= $f['is_nullable'] ?></td>
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
