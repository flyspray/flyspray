<?php if($links): ?>
<table class="links">
<thead>
	<tr>
		<th class="wide"><?php echo Filters::noXSS(L('link')); ?></th>
		<th class="narrow"><?php echo Filters::noXSS(L('delete')); ?></th>
	</tr>
</thead>
<tbody>
<?php foreach ($links as $link): ?>
	<tr>
		<td class="wide"><a href="<?php echo Filters::noXSS($link['url']); ?>"><?php echo Filters::noXSS($link['url']); ?></a></td>
		<td class="narrow ta-c"><input type="checkbox" <?php echo Filters::noXSS(tpl_disableif(!$user->perms('delete_attachments'))); ?> name="delete_link[]" value="<?php echo $link['link_id']; ?>" /></td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
