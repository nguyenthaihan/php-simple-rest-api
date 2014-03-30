<?php
require_once("lib/message.php");
/**
 * This class manage the response message
 *
 * @copyright  
 * @license    
 * @version    
 * @link       
 * @since      
 * @author: Thang Nguyen
 */

class REST {
    public $_content_type = "application/json";
    public $_request = array();
    private $_method = "";

    public function __construct()
    {
            $this->inputs();
    }
    
    /**
     * Manage the response of the api
     * @param array $data
     * @param string/int $messageCode
     * @param string $replacement - The replace of %s element of the defined message
     * @return array
     */
    public function response(&$data, $messageCode = null, $replacement = null)
    {
        if (isset($messageCode)) {
            $data['message'] = $messageCode;
        }
        if (isset($data['message'])) {
            if ($replacement != null ) {
                $data['message'] = Message::get($data['message'], $replacement);
            } else {
                $data['message'] = Message::get($data['message']);
            }
        }
        return $data;
    }
    
    /**
     * Get the request methog
     * @return string
     */
    public function get_request_method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Set the request variable
     */
    private function inputs()
    {
            switch($this->get_request_method()){
                    case "POST":
                            $this->_request = $this->cleanInputs($_POST);
                            break;
                    case "GET":
                            $this->_request = $this->cleanInputs($_GET);
                            break;
                    default:
                            $this->response('',406);
                            break;
            }
    }		

    /**
     * Clean the input data
     * @param array/string $data
     * @return string/array
     */
    private function cleanInputs($data)
    {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->cleanInputs($v);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $data = trim(stripslashes($data));
            }
            $data = strip_tags($data);
            $clean_input = trim($data);
        }
        return $clean_input;
    }
}	
?>