<?php
/**
 * Login is responsible for validating the username and password.
 * It will return the profile to the client or send a fail/error.
 * @param $data {object} username/password object
 * returns {object} Profile Object or error/fail message
*/
class Login_User_Validation {
	private $status; // This is handy, keeps it from getting broadcast as part of $this.  You can call $obj->__get('status'); to pull it.
	public function __construct($data) {
		$email = $data->email;
		$password = md5($data->password);
		// SQL Statement
		$SQL = "SELECT " .
						"a.myID AS id,a.myGUID AS guid, " .
						"a.myUsername AS username, " .
						"a.myFirstname AS firstname, " .
						"a.myLastname AS lastname, " .
						"a.myEmail AS email, " .
						"a.myAccountType AS accounttype, " .
						"a.myGender AS gender, " .
						"a.myAvatar AS avatar, " .
						"a.myAccess AS access_id, " .
						"a.myStatus AS status_id, " .
						"DATE_FORMAT(a.myModDate, '%m/%d/%Y %l:%i %p') AS modifeddate, " .
						"DATE_FORMAT(a.myCreateDate, '%m/%d/%Y %l:%i %p') AS createdate, " .
						"b.myAccess AS access, " .
						//"c.myAccountType AS accounttype, " .
						"d.myStatus AS status " . 
						"FROM " .
						"profile_accounts AS a, " .
						"profile_access AS b, " .
						//"account_types AS c, " .
						"status AS d ".
						"WHERE " .
						"a.myEmail='$email' " .
						"AND " .
						"a.myPassword='$password' ".
						"AND " .
						"b.myID=a.myAccess " .
						//"AND ".
						//"c.myID=a.myAccountType " .
						"AND ".
						"d.myID=a.myStatus " .
						"LIMIT 1";	
		// End Statement	
		$DB      = new DB();
		$result  = $DB->query($SQL);
		$num     = mysql_numrows($result);
		if($num == 0) {
			$this->status = "fail";
			$this->msg    = "The username/password combination does not match.";
		} else {
			$profileArr = array();
			while($obj = mysql_fetch_object($result)) {
				$profileArr[] = $obj;
			}
			
			// At this point we also want to get 
		
			$this->status = "success";
			$this->profile = $profileArr[0];
			
			// Great, silently log history
			// Work in some of the data from the above...
			$data->guid = $this->profile->guid;
			$history = new Login_User_History($data);
			
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