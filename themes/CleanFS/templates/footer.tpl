<?php $this->display('shortcuts.tpl'); ?>
</div>
<p id="footer">
    <!-- Please don't remove this line - it helps promote Flyspray -->
    <a href="http://flyspray.org/" class="offsite"><?php echo Filters::noXSS(L('poweredby')); ?><?php if ($user->perms('is_admin')): ?> <?php echo Filters::noXSS($fs->version); ?> <?php endif; ?></a>
</p>
</body>
</html>
