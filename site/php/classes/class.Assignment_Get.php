<?php
/**
 * Assignment Get will grab student assignments around this due date period.
*/
class Assignment_Get {
	private $status;
	public function __construct($data) {
		$today =  date("YmdHis");
		// Last thing you want to do here is make 2 SQL statements and try to join the results in some kind of way that can get passed back as a object.
		// I'm opting to do a JOIN on the assigned ID, and the meta data at the hive_course.  This should retrieve all name/value pairs. 
		$SQLSelect  = "SELECT a.myAssignID AS id, " .
							 "a.myStatus AS status, " .
							 "a.myOrder AS sort, " . 
							 "a.myStartDate AS startdate, " .
							 "a.myEndDate AS enddate, " .
							 "b.myName AS name, " .
							 "b.myValue AS value " . 
							 "FROM ".
							 "profile_assignments AS a JOIN " .
							 "hive_course AS b ON " .
							 "a.myAssignID=b.myID AND " .
							 "a.myUserID='$data->guid' AND " . 
							 "a.myStatus=1 AND " .
							 "a.myStartDate<='$today' AND " . // not started yet
							 "a.myEndDate>='$today' " .        // hasn't expired yet
							 "ORDER BY " .
							 "a.myEndDate ASC";  // sort in order or by due date?  going with due date for now
		$DB      = new DB();
		$result  = $DB->query($SQLSelect);
		$num     = mysql_numrows($result);
		if($num == 0) {
			$this->status = "fail";
			$this->msg    = "Sorry, no assignments found for " .  $data->guid;
		} else {
			$arr = array();
			while($obj = mysql_fetch_object($result)) {
				/* At this point we have to also get the Lessons assigned to this
				 * Comments to myself .. I think its easier to hand off the obj to get added to by a sub type
				 * Im attempting to comment out what I was doing here, to accomplish that.
				 * If you did this the way its commmented out below you get a container "lessons" then "lesson" below.
				 * I haven't found out a way to get around that yet. This works though.
				 */
				// Option 2
				$obj->data          = $this->checkTier($obj->id);
				$newObj             = new Lesson_Get($obj);
				// Option 1
				/*$newObj             = array();
				$newObj['id']       = $obj->id;
				$newObj[$obj->name] = $obj->value;
				$newObj['startdate']= $obj->startdate;
				$newObj['enddate']  = $obj->enddate;
				$newObj['status']   = $obj->status;
				$newObj['data']     = $this->checkTier($obj->id); 
				$newObj['lessons']  = new Lesson_Get($obj);*/
				array_push($arr, $newObj);
			}
			$this->status = "success";
			$this->course = $arr; // arr[0] for just 1
		}	
        return $this;
    }
    
    private function checkTier($id) {
    	$SQLSelect  = "SELECT " .
    						 //"a.myID AS id, " .
							 //"a.myTierID AS tier_id, " .
							 "a.myName AS name, " . 
							 "a.myValue AS value " .
							 "FROM ".
							 "hive_course_data AS a " . // RIGHT JOIN " .
							 "WHERE " . //"hive_lesson_data AS b ON " .
							 "a.myTierID='$id'";
		$DB      = new DB();
		$result  = $DB->query($SQLSelect);
		$num     = mysql_numrows($result);
		$arr = array();
		if($num > 0) {
			while($obj = mysql_fetch_object($result)) {
				// Normally I'd just return this, but since we have name / value pairs I'd rather re-title them
				$arr[$obj->name] = $obj->value;
			}
		}	
        return $arr;
    }
    
	public function __get($name) {
		return $this->$name;
	}
	public function __set($name, $value) {
		return $this->$name = $value;
	}		
}
?>