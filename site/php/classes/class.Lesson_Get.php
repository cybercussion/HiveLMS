<?php
/**
 * Lesson Get will grab lessons assigned to a course.
*/
class Lesson_Get {
	private $status;
	public function __construct($data) {
		$today =  date("YmdHis");
		// Following is inhierited from Assignments to write out the object without another tier like "lessons"
		$this->id        = $data->id;
		$this->name      = $data->value;
		$this->startdate = $data->startdate;
		$this->enddate   = $data->enddate;
		$this->status    = $data->status;
		$this->data      = $data->data;
 
		// Last thing you want to do here is make 2 SQL statements and try to join the results in some kind of way that can get passed back as a object.
		// I'm opting to do a JOIN on the assigned ID, and the meta data at the hive_course.  This should retrieve all name/value pairs. 
		$SQLSelect  = "SELECT a.myID AS id, " .
							 "a.myTierID AS tier_id, " .
							 "a.myName AS name, " . 
							 "a.myValue AS value, " .
							 "a.myOrder AS sort " .
							 //"b.myName AS dname, " .
							 //"b.myValue AS dvalue " . 
							 "FROM ".
							 "hive_lesson AS a " . // RIGHT JOIN " .
							 "WHERE " . //"hive_lesson_data AS b ON " .
							 "a.myTierID='$data->id'";
		$DB      = new DB();
		$result  = $DB->query($SQLSelect);
		$num     = mysql_numrows($result);
		if($num == 0) {
			$this->status = "fail";
			$this->msg    = "Sorry, no lesson(s) found.";
		} else {
			$arr = array();
			while($obj = mysql_fetch_object($result)) {
				// In order to get any chained data
				$newObj = array();
				$newObj['id']       = $obj->id;
				$newObj['tier_id']  = $obj->tier_id;
				$newObj[$obj->name] = $obj->value;
				$newObj['data']     = $this->checkTier($obj->id);
				array_push($arr, $newObj);
			}
			$this->status = "success";
			$this->lesson = $arr; // arr[0] for just 1
			//return $arr;
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
							 "hive_lesson_data AS a " . // RIGHT JOIN " .
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