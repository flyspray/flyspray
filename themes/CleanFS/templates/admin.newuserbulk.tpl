<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('admintoolbox')); ?> :: <?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('newuserbulk')); ?></h3>

    <?php
    $this->display('common.newuserbulk.tpl');
    ?>
</div>
