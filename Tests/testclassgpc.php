class gpcTest extends PHPUnit_Framework_TestCase{
	public function test_Get_enum(){
    
    	$_GET['do']='';
    
    	$do = Get::enum('do', array('activity','index','roadmap'), 'roadmap');
    	$this->assertEquals('roadmap', $do);
    
    	$do = Get::enum('do', array('activity','index','roadmap'));
    	$this->assertEquals('activity', $do);
    
    	$do = Get::enum('do', array('activity','index','roadmap'), 'garbage');
    	$this->assertEquals('activity', $do);
    	
    	$_GET['do']='xxxgarbage';
    
    	$do = Get::enum('do', array('activity','index','roadmap'), 'roadmap');
    	$this->assertEquals('roadmap', $do);
    
    	$do = Get::enum('do', array('activity','index','roadmap'));
    	$this->assertEquals('activity', $do);
    
    	$do = Get::enum('do', array('activity','index','roadmap'), 'garbage');
    	$this->assertEquals('activity', $do);

	}
}
