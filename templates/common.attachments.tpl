  <?php
  if ($attachments && $user->can_view_task($task_details)): ?>
  <div class="attachments">
  <?php foreach ($attachments as $attachment): ?>
    <?php if (file_exists(BASEDIR . '/attachments/' . $attachment['file_name'])): ?>
    <a title="{$attachment['orig_name']}" href="?getfile={$attachment['attachment_id']}" <?php if (substr($attachment['file_type'], 0, 5) == 'image'): ?>rel="lightbox[bug]"<?php endif; ?>>
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
      &nbsp;&nbsp;
      <?php if (utf8_strlen($attachment['orig_name']) > 30): ?>
      {utf8_substr($attachment['orig_name'], 0, 29)}...
      <?php else: ?>
      {$attachment['orig_name']}
      <?php endif; ?>
      <?php if (file_exists(BASEDIR . '/attachments/' . $attachment['file_name'])): ?>
      </a>
      <?php else: ?>
      </del>
      <?php endif; ?>
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

