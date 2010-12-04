<?php
/**
 * Assignment Add will assign a course ID to a student.
 * Custom courses could be created, then assigned.  Demo course etc ...  For now this is just a quick way to assign
 * a course to a individual.  A Group based "class" would need to be done similarly except per student in the class.
*/
class Assignment_Add {
	public function __construct($data) {
		$data->order = $data->order ? $data->order : 1; // Default (could auto check current assignments here and increment)
		// Absorbing a date string from another system may take converting it to a date like date("YmdHis", strtotime($data->startdate));
		$data->startdate  = $data->startdate ? $data->startdate : date("YmdHis"); // default today
    	$data->enddate    = $data->enddate ? $data->enddate : date("YmdHis", strtotime(date("YmdHis", strtotime($data->startdate)) . "+1 month")); // default
		// I'm taking this SQL Statement vertical so things can be added/removed with ease.  Watch your comma's, parenthesis and quotes! 
		$SQLInsert  = "INSERT INTO profile_assignments (" .
														"myUserID, " . 
														"myAssignID, " .
														"myStatus, " .
														"myOrder, " .
														"myStartDate, " .
														"myEndDate) " . //end - no comma
														"VALUES ( " .
														"'$data->guid', " .
														"'$data->assign_id', " .
														"'$data->status', " .
														"'$data->order', " .
														"'$data->startdate', " .
														"'$data->enddate')";  //end - no comma
		$DB = new DB();
		if (!($DB->query($SQLInsert))) { 
			// Error
			$this->status = "error";
			$this->msg = mysql_fetch_object($SQLInsert);
		} else {
			// Silent for now			
		}	
        //return $this;
    }
    
	public function __get($name) {
		return $this->$name;
	}
	public function __set($name, $value) {
		return $this->$name = $value;
	}		
}
?>