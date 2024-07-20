<?php if (count($links)): ?>
<div class="links">
<?php if ($user->can_view_task($task_details)): ?>
	<p><?php echo Filters::noXSS(L('referencelinks')); ?></p>
	<?php foreach ($links as $link): ?>
	<p><span class="fas fa-link"></span><a href="<?php echo Filters::noXSS($link['url']); ?>"><?php echo Filters::noXSS($link['url']); ?></a></p>
	<?php endforeach; ?>
<?php else: ?>
	<?php echo Filters::noXSS(L('linknoperms')); ?>
<?php endif; ?>
</div>
<?php endif; ?>
