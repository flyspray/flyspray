<?php if ($links && $user->can_view_task($task_details)): ?>
  <div class="links">
   {L('referencelinks')}<br>
   <?php foreach ($links as $link): ?>
    <a href="{$link['url']}">{$link['url']}</a><br>
   <?php endforeach; ?>
  </div>
<?php elseif (count($links)): ?>
  <div class="links">{L('linknoperms')}</div>
<?php endif; ?>

