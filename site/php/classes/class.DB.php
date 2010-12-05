<?php
/**
 * Connection Class for connecting to mySQL
 * I've updated this to add some extra security for SQL Injection (mysql_real_escape_string)
 */
class DB {
	/** 
	 * Create new DB Connection
	 */ 
	public function __construct() {
		$this->host = "localhost";
		$this->db   = "hive";
		$this->user = "username";
		$this->pass = "password";
		$this->link = mysql_connect($this->host, $this->user, $this->pass) or trigger_error("MySQL Connection Database Error: " . mysql_error(), FATAL);
		mysql_select_db($this->db);
	}
	/**
	 * Query SQL
	 */
	public function query($query) {
		$this->result = mysql_query($query, $this->link) or trigger_error('MySQL Query Failed: ' . mysql_error(), FATAL);
		return $this->result;
	}
	
	/**
	 * Server Checkup
	 *
	 */
	public function checkup() {
		$this->link = mysql_connect($this->host, $this->user, $this->pass) or trigger_error("MySQL Connection Database Error: " . mysql_error(), FATAL);
		return "yes";
	}

	/** 
	 * Close SQL Connection
	 */
	public function close() {
		mysql_close($this->link);
	}
	
	private function charset_decode_utf_8 ($string) { 
	    if (! ereg("[\200-\237]", $string) and ! ereg("[\241-\377]", $string)) 
	        return $string; 
	
	    // decode three byte unicode characters 
	    $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",        
	    "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'",    
	    $string); 
	
	    // decode two byte unicode characters 
	    $string = preg_replace("/([\300-\337])([\200-\277])/e", 
	    "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", 
	    $string); 
	
	    return $string; 
	}
	public function __get($value) {
		//$value =  addslashes(preg_replace("/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", htmlspecialchars($value)));
		return mysql_real_escape_string($value);
	}
	public function IsNullOrEmpty($value) {
		return (!isset($question) || trim($question)==='');
	}
}
?>