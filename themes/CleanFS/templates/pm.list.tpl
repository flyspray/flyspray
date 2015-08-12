<!-- add DC 08/2015 -->
<div id="toolbox">
<?php 
$proj->GetInfosPAramSession ($lists_id_g,$lists_name,$catlisttype_g);
//1=>L('basic'),2=>L('versions'), 3=>L('category')
switch ($catlisttype_g)
{
	case 1://BASIC
	//echo "<h3>".Filters::noXSS($_SESSION['lists_name']) ." : ".Filters::noXSS(L('customsfielded'))."</h3>";
	//echo "<h3>".Filters::noXSS($lists_name) ." : ".Filters::noXSS(L('customsfielded'))."</h3>";
	$this->assign('list_type', 'standard');
	$this->assign('rows', $proj->standardLists($lists_id_g));
	$this->display('common.standard.tpl');
	break;
	case 2://VERSION
	echo "<h3>".Filters::noXSS($proj->prefs['project_title']) ." : ".Filters::noXSS(L('verlisted'))."</h3>";	
    $this->display('common.version.tpl');	
	break;
	
	case 3://CATEGORY
	//echo "<h3>".Filters::noXSS($lists_name) ." : ".Filters::noXSS(L('catlisted'))."</h3>";
	//echo "<h3>".Filters::noXSS($proj->prefs['project_title']) ." : ".Filters::noXSS(L('catlisted'))."</h3>";	
	$this->display('common.catcustfields.tpl');
	break;
	default:
}
?>    
</div>