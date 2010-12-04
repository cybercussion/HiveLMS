<?php
/**
 * Login is responsible for validating the username and password.
 * It will return the profile to the client or send a fail/error.
*/
class Login {
	public function __construct($data) {
		$email = $data->email;
		// Handle Demo Login
		if($email == "student@hivelms.com") {
			// Hand off to new User Class
			$data->demo     = true;
			$this->response = new Login_Demo_User($data);
		} else { // Handle normal login
			$this->response = new Login_User($data);
		}
		return $this;
    }
    public function __get($name) {
		return $this->$name;
	}
	public function __set($name, $value) {
		return $this->$name = $value;
	}	
}
?>