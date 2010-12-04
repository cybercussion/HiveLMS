<?php
/**
 * User is responsible for constructing a new user.
 * It will return the profile to the client or send a fail/error.
*/
class Login_User {
	public function __construct($data) {
		$this->login =  new Login_User_Validation($data);
	 	$this->assignment = new Assignment_Get($this->login->profile); // pass guid
		$this->status = $this->login->__get('status'); // boil up
		
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