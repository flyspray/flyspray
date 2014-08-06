<?php if($links): ?>
    <table class="links">
     <thread><tr><th>{L('link')}</th><th>{L('delete')}</th></tr></thread>
      <?php foreach ($links as $link): ?>
       <tr>
        <td>
         <a href="{$link['url']}">{$link['url']}</a>
        </td>
        <td>
         <input type="checkbox" {!tpl_disableif(!$user->perms('delete_attachments'))} name="delete_link[]" value="{$link['link_id']}" />
        </td>
       </tr>
      <?php endforeach; ?>
     </table>
<?php endif; ?>
