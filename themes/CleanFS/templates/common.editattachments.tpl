    <?php if ($attachments): ?>
    <table class="attachments">
      <thead><tr><th><?php echo Filters::noXSS(L('file')); ?></th><th><?php echo Filters::noXSS(L('size')); ?></th><th><?php echo Filters::noXSS(L('delete')); ?></th></tr></thead>
      <?php foreach ($attachments as $attachment): ?>
      <tr>
        <td>
          <?php if (file_exists(BASEDIR . '/attachments/' . $attachment['file_name'])): ?>
          <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?getfile=<?php echo Filters::noXSS($attachment['attachment_id']); ?>" title="<?php echo Filters::noXSS($attachment['file_type']); ?>">
          <?php else: ?>
          <del>
          <?php endif; ?>
          <?php
          // Strip the mimetype to get the icon image name
          list($main) = explode('/', $attachment['file_type']);
           $imgdir = BASEDIR . "/themes/".Filters::noXSS($proj->prefs['theme_style'])."/mime/";
           $imgpath = Filters::noXSS($baseurl)."themes/".Filters::noXSS($proj->prefs['theme_style'])."/mime/";
          if (file_exists($imgdir.$attachment['file_type'] . '.png')):
          ?>
          <img src="<?php echo Filters::noXSS($imgpath); ?><?php echo Filters::noXSS($attachment['file_type']); ?>.png" alt="(<?php echo Filters::noXSS($attachment['file_type']); ?>)" title="<?php echo Filters::noXSS($attachment['file_type']); ?>" />
          <?php else: ?>
          <img src="<?php echo Filters::noXSS($imgpath); ?><?php echo Filters::noXSS($main); ?>.png" alt="" title="<?php echo Filters::noXSS($attachment['file_type']); ?>" />
          <?php endif; ?>
          &nbsp;&nbsp;<?php echo Filters::noXSS($attachment['orig_name']); ?>

          <?php if (file_exists(BASEDIR . '/attachments/' . $attachment['file_name'])): ?>
          </a>
          <?php else: ?>
          </del>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($attachment['file_size'] < 1000000): ?>
          <?php echo Filters::noXSS(round($attachment['file_size']/1024,1)); ?> <?php echo Filters::noXSS(L('KiB')); ?>

          <?php else: ?>
          <?php echo Filters::noXSS(round($attachment['file_size']/1024/1024,2)); ?> <?php echo Filters::noXSS(L('MiB')); ?>

          <?php endif; ?>
        </td>
        <td>
          <input type="checkbox" <?php echo tpl_disableif(!$user->perms('delete_attachments')); ?> name="delete_att[]" value="<?php echo Filters::noXSS($attachment['attachment_id']); ?>" />
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
