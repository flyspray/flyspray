<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('tags')); ?></h3>
  <p>Tag management is not yet implemented into Flyspray.</p>
  <p>Please see <a href="https://bugs.flyspray.org/2012" target="_blank">bugs.flyspray.org/2012</a> for status of <b>Tags</b> feature.</p>
<?php
$rows=$proj->listTags(false);

?>
<table class="list">
<colgroup>   
<col class="cname"></col>
<col class="ccount"></col>
<col class="cdelete"></col>
</colgroup>
<thead>
<tr>
<th>name</th>
<th>usage counter</th>
<th>actions</th>
</tr>
</thead>
<tbody>
<?php
foreach ($rows as $row):
?>
<tr>
<td><?php echo Filters::noXSS($row['name']);?></td>
<td><?php echo Filters::noXSS($row['count']);?></td>
<td></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p>TODO: <span title="DELETE FROM {tags} WHERE tag ='?';">delete tag</span>, :hover lists tasks</p>

</div>
