    <?php if ($attachments): ?>
    <table class="attachments">
      <thead><tr><th>{L('file')}</th><th>{L('size')}</th><th>{L('delete')}</th></tr></thead>
      <?php foreach ($attachments as $attachment): ?>
      <tr>
        <td>
          <?php if (file_exists(BASEDIR . '/attachments/' . $attachment['file_name'])): ?>
          <a href="{$_SERVER['SCRIPT_NAME']}?getfile={$attachment['attachment_id']}" title="{$attachment['file_type']}">
          <?php else: ?>
          <del>
          <?php endif; ?>
          <?php
          // Strip the mimetype to get the icon image name
          list($main) = explode('/', $attachment['file_type']);
          $imgdir = BASEDIR . "/themes/{$proj->prefs['theme_style']}/mime/";
          $imgpath = "{$baseurl}themes/{$proj->prefs['theme_style']}/mime/";
          if (file_exists($imgdir.$attachment['file_type'] . '.png')):
          ?>
          <img src="{$imgpath}{$attachment['file_type']}.png" alt="({$attachment['file_type']})" title="{$attachment['file_type']}" />
          <?php else: ?>
          <img src="{$imgpath}{$main}.png" alt="" title="{$attachment['file_type']}" />
          <?php endif; ?>
          &nbsp;&nbsp;{$attachment['orig_name']}
          <?php if (file_exists(BASEDIR . '/attachments/' . $attachment['file_name'])): ?>
          </a>
          <?php else: ?>
          </del>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($attachment['file_size'] < 1000000): ?>
          {round($attachment['file_size']/1024,1)} {L('KiB')}
          <?php else: ?>
          {round($attachment['file_size']/1024/1024,2)} {L('MiB')}
          <?php endif; ?>
        </td>
        <td>
          <input type="checkbox" {!tpl_disableif(!$user->perms('delete_attachments'))} name="delete_att[]" value="{$attachment['attachment_id']}" />
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
