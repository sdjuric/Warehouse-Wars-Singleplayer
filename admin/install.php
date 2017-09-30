
<?php
require_once "model/database.php";

//Setup information courtesy of http://php.net/manual

class Setup{
	public $access;
	
	function __construct(){
		//Please change the information here to your own
		//The first field is your database username
		//The second field if your database password
		$database=new Database("REMOVED", "REMOVED");

		$this->access=$database;

		
	}
}
?>