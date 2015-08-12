<!-- add DC 19/02/2015 -->
<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('customsfieldsaffec')); ?></h3>
<?php
    //LISTS AFFECT
    $this->assign('list_type', 'fields');
    
  	$this->assign('rows', $proj->listfields($proj->id));
  	
  	//AVAILABLE LISTS ( CATEGORY ) 
  	$this->assign('rowslistsavble', $proj->listsrowslistsavble($proj->id));
  	
    $this->display('common.customsfields.tpl');
?>
</div>