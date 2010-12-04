<?php
/**
 * Assignment Get will grab student assignments around this due date period.
*/
class Live_Quiz_Set {
	public function __construct($data) {
		$DB = new DB(); // Remember $DB->$value cleans up data and prevents SQL Injection
		// This will Create, or Update an existing Live Quiz
		$id     = $data->id;     // If this is here its a update
		$guid   = $data->guid;   // Req
		$title  = $data->title;  // Req
		$path   = $data->path;   // Req
		$page   = $data->page;   // Optional
		$status = $data->status; // Optional
		$now    =  date("YmdHis");
		
		if($id == "" || $id == null) { // This is a add
			$SQLInsert  = "INSERT INTO live_quiz (" .
					    							"myUserID, " .
													"myName, " .
													"myValue, " . 
													"myPage, ".
													"myStatus, " .
													"myTimeStamp) " .
						   				 "VALUES ( ".
													"'{$DB->$guid}', " .
													"'{$DB->$title}', " .
													"'{$DB->$path}', " .
													"'0', " .
													"'1', " .
													"'$now')";
			
			if (!($DB->query($SQLInsert))) { 
				// Error
				$response->status = "error";
				$response->msg = mysql_fetch_object($SQLInsert);
			} else {
				$response->status = "success";
				$arr = array(); // Have to return an array here so it matches the "get"
				$obj->id = mysql_insert_id();
				$arr[] = $obj;
				$response->live_quiz = $arr;			
			}
			
		} else { // Update
			$response->msg = "";
			// Semi Dynamic update
			$SQLUPDATE = "UPDATE live_quiz SET ";
			$SQLVALUES =  array();
			if($page != "" || $page != null) {
				array_push($SQLVALUES, "myPage='$page'");
				$response->msg .= "Updating page. ";
			}
			if ($status != "" || $status != null) {
				array_push($SQLVALUES, "myStatus='$status'");
				$response->msg .= "Updating status. ";
			}
			$SQLWHERE = " WHERE myID='$id'";
			$SQLUpdate = $SQLUPDATE . $this->formatUPDATE($SQLVALUES) . $SQLWHERE;
			//return $this->response = $SQLUpdate;
			if (!($DB->query($SQLUpdate))) {
               	$response->status = "error";
               	$response->msg = mysql_fetch_object($sqlUpdate);
			} else {
				$response->status = "success";
			}
		}	
        return $this->response = $response;
    }
	
	private function formatUPDATE( $values ){
		$string = "";
		for($i = 0;$i < count($values); $i++) {
			if($i == count($values) -1) {
				$string .= $values[$i]; // don't want a comma on the last one	
			} else {
				$string .= $values[$i] . ", ";
			}
		}
		return $string;
	}
    
	public function __get($name) {
		return $this->$name;
	}
	public function __set($name, $value) {
		return $this->$name = $value;
	}		
}
?>