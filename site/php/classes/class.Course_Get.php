<?php
/**
 * Course Get will grab the course by ID, or Value
*/
class Course_Get {
	private $status;
	public function __construct($data) {
		
		// I'm taking this SQL Statement vertical so things can be added/removed with ease.  Watch your comma's, parenthesis and quotes! 
		$SQLSelect  = "SELECT a.myID AS id, " .
							 "a.myValue AS value, " .
							 "a.myName AS name, " .
							 //"a.myOrder AS sort, " .
							 "a.myTierID AS tier_id " .
							 "FROM " .
							 "hive_course AS a " .
							 "LEFT JOIN " .
							 "hive_course_data AS b " .
							 "ON " .
							 "a.myTierID=b.myID AND " .
							 "b.myID='$data->id' OR " . 
						  	 "b.myValue LIKE '%$data->value%' " . // Catch all Demo content
							 "ORDER BY " .
							 "a.myOrder ASC";  // sort in order or by due date?  going with due date for now
		$DB      = new DB();
		$result  = $DB->query($SQLSelect);
		$num     = mysql_numrows($result);
		if($num == 0) {
			$this->status = "fail";
			$this->msg    = "Sorry, no course(s) found.";
		} else {
			$arr = array();
			while($obj = mysql_fetch_object($result)) {
				$newObj = array();
				$newObj['id']       = $obj->id;
				$newObj[$obj->name] = $obj->value;
				$newObj['tier_id']  = $obj->tier_id;
				array_push($arr, $newObj);
			}
			//$arr[] = new Course_Get($request); // Dig deeper for any sub data assigned
			$this->status = "success";
			$this->course = $arr; // arr[0] for just 1
			
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