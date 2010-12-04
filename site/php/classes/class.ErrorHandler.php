<?php
/**
 * Ok, because I'm using JSON I don't want to get stuck with Strings coming back when I'm trying to post messages to the UI.
 * Below is a conversion from http://vailo.wordpress.com/2008/07/02/the-php-error-handler-class/ to JSON.
 *
*/
class ErrorHandler {
    private $debug = 0;
	private $response;
    public function __construct($debug = 0) {
        $this->debug = $debug;
        set_error_handler(array($this, 'handleError'), E_ALL);
    }

    public function handleError($errorType, $errorString, $errorFile, $errorLine) {
    	//$this->$response;
        switch ($errorType) {
            case FATAL:
	            switch ($this->debug) {
					case 0:
	                    $this->response->status = "fatal";
		            	$this->response->error->errorString = "An Error has occurred in PHP, enable debug to troubleshoot it.";
		            	echo(json_encode($this->response));
	                    exit;
	                case 1:
	                    $this->response->status = "fatal";
		            	$this->response->error->errorType = $errorType;
		            	$this->response->error->errorLine = $errorLine;
		            	$this->response->error->errorFile = $errorFile;
		            	$this->response->error->errorString = $errorString;
		            	echo(json_encode($this->response));
	                    exit;
	            }
            case ERROR:
            	$this->response->status = "error";
            	$this->response->error->errorType = $errorType;
            	$this->response->error->errorLine = $errorLine;
            	$this->response->error->errorFile = $errorFile;
            	$this->response->error->errorString = $errorString;
            	echo(json_encode($this->response));
	            break;
            case WARNING:
	            $this->response->status = "warning";
            	$this->response->error->errorType = $errorType;
            	$this->response->error->errorLine = $errorLine;
            	$this->response->error->errorFile = $errorFile;
            	$this->response->error->errorString = $errorString;
            	echo(json_encode($this->response));
	            break;
        }
    }
} 
?>