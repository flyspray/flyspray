<div id="pma_wrapper">
<?php
$this->display('admin.menu.tpl');
if ($area != 'translations') {
	$this->display('admin.'.$area.'.tpl');
} else {
	$this->display('admin.translation.tpl');
}
?>
</div>
