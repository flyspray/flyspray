<?php $this->display('shortcuts.tpl'); ?>
</div>
<?php if(isset($general_integration)): echo $general_integration; endif; ?>
<div id="footer">
  <?php if(isset($footer_integration)): echo $footer_integration; endif; ?>    
  <!-- Please don't remove this line - it helps promote Flyspray -->
  <a href="https://www.flyspray.org/" class="offsite"><?= eL('poweredby'); ?><?php if ($user->perms('is_admin')): ?> <?php echo Filters::noXSS($fs->version); ?> <?php endif; ?></a>
</div>
</body>
</html>
