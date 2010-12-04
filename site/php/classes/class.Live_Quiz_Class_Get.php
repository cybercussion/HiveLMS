<?php
/**
 * Assignment Get will grab student assignments around this due date period.
*/
class Live_Quiz_Class_Get {
	public function __construct($data) {
		$DB = new DB();
		// This will create a dynamic SQL statement to filter the results
		/**
		 * Expects
		 * @param id
		 data: {
		 		action: "Live_Quiz_Class_Get",
		 		id: 1,
		 		
		 	}
		*/
	
		$id     = $data->id;     // Optional
		
		
		//Define Base statements
		$SQLSELECT     = "SELECT " .
									"myID AS id, " .
									"myTierID AS tier, " .
									"myUserID AS guid, " .
									"myPage AS page, " .
									"myTotalPage AS totalpage, " .
									"myCorrect AS correct, " .
									"myAnswer AS answer, " .
									"DATE_FORMAT(myTimeStamp, '%m/%d/%Y %l:%i %p') As time ";
    	$SQLFROM       = "FROM live_quiz_class  ";
    	$SQLBASEWHERE  = "WHERE ";
		
		$SQLWHERE = array();
		if(!$this->isEmpty($id)) {
			array_push($SQLWHERE, "myTierID='{$DB->$id}' ");
		}
    			
    	$SQLWHERE = $this->formatWHERE($SQLWHERE);
		// Append extra filters or not
    	if($SQLWHERE) {
    		$SQLWHERE = $SQLBASEWHERE . $SQLWHERE;
    	} else {
    		$SQLWHERE = $SQLBASEWHERE;
    	}
		// Put it together
    	$SQLQUERY = $SQLSELECT . $SQLFROM . $SQLWHERE;
		// Ship it
		$result  = $DB->query($SQLQUERY);
		$num     = mysql_numrows($result);
		if($num == 0) {
			$response->status = "fail";
			$response->msg    = "Sorry, no students found.";
		} else {
			$arr = array();
			while($obj = mysql_fetch_object($result)) {
				// In order to get any chained data
				$arr[] = $obj;
			}
			$response->status = "success";
			$response->live_quiz_class = $arr; // arr[0] for just 1
		}	
        return $this->response = $response;
    }
    
    private function formatWHERE( $where ){
		$string = "";
		for($i = 0;$i < count($where); $i++) {
			if($i == 0) {
				// No AND
				$string .= $where[$i];
			} else {
				$string .= "AND " . $where[$i];
			}
		}
		return $string;
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