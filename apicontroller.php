<?php
session_start();
require_once("lib/rest.php");
require_once("lib/validateparam.php");
/**
 * This class - API is a simple class to implement the REST api server.
 *
 * @copyright  
 * @license    
 * @version    
 * @link       
 * @since      
 * @author: Thang Nguyen
 */

class API extends REST {
    private $res;
    private $variables;
    private $demoUser = 'demo';
    private $demoPassword = 'demo';
    private $session = false;
    
    public function __construct(){
        parent::__construct();
        $this->res = self::initResult();
        $this->variables = $this->_request;
    }
    
    /**
     * Get and set the session for authenticated
     * @param string $token
     * @return string $this->session
     */
    public function session($token = false)
    {
        if ($token === false) {
            $this->session = $_SESSION['token'];
            return $this->session;
        } else {
            $_SESSION['token'] = $token;
            $this->session = $token;
            return $this->session;
        }
    }

    /**
     * Set the default response
     * @return array
     */
    public static function initResult()
    {
        return array(
                'status' => false,
                'message' => 105,
                'response' => null
            );
    }
    
    /**
     * Check if the request is authenticated or not
     * @param string $token
     * @return boolean/json string
     */
    public function isAuthenticated ($token)
    {
        $validToken = $this->validToken($token);
        if ($validToken) {
            return true;
        } else {
            $this->response($this->res,102);
            $this->json();
        }
    }

    /*
     * Public method for access api.
     * This method dynmically call the method based on the query string
     *
     */
    public function processApi($token = false)
    {
        if (isset($_REQUEST['rquest'])) {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
        if ((int)method_exists($this,$func) > 0) {
            if ($func != 'login') {
                $isAuth = false;
                if($token !== false) {
                    $isAuth = $this->isAuthenticated($token);
                    if ($isAuth) {
                        $isAuth = true;
                        $this->$func();
                    }
                }
                if ($isAuth == false) {
                    $this->response($this->res,102);
                }
            } else {
                $this->$func();
            }
        } else {
            $this->response($this->res,404);
        }
        } else {
        $this->response($this->res,101);
        }
        return $this->json();
    }

    /**
     * Encode array into JSON
     */
    private function json()
    {
        if (isset($this->res) && is_array($this->res)) {
            echo json_encode($this->res);
        }
    }
    public function generateToken()
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $APIKey = 'bb53485fac9818a01c95388e57401869';
        $nonce = md5($date->format('U'));
        $created = $date->format('c');
        $secure = substr(base64_encode(md5($nonce.$created.$APIKey, true)), 0, 22);
        return $secure;
    }

    /**
     * Validate token
     * @param type $token
     * @return boolean
     */
    public function validToken($token)
    {
        $APIKey = 'bb53485fac9818a01c95388e57401869';
        $valid = false;
        $dateNow = new DateTime('now', new DateTimeZone('UTC'));
        $timeNow = $dateNow->format('U');
        // Check with gennerated token within 10 minutes
        for ($i = 0; $i < 3000; $i++) {
            $moment = $timeNow - $i;
            $nonce = md5($moment);
            $dateNow->setTimestamp($moment);
            $created = $dateNow->format('c');
            $secureToVerify =substr(base64_encode(md5($nonce.$created.$APIKey, true)), 0, 22);
            if ($secureToVerify == $token){
                $valid = true;
                break;
            }
        }
        return $valid;
    }

    /**
     *	Simple login API
     *  Login must be POST method
     *  username : the user name
     *  password : password
     */
    private function login()
    {
        $reponseData = null;
        $validateParam = new ValidateParam($this->variables);
        $userName = $validateParam->checkUserName();
        $password = $validateParam->checkPassword();
        if ($userName !== false && $password !== false) {
            // Implement simple check authenticated with demo account. And then, generate a token for the next request.
            if ($userName == $this->demoUser && $password == $this->demoPassword) {
                // Generate the token
                $token = $this->generateToken();
                $this->session($token);
                $this->res['status'] = true;
                $reponseData = array('userName' => $this->demoUser, 'token' => $token);
                $this->response($this->res);
            } else {
                // authenticated fail
                $this->response($this->res,102);
            }
        } else {
            // Param missing
            $this->response($this->res,103);
        }
        $this->res['response'] = $reponseData;
        return $this->res;
    }

    // Domain functions stuff

    private function domainChecker()
    {
        $reponseData = null;
        $validateParam = new ValidateParam($this->variables);
        $domainName = $validateParam->checkDomainName();
        if ($domainName === false) {
            // Invalid params or missing params
            $this->response($this->res,103);
            return $this->res;
        }
        if ($domainName === null) {
            $domainName = 'google.com';
        }
        $checkAvail = $this->checkDomainAvailability($domainName);
        if($checkAvail['status']) {
            $response = array('success'=>true,'response'=>array('domain'=>$domainName,'message'=>'Domain : '.$domainName.' is Available'));
        } else {
            $getDomainInfo = array();
            $getDomainInfo = $this->getDomainInfo($domainName);
            if($getDomainInfo['status']) {
                $response = $getDomainInfo['response'];
                array_push($response,$checkAvail['response']);
            }
            $this->res['status'] = true;
            $this->res['response'] = $response;
            $this->response($this->res, 106, array($domainName));
            return $this->res;
        }
        $this->res['status'] = true;
        $this->res['response'] = $response;
        $this->response($this->res, 105);
        return $this->res;
    }
    
    /**
     * Check if the given domain is available or not
     * @param string $domainName
     * @return array
     */
    private function checkDomainAvailability($domainName)
    {
        if (checkdnsrr($domainName, 'ANY')) {
            $dnsr = dns_get_record($domainName);
            return array('status' => false, 'response' => $dnsr);
        } else {
            return array('status' => true, 'response' => null);
        }
    }
    
    /**
     * Get the basic information of the registered domain
     * @param string $domainName
     * @return arrray
     */
    private function getDomainInfo ($domainName)
    {
        $server = 'whois.crsnic.net';
        // Open a socket connection to the whois server
        $connection = fsockopen($server, 43);
        if (!$connection) {
            return array('status' => false, 'response' => null);
        }
        // Send the requested doman name
        fputs($connection, $domainName."\r\n");
        // Read and store the server response
        $response_text = ' :';
        while (!feof($connection)) {
            $response_text .= fgets($connection,128);
        }
        // Close the connection
        fclose($connection);

        $separator = "\r\n";
        $line = strtok($response_text, $separator);
        $updateDate = null;
        $creationDate = null;
        $expiredDate = null;

        while ($line !== false) {
            # do something with $line
            $line = strtok( $separator );
            if (strpos($line, 'Updated Date')) {
                $updateDate = trim($line,'Updated Date:') ;
            }
            if (strpos($line, 'Creation Date')) {
                 $creationDate = trim($line,'Creation Date:') ;
            }
            if (strpos($line, 'Expiration Date')) {
                $expiredDate = trim($line,'Expiration Date:') ;
            }
            $response_text = trim($response_text,$line);
        }
        return array('status' => true, 'response' => array('creationDate' => $creationDate, 'updateDate' => $updateDate, 'expiredDate' => $expiredDate));
    }
}

// Initiate implement the usage of api class
// $token : it's the random token of login api. The token will be expired within 10 minutes
$token = '7wlbpaAwY2MZ6LzPTLkgLA';
$api = new API;
$api->processApi($token);
?>