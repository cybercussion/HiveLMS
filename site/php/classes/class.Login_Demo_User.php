<?php
/**
 * User is responsible for constructing a new user.
 * It will return the profile to the client or send a fail/error.
*/
class Login_Demo_User {
	public function __construct($data) {
		$email = $data->email;
		$password = md5($data->password);
		$demo     = $data->demo;
		// Check for Demo Mode, if so make a fake user
		if($demo == true) {
			// Construct Demo Account
			$username   = "guest_" . $this->generate_random_letters(5);
			$email      = $username . "@hivelms.com";
			$guid       = md5($email . $data->password);
			
			$modDate    = date("YmdHis");
    		$createDate = date("YmdHis");
    		// I'm taking this SQL Statement vertical so things can be added/removed with ease.  Watch your comma's, parenthesis and quotes! 
			$SQLInsert  = "INSERT INTO profile_accounts (" .
															"myGUID, " . 
															"myUsername, " .
															"myPassword, " .
															//"myFirstname, " .
															//"myLastname, " .
															//"myPhone, " .
															"myEmail, " .
															//"myDomain, " .
															//"myGender," .
															//"myAvatar, " .
															"myStatus, ".
															"myModDate, ".
															"myCreateDate) " . //end - no comma
															"VALUES ( " .
															"'$guid', " .
															"'$username', " .
															"'$password', " .
															//"'$firstname', " .
															//"'$lastname', " .
															//"'$phone', " .
															"'$email', " .
															//"'$domain', " .
															//"'$gender', " .
															//"'$avatar', " .
															"'1', " .
															"'$modDate', " .
															"'$createDate')";  //end - no comma
			$DB = new DB();
			if (!($DB->query($SQLInsert))) { 
				// Error
				$this->status = "error";
				$this->msg    = mysql_fetch_object($SQLInsert);
			} else {
				$user_id           = mysql_insert_id();
				// Validate the new user account returning the profile
				$data->username    = $username;
				$data->email       = $email;
				$data->guid        = $guid;
				$this->login       =  new Login_User_Validation($data);
				$this->status      = $this->login->__get('status'); // boil up
				// Next we need to assign some dummy course to this demo user.  For this case the Innovation Challenge.
				$courses = $this->getDemoCourses();
				foreach($courses as $course) {
					for($i=0; $i<count($course); $i++) {
						$data->order     = $i;
						$data->assign_id = $course[$i]['id']; // Little bit tripped out this is an array still
						//$this->assign_id = $course[$i]['id']; // debugging
						//$this->course    = $course;
						$data->status    = 1;
						$assignCourse    = new Assignment_Add($data);
					}
				}
				//$this->courses = $courses; // debugging
				// Now that the course is created, lets return the Assignments with the profile.
				$this->assignment = new Assignment_Get($data);
			}	
		} else {
			$this->status = "fail";
			$this->msg    = "Sorry, this was not a demo account.  Please alert an administrator.";
		}
        return $this;
    }
    
    private function getDemoCourses() {
    	$request->value = "Demo";
    	return new Course_Get($request);
    }
    
    private function generate_random_letters($length) {
		$random = '';
		for ($i = 0; $i < $length; $i++) {
			$random .= chr(rand(ord('a'), ord('z')));
		}
		return $random;
	}
	public function __get($name) {
		return $this->$name;
	}
	public function __set($name, $value) {
		return $this->$name = $value;
	}		
}
?>