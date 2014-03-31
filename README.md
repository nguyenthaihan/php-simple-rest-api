php-simple-api
===================

This is the simple rest api implement

1. Files structure
- submit.html: the submit form, it can help developer to test the API by using browser.
- jquery.js: jquery library for submit form
- apicontroller.php: the file that contain the api class and and running that api class.
- .htaccess: url routing 
- Lib
    + message.php : get the message from message code.
    + rest.php: manage basic functions for rest calling
    + validateparam.php : handle the validation of inputs.
- Config
    + message
        . common.php : define the message code and message
        
2. Next release will:
- extends the class ValidateParam with class Exceptions. Then we can try catch inside functions
- Remove all variable such as $demoUsername, $demoPassword to configuration files.
- Remove using session to store authenticated to make this api work properly with the multi server. ( on multi server, we will have problem with session)
- Restructure the files,class to make the code more clearly, such as seperate input,output class 