<?php if($links): ?>
<table class="links">
<thead>
    <tr><th><?php echo Filters::noXSS(L('link')); ?></th><th<?php echo $user->perms('delete_attachments') ? '' : ' style="color:#999"'; ?>><?php echo Filters::noXSS(L('delete')); ?></th></tr>
</thead>
<tbody>
<?php foreach ($links as $link): ?>
    <tr>
        <td><a href="<?php echo $link['url']; ?>"><?php echo $link['url']; ?></a></td>
        <td><input type="checkbox" <?php echo Filters::noXSS(tpl_disableif(!$user->perms('delete_attachments'))); ?> name="delete_link[]" value="<?php echo $link['link_id']; ?>" /></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
