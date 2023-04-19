<?php
	/**
	* Database Connection
	*/
	class DBConnection {



		private $server = 'localhost';
		private $dbname = 'littleproject';
		private $user = 'root';
		private $pass = '';

		public function connect() {
			try {
	//Create connection
    $con = new mysqli($this->server, $this->user, $this->pass,$this->dbname);
    //Check connection
    if($con->connect_error){
            die("Connection failed:" .$con->connect_error);
    }
	return $con;
			} catch (\Exception $e) {
				echo "Database Error: " . $e->getMessage();
			}
		}
	}
?>