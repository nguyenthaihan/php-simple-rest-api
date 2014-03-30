<?php
/**
 * Validate parameters were sent from the api request
 *
 * @copyright  
 * @license    
 * @version    
 * @link       
 * @since      
 * @author: Thang Nguyen
 */

class ValidateParam
{
    private $variables;
    public function __construct($variables)
    {
        $this->variables = $variables;
    }

    /**
     * check Int
     * @param Int $value
     * @return Bool
     */
    public function checkInt($param)
    {
        $result = false; 
        $value = $this->checkPostVariable($param);
        if ($value !== false) {
            if (ctype_digit($value) === false) {
                $result = false;
            } else {
                $result = $value;
            }
        }
        return $result;
    }
    
    /**
     * Check the input params if it is set or null/not null
     * @param string $param
     * @param boolean $acceptNull
     * @return boolean|null
     */
    public function checkInputVariable($param, $acceptNull = false)
    {
        $result = false;
        if (isset($this->variables[$param])) {
            $result = $this->variables[$param];
        } else {
            // send out a log message to confirm that this param is not set
        }
        if ($result === null) {
            if ($acceptNull === true) {
                return null;
            } else {
                // Send out a log message to confirm that this param is null
            }
            return false;
        }
        return $result;
    }
    
    /**
     * Validate the input username
     * @return string
     */
    public function checkUserName()
    {
        return $this->checkInputVariable('username');
    }
    
    /**
     * Validate the input domain name
     * @return string
     */
    public function checkDomainName()
    {
        return $this->checkInputVariable('domainName',true);
    }
    
    /**
     * Validate the password
     * @return string
     */
    public function checkPassword ()
    {
        return $this->checkInputVariable('password');
    }
}

?>