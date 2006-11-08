  <?php
  if ($attachments && $user->can_view_task($task_details)): ?>
  <div class="attachments">
  <?php foreach ($attachments as $attachment): ?>
    <a href="?getfile={$attachment['attachment_id']}" title="{$attachment['file_type']}">
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
      &nbsp;&nbsp;{$attachment['orig_name']}</a>
      <?php if ($attachment['file_size'] < 1000000): ?>
      ({round($attachment['file_size']/1024,1)} {L('KiB')})
      <?php else: ?>
      ({round($attachment['file_size']/1024/1024,2)} {L('MiB')})
      <?php endif; ?>
    <br />
  <?php endforeach; ?>
  </div>
  <?php elseif (count($attachments)): ?>
  <div class="attachments">{L('attachnoperms')}</div>
  <?php endif; ?>
  
