<?php
/*
 * This file shows each individual selenium test. The template file name is passed 
 * through $_GET['template']
 * 
 */
require_once 'Savant2.php';
$tpl = new Savant2(array('resource_path' => array('savant2_plugins'),
                         'template_path' => array('flyspray_tests')));
$template = empty ($_GET['template']) ? '' : $_GET['template'];
$res = $tpl->display($template);
if ($tpl->isError($res)) {
    echo "Big fat template error. <pre>";
    print_r($res);
    echo "</pre>";
}
?>