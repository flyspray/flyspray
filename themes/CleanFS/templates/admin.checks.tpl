<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?= eL('flyspraychecks'); ?></h2>
<p><?= eL('phpversion') ?> <code><?php echo PHP_VERSION; ?></code></p>
<?php if(isset($utf8mb4upgradable)): ?>
<div class="error"><?= Filters::noXSS($utf8mb4upgradable) ?></div>
<?php endif; ?>

<?php if(isset($oldmysqlversion)): ?>
<div class="error"><?= Filters::noXSS($oldmysqlversion) ?></div>
<?php endif; ?>
<p><?= eL('adodbversion') ?> <code><?php if(isset($adodbversion)) { echo Filters::noXSS($adodbversion); } ?></code></p>
<p><?= eL('swiftmailerversion') ?> <code><?php if(isset($swiftmailerversion)) { echo Filters::noXSS($swiftmailerversion); } ?></code></p>
<p><?= eL('htmlpurifierversion') ?> <code><?php if(isset($htmlpurifierversion)) { echo Filters::noXSS($htmlpurifierversion); } ?></code></p>

<div class="box">
<h3><?= eL('passwdcrypt') ?> <?php echo Filters::noXSS($passwdcrypt); ?></h3>
<?php if(isset($hashlengths)): ?>
<p><?= eL('passwordhashlengths') ?></p>

<table id="passwdhashes">
<thead>
<tr>
	<th><?= eL('stringlength') ?></th>
	<th><?= eL('count') ?></th>
	<th><?= eL('salted') ?></th>
	<th><?= eL('options') ?></th>
	<th><?= eL('hashalgorithm') ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($pwhashes as $pwhash) {
?>
<tr class="<?= $pwhash['class'] ?>">
	<td><?= $pwhash['l'] ?></td>
	<td><?= $pwhash['c'] ?></td>
	<td class="<?= eL($pwhash['s'] ? 'yes' : 'no') ?>"><span class="fas fa-<?= ($pwhash['s'] ? 'check' : 'ban') ?>"></span></td>
	<td><?= $pwhash['bcr'] . ' ' . $pwhash['cr'] . ' ' . $pwhash['md5crypt'] . ' ' . $pwhash['argon2i'] ?></td>
	<td><?= $pwhash['algo'] ?></td>
</tr>
<?php
}
?>
</tbody>
</table>

<?php if ($warnhash > 0): ?>
<div class="error"><?= $warnhash ?> users with unsalted password hashes.</div>
<?php endif; ?>

<?php if ($warnhash2 > 0): ?>
<div class="error"><?= $warnhash2 ?> users with salted password hashes, but considered bad algorithms for password hashing.</div>
<?php endif; ?>

<?php endif; ?>
</div>

<?php if(isset($registrations)): ?>
<div class="box">
<h3><?= $regcount ?> <?= eL('unfinishedregistrations') ?></h3>
<table id="unfinishedregistrations" class="userlist">
<thead>
<tr>
	<th class="regdate"><?= eL('registrationtime') ?></th>
	<th class="username"><?= eL('username') ?></th>
	<th class="emailaddress"><?= eL('emailaddress') ?></th>
