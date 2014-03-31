<?php
require_once("config/message/common.php");
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

class Message {
    private $message;
    private $returnMessage;

    public function __construct($messageCode) 
    {
        if (is_numeric($messageCode)) {
            $messageCode = (string) $messageCode;
        }
        $this->message = constant($messageCode);
    }

    /**
     * Get the message from a defined message code
     * @param string $replacement
     * @return string
     */
    public function message($replacement = null) 
    {
        if ($this->message) {
            if ($replacement!=null) {
                $returnMess = '';
                foreach ($replacement as $replaceItem) {
                    if ($returnMess == '') {
                        $returnMess = preg_replace(array("/%s/"), $replaceItem, $this->message, 1);
                    } else {
                        $returnMess = preg_replace(array("/%s/"), $replaceItem, $returnMess, 1);
                    }
                }
                $this->message = $returnMess;
                return $this->message;
            } else {
                return $this->message;
            }
        } else {
            $defaultMessageCode = 100;
            $this->message = constant($defaultMessageCode);
            return $this->message;
        }
    }

    /**
     * A static fucntion for easy to call from another class. This will call the message() function from this class
     * @param string $messageCode
     * @param string $replacement
     * @return string
     */
    public static function get($messageCode, $replacement = null) 
    {
        $newMess = new Message($messageCode);
        $returnMess = $newMess->message($replacement);
        return $returnMess;
    }
}
?>
