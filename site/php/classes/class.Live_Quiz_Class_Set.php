<?php
/**
 * Assignment Get will grab student assignments around this due date period.
*/
class Live_Quiz_Class_Set {
	public function __construct($data) {
		$DB = new DB(); // Remember $DB->$value cleans up data and prevents SQL Injection
		// This will Create, or Update an existing Live Quiz
		$id         = $data->id;
		$tierid     = $data->tierid;     // If this is here its a update
		$guid       = $data->guid;   // Req
		$page       = $data->page;  // Req
		$totalpage  = $data->totalpage;   // Req
		$correct    = $data->correct;   // Optional
		$answer     = $data->answer; // Optional
		$now        = date("YmdHis");
		
		if($id == "" || $id == null) { // This is a add
			$SQLInsert  = "INSERT INTO live_quiz_class (" .
						    							"myTierID, " .
						    							"myUserID, " .
														"myPage, " .
														"myTotalPage, " . 
														"myCorrect, ".
														"myAnswer, " .
														"myTimeStamp) " .
							   				   "VALUES ( ".
							   				 			"'{$DB->$tierid}', " .
														"'{$DB->$guid}', " .
														"'{$DB->$page}', " .
														"'{$DB->$totalpage}', " .
														"'{$DB->$correct}', " .
														"'{$DB->$answer}', " .
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
				$response->live_quiz_class = $arr;			
			}
			
		} else { // Update
			$response->msg = "";
			// Semi Dynamic update
			$SQLUPDATE = "UPDATE live_quiz_class SET ";
			$SQLVALUES =  array();
			if($page != "" || $page != null) {
				array_push($SQLVALUES, "myPage='$page'");
				$response->msg .= "Updating page. ";
			}
			if ($correct != "" || $correct != null) {
				array_push($SQLVALUES, "myCorrect='$correct'");
				$response->msg .= "Updating correct. ";
			}
			if ($answer != "" || $answer != null) {
				array_push($SQLVALUES, "myAnswer='$answer'");
				$response->msg .= "Updating answer. ";
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