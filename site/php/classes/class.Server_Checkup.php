<?php
/**
 * Login is responsible for validating the username and password.
 * It will return the profile to the client or send a fail/error.
*/
class Server_Checkup {
	public function __construct($data) {
		$this->response->status = "success";
		$DB = new DB();
		$this->response->sql->online = $DB->checkup();
		$this->response->sql->version = mysql_get_server_info();
		$this->response->php->online = "yes";
		$this->response->php->version = phpversion();
		$this->response->server->OS = PHP_OS;
		// read in the uptime (using exec)
		$uptime = exec("cat /proc/uptime");
		if(!$uptime) {
			// Probably windows then
			//$winstats = shell_exec("net statistics server");
			// grab the date & time the server started up
			//preg_match("(\d{1,2}/\d{1,2}/\d{4}\s+\d{1,2}\:\d{2}\s+\w{2})", $winstats, $matches);
			// convert the readable date & time to a timestamp and deduct it from the current timestamp
			// thus giving us the total uptime in seconds
			//$uptimeSecs = time() - strtotime($matches[0]);
			$uptimeSecs = (time() - filemtime('c:\pagefile.sys')); // this works, the net statistics were returning 14866 days
		} else {
			$uptime = split(" ",$uptime);
			$uptimeSecs = $uptime[0];
		}
		$this->response->server->uptime = $this->format_uptime($uptimeSecs);
		
        return $this->response;
    }
    
    private function format_uptime($seconds) {
		$secs = intval($seconds % 60);
		$mins = intval($seconds / 60 % 60);
		$hours = intval($seconds / 3600 % 24);
		$days = intval($seconds / 86400);
		
		if ($days > 0) {
			$uptimeString .= $days;
			$uptimeString .= (($days == 1) ? " day" : " days");
		}
		if ($hours > 0) {
			$uptimeString .= (($days > 0) ? ", " : "") . $hours;
			$uptimeString .= (($hours == 1) ? " hour" : " hours");
		}
		if ($mins > 0) {
			$uptimeString .= (($days > 0 || $hours > 0) ? ", " : "") . $mins;
			$uptimeString .= (($mins == 1) ? " minute" : " minutes");
		}
		if ($secs > 0) {
			$uptimeString .= (($days > 0 || $hours > 0 || $mins > 0) ? ", " : "") . $secs;
			$uptimeString .= (($secs == 1) ? " second" : " seconds");
		}
		return $uptimeString;
	}		
}
?>