</tr>
</thead>
<tbody>
<?php foreach($registrations as $reg): ?>
<tr>
	<td class="user_regdate"><?= formatDate($reg['reg_time']) ?></td>
	<td class="user_username"><?= Filters::noXSS($reg['user_name']) ?></td>
	<td class="user_emailaddress"><?= Filters::noXSS($reg['email_address']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php if(isset($xmppmessagecount)): ?>
<div class="box">
<h3><?= $xmppmessagecount ?> <?= eL('unsentxmppmessages') ?></h3>

<?php echo tpl_form(Filters::noXSS(createUrl($baseurl))); ?>
<input type="hidden" name="action" value="admin.xmppcleanup"/>
<?php if(isset($olderyear) && $olderyear>0): ?>
<button type="submit" name="xmppcleanup" value="year">delete <?= $olderyear ?> unsent xmpp notifications older 1 year</button>
<?php endif; ?>
<?php if(isset($oldermonth) && $oldermonth>0): ?>
<button type="submit" name="xmppcleanup" value="month">delete <?= $oldermonth ?> unsent xmpp notifications older 1 month</button>
<?php endif; ?>
</form>
<table id="unsentxmpp">
<thead>
<tr>
	<th><?= eL('messageid') ?></th>
	<th><?= eL('recipients') ?></th>
	<th><?= eL('created') ?></th>
	<th><?= eL('messagesubject') ?></th>
</tr>
</thead>
<tbody>
<?php foreach($xmppmessages as $xmppm): ?>
<tr>
	<td class="messageid"><?= $xmppm['message_id'] ?></td>
	<td class="recipients"><?= $xmppm['rcount'] ?></td>
	<td class="timcreated"><?= formatDate($xmppm['time_created'], true) ?></td>
	<td class="subject"><?= Filters::noXSS($xmppm['message_subject']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php if(isset($legacyusernamessummary)): ?>
<?= $legacyusernamessummary ?>
<?php endif; ?>

<div class="box">
<h3><?= eL('categoriesstatus') ?></h3>
<?php if (isset($cattreelftrgt) or isset($cattreenonunique) or isset($cattreeerrors)): ?>
<div class="error"><?= eL('categoryerrorsdetected') ?></div>
<?php else: ?>
<p><?= eL('nocategorytreeerrors') ?></p>
<?php endif; ?>

<?php if(isset($cattreelftrgt)): ?>
<div class="error"><?= $cattreelftrgt ?> <?= eL('categoryhandordererrors') ?></div>
<?php endif; ?>

<?php if(isset($cattreenonunique)): ?>
<div class="error"><?= eL('categoryhanduniqerrors') ?></div>
<table>
<thead>
<tr>
<th><?= eL('projectid') ?></th>
<th><?= eL('categorybadleftright') ?></th>
<th><?= eL('count') ?></th>
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
<table id="cattreeerrors">
<thead>
<tr>
	<th><?= eL('projectid') ?></th>
	<th><?= eL('categorytreeanomalies') ?></th>
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
<p>This shows 20 of that as example.</p>
<?php endif; ?>
</div>

<table id="taskcategoryerrors" class="tasklist">
<thead>
<tr>
	<th class="categoryerror divider"><?= eL('categoryerror') ?></th>
	<th class="id"><?= eL('taskid') ?></th>
	<th class="summary"><?= eL('summary') ?></th>
	<th class="closed"><?= eL('taskclosed') ?></th>
	<th class="projectid"><?= eL('taskprojectid') ?></th>
	<th class="categoryid divider"><?= eL('taskcategoryid') ?></th>
	<th class="project divider"><?= eL('projecttitle') ?></th>
	<th class="categoryprojectid"><?= eL('categoryprojectid') ?></th>
	<th class="category divider"><?= eL('categoryname') ?></th>
	<th class="categoryproject"><?= eL('categoryprojecttitle') ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($wrongtaskcategories as $wtc): ?>
<tr class="category-error-<?= (is_null($wtc['cpid']) ? 'notexist' : 'mismatch') ?>">
<?php if(is_null($wtc['cpid'])) { ?>
	<td class="task_categoryerror divider"><span class="fas fa-circle-exclamation fa-lg"></span> <?= eL('categorynotexist') ?></td>
<?php } else { ?>
	<td class="task_categoryerror divider"><span class="fas fa-square-xmark fa-lg"></span> <?= eL('categoryotherproject') ?></td>
<?php } ?>
	<td class="task_id"><?= $wtc['task_id'] ?></td>
	<td class="task_summary"><?= tpl_tasklink($wtc) ?></td>
	<td class="task_closed"><?= ($wtc['is_closed'] ? el('yes') : eL('no'))?></td>
	<td class="task_projectid"><?= $wtc['tpid'] ?></td>
	<td class="task_categoryid divider"><?= $wtc['product_category'] ?></td>
	<td class="task_project divider"><?= $wtc['project_title'] ?></td>
	<td class="task_categoryprojectid"><?= (!is_null($wtc['cpid']) ? $wtc['cpid'] : '&mdash;') ?></td>
	<td class="task_category divider"><?= (!is_null($wtc['task_category_name']) ? $wtc['task_category_name'] : '&mdash;') ?></td>
	<td class="task_categoryproject"><?= (!is_null($wtc['other_project_title']) ? $wtc['other_project_title'] : '&mdash;') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php if(isset($fstables)): ?>
<h3><?= eL('database') ?></h3>
<p><?= eL('databasetype') ?> <code><?= $conf['database']['dbtype'] ?></code></p>
<p><?= eL('defaultcharset') ?> <code><?= Filters::noXSS($fsdb['default_character_set_name']) ?></code></p>
<p><?= eL('defaultcollation') ?> <code><?= Filters::noXSS($fsdb['default_collation_name']) ?></code></p>

<p><a id="toggledbconninfo" class="button" data-off-text="<?= el('showconnectioninfo') ?>" data-on-text="<?= eL('hideconnectioninfo') ?>" data-off-icon="eye" data-on-icon="eye-slash"> <span class="fas fa-eye fa-lg"></span><span><?= eL('showconnectioninfo') ?></span></a></p>

<pre id="dbinfo" class="hidden-info">
<?php global $db; echo Filters::noXSS(print_r($db->dblink, true)); ?>
</pre>

<p><a id="toggledbfields" class="button" data-off-text="<?= el('showfields') ?>" data-on-text="<?= eL('hidefields') ?>" data-off-icon="eye" data-on-icon="eye-slash"> <span class="fas fa-eye fa-lg"></span><span><?= eL('showfields') ?></span></a></p>

<table id="dbtables" class="hidden-fields">
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
