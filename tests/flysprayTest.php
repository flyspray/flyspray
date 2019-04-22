<?php

use PHPUnit\Framework\TestCase;

class FlysprayTest extends TestCase{
  #private $pdo;
  private $db;
  
  # just taken as first test from github project travis-ci-examples/php
  protected function setUp(): void
  {
    #$this->pdo = new PDO($GLOBALS['db_dsn'], $GLOBALS['db_username'], $GLOBALS['db_password']);
    #$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    #$this->pdo->query("CREATE TABLE hello (what VARCHAR(50) NOT NULL)");
    $this->db = new Database;
    $this->db->dbOpen($GLOBALS['dbhost'], $GLOBALS['dbuser'], $GLOBALS['dbpass'], $GLOBALS['dbname'], $GLOBALS['dbtype'], $GLOBALS['dbprefix']);
    $this->db->query("CREATE TABLE {projects} (what VARCHAR(50) NOT NULL)");
  }
  public function tearDown(): void
  {
    #$this->pdo->query("DROP TABLE hello");
    $this->db->query("DROP TABLE {projects}");
  }
  
  public function testHelloWorld(){
    $helloWorld = 'Hello World';
    $this->assertEquals('Hello World', $helloWorld);
  }
  
  public function testTranslationSyntax(){
    if ($handle = opendir('lang')) {
      $languages=array();
      while (false !== ($file = readdir($handle))) {
        # exclude temporary files from onsite translations
        if ($file != "." && $file != ".." && !(substr($file,-4)=='.bak') && !(substr($file,-5)=='.safe') ) {
          $langfiles[]=$file;
        }
      }
    }

    foreach($langfiles as $lang){
      $this->assertStringStartsWith('No syntax errors', shell_exec("php -l lang/$lang"));
    }
  }
}
?>
