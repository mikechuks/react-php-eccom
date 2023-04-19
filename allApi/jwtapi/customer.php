<?php 
	class Customer {
		private $id;
		private $coin;
		private $state;
		private $company;
		private $service;
		private $purchaseAmount;  
		private $phoneNumber; 
		private $coinImage;       
		private $updatedBy;
		private $createdBy;
		private $tableName = 'edit';
		private $dbConn;
		public  $coinOne;

		function setId($id) { $this->id = $id; }
		function getId() { return $this->id; }
		function setCoin($coin) { $this->coin = $coin; }
		function getCoin() { return $this->coin; }
		function setState($state) { $this->state = $state; }
		function getState() { return $this->state; }
		function setCompany($company) { $this->company = $company; }
		function getCompany() { return $this->company; }
		function setService($service) { $this->service = $service; }
		function getService() { return $this->service; }
        function setPurchaseAmount($purchaseAmount) { $this->purchaseAmount = $purchaseAmount; }
		function getPurchaseAmount() { return $this->purchaseAmount; }
		function setPhoneNumber($phoneNumber) { $this->phoneNumber = $phoneNumber; }
		function getPhoneNumber() { return $this->phoneNumber; }
		function setCoinImage($coinImage) { $this->coinImage = $coinImage; }
		function getCoinImage() { return $this->coinImage; }
		function setUpdatedBy($updatedBy) { $this->updatedBy = $updatedBy; }
		function getUpdatedBy() { return $this->updatedBy; }
		function setCreatedBy($createdBy) { $this->createdBy = $createdBy; }
		function getCreatedBy() { return $this->createdBy; }

		public function __construct() {
			$db = new DBConnection();
			$this->dbConn = $db->connect();
		}


		public function insert() {
				$sql = 'INSERT INTO ' . $this->tableName . '(coin,state,company,service,purchaseAmount,phoneNumber,coinImage,CreatedBy)values(?,?,?,?,?,?,?,?)';
				$stmt = $this->dbConn->prepare($sql);
				$stmt->bind_param("ssssssss", $this->coin,$this->state,$this->company,$this->service,$this->purchaseAmount,$this->phoneNumber,$this->coinImage,$this->createdBy);
				$result = $stmt->execute();
			if($result) {
				return true;
			} else {
				return false;
			}
		}
		public function getCustomerDetailsById() {
			$sql = "SELECT id, coin,state,company,service,purchaseAmount,phoneNumber,coinImage,CreatedBy FROM $this->tableName where CreatedBy = '$this->id'";
			$stmt = $this->dbConn->prepare($sql);
			$stmt->execute();
			$result = $stmt->get_result();
			$outp = $result->fetch_all(MYSQLI_ASSOC);
			return $outp;
		}

		/*public function update() {

			$sql = "UPDATE $this->tableName SET";
			if( null != $this->getName()) {
				$sql .=	" name = '" . $this->getName() . "',";
			}

			if( null != $this->getAddress()) {
				$sql .=	" address = '" . $this->getAddress() . "',";
			}

			if( null != $this->getMobile()) {
				$sql .=	" mobile = " . $this->getMobile() . ",";
			}

			$sql .=	" updated_by = :updatedBy, 
					  updated_on = :updatedOn
					WHERE 
						id = :userId";

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':userId', $this->id);
			$stmt->bindParam(':updatedBy', $this->updatedBy);
			$stmt->bindParam(':updatedOn', $this->updatedOn);
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}*//*

		public function delete() {
			$stmt = $this->dbConn->prepare('DELETE FROM ' . $this->tableName . ' WHERE id = :userId');
			$stmt->bindParam(':userId', $this->id);

			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}*/
	}
 ?>