<?php
	/**
	 * Disclaimer:
	 * I thought about implementing REST, but its far too complicated.  Server seems to need to be 
	 * tweaked to route calls and handling binary uplaods looks challenging.  I prefer my old approach.
	 * Its straight forward and based on actions.  Verbs are nice, but the pain of trying to read
	 * tutorials online for how to implement a REST API and manage it are literally mind numbing.  Tens
	 * if not hundreds of crop up frameworks and its fairly unclear how to ambigous how to implmeent.
	 * I'm going ot put a twist on it though and try and stream line how things work.
	 *
	*/
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');
	// result will endup having a object called "response" from the class that handles it.
	$result;
	
	/////////////////////////////// ERROR HANDLING //////////////////////////////////////
	/**
	 * New Error handling to manage the messages in JSON Strings.
	 * Could also log errors here, send emails to admins etc ...
	 * Keep in mind http://se.php.net/manual/en/function.set-error-handler.php
	 * @usage: trigger_error('Database connection failed.', FATAL);	
	*/
	register_shutdown_function('fatalErrorShutdownHandler');
	error_reporting(E_ALL);
	ini_set('display_errors', 1); // disable this later to hide string based errors
	ini_set('html_errors', 0);
	//ini_set('error_prepend_string', '{"status": "error", "msg":"');
	//ini_set('error_append_string', '"}');
	define('FATAL', E_USER_ERROR);
	define('ERROR', E_USER_WARNING);
	define('WARNING', E_USER_NOTICE);
	$errorHandler = new ErrorHandler(1); // 1 = ON Clear messaging, 0 = OFF Generic fault message
	
	/**
	 * In an attempt to trap that shutdown error I'll handle it here
	*/
	function fatalErrorShutdownHandler() {
		$last_error = error_get_last();
		if ($last_error['type'] === E_ERROR) {
			// fatal error
			$response->status = "fatal";
        	$response->error;
        	$response->error->errorType = E_ERROR;
        	$response->error->errorLine = $last_error['line'];
        	$response->error->errorFile = $last_error['file'];
        	$response->error->errorString = $last_error['message'];
        	echo(json_encode($response));
		}
	}
	
	//////////////////////////////// END ////////////////////////////////////////////////
	
    /////////////////////////// DYNAMIC CLASSES /////////////////////////////////////////
    /**
	 * This is the autoload that will only attach a class into the processing of a message
	 * If it is used.  This keeps us from having to repeatedly link includes or classes.
	 * In this case I'd like to get away from includes and try to use Objects.  Because of this
	 * I'm going to use the functionality in PHP to only load classes when they are needed.
	 * All classes used in this project must be named class.[name].php
	*/
	
	function __autoload($class_name) {
		$requestedFile = 'classes/class.' . $class_name . '.php';
		if(is_file($requestedFile)) {
			require_once $requestedFile;
		} else {
			trigger_error("The requested Class " . $class_name . " is not found.", FATAL);
		}
	}
    
    /**
	 * Before in the past I had this switch statement where it all got broken down by each case.
	 * In this instance I'm going to just dynamically reference a class, and eco the result.
	 * Technically I should manage the errors that can occur in the app so they are not just being
	 * passed has HTML strings when I'm trying to send JSON strings.
	*/
  	try {
		$data = json_decode(str_replace("\\", "", $_POST['data']));
		if($data == "") {
			trigger_error('Sorry, no public view available.', FATAL);
		}
		$result = new $data->action($data);
	} catch (Exception $e) {
		$result->response->status = "error";
		$result->response->msg = $e->getMessage();
	}
	//$result->response->type = gettype($result->response);
	echo(json_encode($result->response));
	//////////////////////////////// END /////////////////////////////////////////////////
?>