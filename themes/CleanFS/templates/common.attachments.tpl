  <?php
  if ($attachments && $user->can_view_task($task_details)): ?>
  <div class="attachments">
  <?php foreach ($attachments as $attachment): ?>
    <?php if (file_exists(BASEDIR . '/attachments/' . $attachment['file_name'])): ?>
    <a title="<?php echo Filters::noXSS($attachment['orig_name']); ?>" href="?getfile=<?php echo Filters::noXSS($attachment['attachment_id']); ?>" <?php if (substr($attachment['file_type'], 0, 5) == 'image'): ?>rel="lightbox[bug]"<?php endif; ?>>
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
      &nbsp;&nbsp;
      <?php if (utf8_strlen($attachment['orig_name']) > 30): ?>
      <?php echo Filters::noXSS(utf8_substr($attachment['orig_name'], 0, 29)); ?>...
      <?php else: ?>
      <?php echo Filters::noXSS($attachment['orig_name']); ?>

      <?php endif; ?>
      <?php if (file_exists(BASEDIR . '/attachments/' . $attachment['file_name'])): ?>
      </a>
      <?php else: ?>
      </del>
      <?php endif; ?>
      <?php if ($attachment['file_size'] < 1000000): ?>
      (<?php echo Filters::noXSS(round($attachment['file_size']/1024,1)); ?> <?php echo Filters::noXSS(L('KiB')); ?>)
      <?php else: ?>
      (<?php echo Filters::noXSS(round($attachment['file_size']/1024/1024,2)); ?> <?php echo Filters::noXSS(L('MiB')); ?>)
      <?php endif; ?>
    <br />
  <?php endforeach; ?>
  </div>
  <?php elseif (count($attachments)): ?>
  <div class="attachments"><?php echo Filters::noXSS(L('attachnoperms')); ?></div>
  <?php endif; ?>
