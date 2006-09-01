<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-AU" xml:lang="en-AU">
<head>
  <title>Tests</title>
  <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
  <link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>
  <table class="mytable">
    <thead>
      <tr>
        <td>Testsuite</td>
      </tr>
    </thead>  
    <tbody>
<?php foreach ($this->tests as $test): ?>
      <tr>
        <td><a href="test_renderer.php?template=<?php $this->eprint($test); print '">';
               $name = substr($test,4); // Remove 100_
               $name = str_replace('_',' ',$name);
               $name = str_replace('.tpl.php','',$name);
               $this->eprint($name);?></a></td>
      </tr>
<?php endforeach; ?>
  </table>
</body>
