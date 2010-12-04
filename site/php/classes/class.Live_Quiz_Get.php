<?php
/**
 * Assignment Get will grab student assignments around this due date period.
*/
class Live_Quiz_Get {
	public function __construct($data) {
		$DB = new DB();
		// This will create a dynamic SQL statement to filter the results
		/**
		 * Expects
		 * @param id
		 * @param user guid
		 * @param Quiz Title
		 * @param Quiz Path
		 * @param Page
		 * @param status 1=lobby, 2=running, 3=locked, 4=ended
		 // ID Required, rest optional for searching.
		 data: {
		 		action: "Live_Quiz_Get",
		 		id: 1,
		 		guid: 67898765678900876,
		 		title: "Name of Quiz",
		 		path: "content/ic/livequiz/player.html",
		 		status: 1
		 	}
		*/
		//$data->limit = $data->limit ? $data->limit : 1;
		
		$id     = $data->id;     // Optional
		$guid   = $data->guid;   // Optional
		$title  = $data->title;  // Optional
		$path   = $data->path;   // Optional
		$page   = $data->page;   // Optional
		$status = $data->status; // Optional
		
		
		//Define Base statements
		$SQLSELECT     = "SELECT " .
									"myID As id, " .
									"myUserID As guid, " .
									"myName As title, " .
									"myValue As path, " .
									"myPage As page, " .
									"myStatus As status, " .
									"DATE_FORMAT(myTimeStamp, '%m/%d/%Y %l:%i %p') As time ";
    	$SQLFROM       = "FROM live_quiz  ";
    	$SQLBASEWHERE  = "WHERE ";
		
		$SQLWHERE = array();
		if(!$this->isEmpty($id)) {
			array_push($SQLWHERE, "myID='{$DB->$id}' ");
		}
		if(!$this->isEmpty($guid)) {
    		array_push($SQLWHERE, "myUserID='{$DB->$guid}' ");
    	} 
    	if(!$this->isEmpty($title)) {
	    	array_push($SQLWHERE, "myName='{$DB->$title}' ");
	    } 
	    if(!$this->isEmpty($path)) {
	    	array_push($SQLWHERE, "myValue='{$DB->$path}' ");
	    }
		if(!$this->isEmpty($status)) {
	    	array_push($SQLWHERE, "myStatus='{$DB->$status}' ");
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
			$response->msg    = "Sorry, no quiz found.";
		} else {
			$arr = array();
			while($obj = mysql_fetch_object($result)) {
				// In order to get any chained data
				$arr[] = $obj;
			}
			$response->status = "success";
			$response->live_quiz = $arr; // arr[0] for just 1
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