<?php if ($links && $user->can_view_task($task_details)): ?>
  <div class="links">
   <?php echo Filters::noXSS(L('referencelinks')); ?><br>
   <?php foreach ($links as $link): ?>
    <a href="<?php echo $link['url']; ?>"><?php echo $link['url']; ?></a><br>
   <?php endforeach; ?>
  </div>
<?php elseif (count($links)): ?>
  <div class="links"><?php echo Filters::noXSS(L('linknoperms')); ?></div>
<?php endif; ?>
