<?php
session_start();

if(isset($_SESSION['selectedcomp'])){
	$_SESSION['selectedcomp'] = "eon_spc";
}else{
	$_SESSION['selectedcomp'] = "eon_spc";
}


header('Content-Type: application/json');

	class connector{
		public $host = "localhost";
		public $dbname = "eon_spc";
		public $name = "root";						
		public $pass = "";
		function connect(){
			$conn = mysqli_connect("$this->host", "$this->name", "$this->pass",$_SESSION['selectedcomp']);
			if (!$conn)
			{
				die('Could not connect: ' . mysqli_connect_error());
			}
			$conn->set_charset("utf8");
			return $conn;
		}
		
		 
	}

?>