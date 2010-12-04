<?php
/**
 * Assignment Get will grab student assignments around this due date period.
*/
class Live_Quiz_Dump {
	public function __construct($data) {
		$DB = new DB();
		// This will create a dynamic SQL statement to filter the results
		/**
		 * Expects
		 * @param id
		 // ID Required, rest optional for searching.
		 data: {
		 		action: "Live_Quiz_Dump",
		 		id: 1
		 	}
		*/
		$id     = $data->id;     // Optional
		if($this->isEmpty($id)) {
			$response->status = "fail";
			$response->msg    = "Sorry, the ID was null, ignoring request";
		} else {
		
			//Define Base statements
			$SQLDELETE     = "DELETE FROM live_quiz WHERE myID='$id'";
			// Ship it
			$DB->query($SQLDELETE);
			$msg = "affected Quiz " . mysql_affected_rows();
			
			$SQLDELETE2     = "DELETE FROM live_quiz_class WHERE myTierID='$id'";
			// Ship it
			$DB->query($SQLDELETE2);
			$response->status = "success";
			$response->msg    = "Quiz and Class Removed for " . $DB->$id . " " . $msg . " affected class rows "  . mysql_affected_rows();
				
	        return $this->response = $response;
	    }
    }
	
	private function isEmpty($value) {
		if($value == null || $value == "" || $value == "undefined") {
			return true;
		} else {
			return false;
		}
	}
    
	public function __get($name) {
		return $this->$name;
	}
	public function __set($name, $value) {
		return $this->$name = $value;
	}		
}
?>