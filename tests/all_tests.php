<?php
require_once 'Savant2.php';
define('TEMPLATE_DIR','flyspray_tests');  // The directory where the tests are stored

$tpl = new Savant2(array ('resource_path' => array (dirname(__FILE__).'/savant2_plugins')));
	
// Find all the files in the TEMPLATE_DIR
$tests = array();
if ($dh = opendir(TEMPLATE_DIR)) {
   while (($file = readdir($dh)) !== false) {
      if (is_file(TEMPLATE_DIR .'/'. $file)) {
        $tests[] = $file;
      }
   }
   closedir($dh);
} else {
  die('Could not open directory ' . TEMPLATE_DIR);  
}
// Sort the list of files
sort($tests);
$tpl->assign('tests', $tests);
// all_tests.tpl.php builds a simple one column html table with ahref links
// to all the tests. Each test is defined in a savant2 template in TEMPLATE_DIR
// the test is displayed by calling the test_renderer.php?template=TEMPLATE_NAME
$template = 'all_tests.tpl.php';
$res = $tpl->display($template);
if ($tpl->isError($res)) {
   echo "Big fat template error. <pre>";
   print_r($res);
   echo "</pre>";
}
?>




