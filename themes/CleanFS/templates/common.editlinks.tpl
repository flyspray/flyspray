<?php if($links): ?>
    <table class="links">
     <thread><tr><th><?php echo Filters::noXSS(L('link')); ?></th><th><?php echo Filters::noXSS(L('delete')); ?></th></tr></thread>
      <?php foreach ($links as $link): ?>
       <tr>
        <td>
         <a href="<?php echo $link['url']; ?>"><?php echo $link['url']; ?></a>
        </td>
        <td>
         <input type="checkbox" <?php echo Filters::noXSS(tpl_disableif(!$user->perms('delete_attachments'))); ?> name="delete_link[]" value="<?php echo $link['link_id']; ?>" />
        </td>
       </tr>
      <?php endforeach; ?>
     </table>
<?php endif; ?>
