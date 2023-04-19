<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access");
    header("Access-Control-Allow-Methods: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
class Api extends Rest {
    public $dbConn;
    public $logData;
    	public function __construct() {
        parent::__construct();
    }
    public function generate() {
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $pass = $this->validateParameter('pass', $this->param['pass'], STRING);
        $userlogin = true;
        $passlogin = true;
        $userFalse = false;
        try {
            //Login
        if($email == $userlogin && $pass == $passlogin){
                   $sql = "SELECT * FROM register where user = '$email' AND password = '$pass'";
                    $stmt = $this->dbConn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $outp = $result->fetch_all(MYSQLI_ASSOC);
            if(!is_array($outp)) {
                $this->returnResponse(INVALID_USER_PASS, "Email or Password is incorrect.");
            }
            if($outp[0]['active'] == 0) {
                $this->returnResponse(USER_NOT_ACTIVE, "User is not activated. Please contact to admin.");
            }

            $paylac = [
                'iat' => time(),
                'iss' => 'localhost',
                'exp' => time() + (1440*60),
               'userId' => $outp[0]['id']
            ];

            $tokentwo = JWT::encode($paylac, SECRETE_PINS);
            //$data = ['token' => $token];
            //$this->returnResponse(SUCCESS_RESPONSE, $data);
            // echo json_encode($outp);
            $datatwo = ['pintwo' => $tokentwo];
           $falseToken = "Pins Expired";
           if($datatwo['pintwo'] == $userFalse){
            $this->throwError(ACCESS_TOKEN_ERRORS, $falseToken);
            }else{
            //$this->returnResponse(SUCCESS_RESPONSE, $data);
                     $paylod = [
                    'iat' => time(),
                    'iss' => 'localhost',
                    'exp' => time() + (45*60),
                    'userId' => $outp[0]['id']
                ];
                
                $token = JWT::encode($paylod, SECRETE_KEY);
                $data = ['pin' => $token, $datatwo, 'id' =>$outp[0]['id'], 'user' =>$outp[0]['user'], 'email' =>$outp[0]['email'], 'password' =>$outp[0]['password'], 'address' =>$outp[0]['address'], 
                'genders' =>$outp[0]['genders'], 'active' =>$outp[0]['active']];
                //$this->custData = $data;
                //$this->getAllDetails();
                $this->returnResponseApi($data);
            }
        }} catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
        }
    }
    public function addCustomer() {
        $coin = $this->validateParameter('coin', $this->param['coin'], STRING, false);
        $state = $this->validateParameter('state', $this->param['state'], STRING, false);
        $company = $this->validateParameter('company', $this->param['company'], STRING, false);
        $service = $this->validateParameter('service', $this->param['service'], STRING, false);
        $purchaseAmount = $this->validateParameter('purchaseAmont', $this->param['purchaseAmont'], STRING, false);
        $phoneNumber = $this->validateParameter('phoneNumber', $this->param['phoneNumber'], STRING, false);
        $coinImage = $this->validateParameter('coinImage', $this->param['coinImage'], STRING, false);
        try{
         $cust = new Customer;
        $cust->setCoin($coin);
        $cust->setState($state);
        $cust->setCompany($company);
        $cust->setService($service);
        $cust->setPurchaseAmount($purchaseAmount);
        $cust->setPhoneNumber($phoneNumber);
        $cust->setCoinImage($coinImage);
        $cust->setCreatedBy($this->userId);
        
        if(!$cust->insert()) {
            $message = 'Failed to insert.';
        } else {
            $message = "Inserted successfully.";
        }
        
        $this->returnResponse(SUCCESS_RESPONSE, $message);
        }catch(Exception $e) {
            $this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
        }
    }
    public function getCustomerDetails() {
        $customerId = $this->validateParameter('customerId', $this->param['customerId'], INTEGER);

        $cust = new Customer;
        $cust->setId($customerId);
        $customer = $cust->getCustomerDetailsById();
       // $this->logData = $customer;
       // $this->getAllDetails();
        //$costomerData = $this->generate();
        //if($costomerData == true){
            $this->returnResponseApi($customer);
        //}
        if(!is_array($customer)) {
            $this->returnResponse(SUCCESS_RESPONSE, ['message' => 'Customer details not found.']);
        }
    }
  /* public function getAllDetails() {
        $allData = [['reply' => [['custMessage' =>$this->logData]]]];
        echo json_encode($allData); exit;
    }*/
}
?>