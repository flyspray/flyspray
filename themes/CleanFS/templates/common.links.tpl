<?php if ($links && $user->can_view_task($task_details)): ?>
  <div class="links">
   <p><?php echo Filters::noXSS(L('referencelinks')); ?></p>
   <?php foreach ($links as $link): ?>
    <p><span class="fas fa-link"></span> <a href="<?php echo Filters::noXSS($link['url']); ?>"><?php echo Filters::noXSS($link['url']); ?></a></p>
   <?php endforeach; ?>
  </div>
<?php elseif (count($links)): ?>
  <div class="links"><?php echo Filters::noXSS(L('linknoperms')); ?></div>
<?php endif; ?>
