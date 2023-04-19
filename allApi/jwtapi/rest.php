<?php
	require_once('constant.php');
  header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access");
    header("Access-Control-Allow-Methods: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
class Rest {
    protected $request;
    protected $serviceName;
    protected $param;
    protected $dbConn;
    protected $userId;
    public function __construct() {
        $method = $_SERVER['REQUEST_METHOD'];
switch($method){ 
        case "GET":
            //$handler = fopen('php://input', 'r');
            //$this->request = stream_get_contents($handler);
            $this->request = file_get_contents('php://input');
            $this->request = array("name" => $_GET['name'], "param" => array("customerId"=>$_GET['customerId']),);
            $this->getvalidateRequest();
            
            $db = new DBConnection;
            $this->dbConn = $db->connect();;
    
            if( 'generate' != strtolower( $this->serviceName) ) {
                $this->validateToken();}
        break;
        case "POST":
            $handler = fopen('php://input', 'r');
            $this->request = stream_get_contents($handler);
            $this->validateRequest();
            
            $db = new DBConnection;
            $this->dbConn = $db->connect();;
    
            if( 'generate' != strtolower( $this->serviceName) ) {
                $this->validateToken();}
        //print_r($data);
        break;
        default:
        $this->throwError(REQUEST_METHOD_NOT_VALID, 'Request Method is not valid.');
}
    }
        Public function getvalidateRequest() {
        if(/*$_SERVER['CONTENT_TYPE'] !== 'application/json'*/ header("Content-Type: application/json; charset=UTF-8")) {
            $this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request content type is not valid');
        }

        $data = $this->request;//json_decode($this->request, true);

        if(!isset($data['name']) || $data['name'] == "") {
            $this->throwError(API_NAME_REQUIRED, "API name is required.");
        }
        $this->serviceName = $data['name'];

        if(!is_array($data['param'])) {
            $this->throwError(API_PARAM_REQUIRED, "API PARAM is required.");
        }
        $this->param = $data['param'];
    }
    Public function validateRequest() {
        if($_SERVER['CONTENT_TYPE'] !== 'application/json' /*header("Content-Type: application/json; charset=UTF-8")*/) {
            $this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request content type is not valid');
        }

        $data = json_decode($this->request, true);

        if(!isset($data['name']) || $data['name'] == "") {
            $this->throwError(API_NAME_REQUIRED, "API name is required.");
        }
        $this->serviceName = $data['name'];

        if(!is_array($data['param'])) {
            $this->throwError(API_PARAM_REQUIRED, "API PARAM is required.");
        }
        $this->param = $data['param'];
    }
    public function validateParameter($fieldName, $value, $dataType, $required = true) {
        if($required == true && empty($value) == true) {
            $this->throwError(VALIDATE_PARAMETER_REQUIRED, $fieldName . " parameter is required.");
        }

        switch ($dataType) {
            case BOOLEAN:
                if(!is_bool($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be boolean.');
                }
                break;
            case INTEGER:
                if(!is_numeric($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be numeric.');
                }
                break;

            case STRING:
                if(!is_string($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be string.');
                }
                break;

            default:
                $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName);
                break;
        }

        return $value;

    }
    public function validateToken() {
        try {
            $token = $this->getBearerToken();
            $payload = JWT::decode($token, SECRETE_KEY, ['HS256']);

            $sql = "SELECT * FROM register where id = '$payload->userId' ";
            $stmt = $this->dbConn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $outp = $result->fetch_all(MYSQLI_ASSOC);
            if(!is_array($outp)) {
                $this->returnResponse(INVALID_USER_PASS, "This user is not found in our database.");
            }

            if( $outp[0]['active'] == 0 ) {
                $this->returnResponse(USER_NOT_ACTIVE, "This user may be decactived. Please contact to admin.");
            }
            $this->userId = $payload->userId;
        } catch (Exception $e) {
            $this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
        }
    }
    public function processApi() {
        try {
            $api = new API;
            $rMethod = new reflectionMethod('API', $this->serviceName);
            if(!method_exists($api, $this->serviceName)) {
                $this->throwError(API_DOST_NOT_EXIST, "API does not exist.");
            }
            $rMethod->invoke($api);
        } catch (Exception $e) {
            $this->throwError(API_DOST_NOT_EXIST, "API does not exist.");
        }

    }
    public function throwError($code, $message) {
        header("content-type: application/json");
        $errorMsg = json_encode(['error' => ['status'=>$code, 'message'=>$message]]);
        echo $errorMsg; exit;
    }
    public function returnResponse($code, $data) {
        header("content-type: application/json");
        $response = json_encode([['resonse' => ['status' => $code, "result" => $data]]]);
        echo $response; exit;
    }
    public function returnResponseApi($data) {
        header("content-type: application/json");
        $response = json_encode([$data]);
        echo $response; exit;
    }
    public function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        $this->throwError( ATHORIZATION_HEADER_NOT_FOUND, 'Access Token Not found');
    }
    }
?>