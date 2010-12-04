<?php
/**
 * Login User History is responsible for logging support info for user.
 * It will essentially log critical info that makes researching issues easier.  I've offloaded a bit of stuff from the JavaScript side.
 * However things like Screen Size, Pixel Depth and availabliity of Flash has to happen on the JavaScript side.
*/
class Login_User_History {
	public function __construct($data) {
		// SQL is recording (UserID, IP, Platform(OS), Browser, Browser Version, ScreenSize, Pixel Depth, Language, Flash, Visits, Mod Date, Create Date)
		// For now I'm just going to store this directly into the profile_history, however this could also be a service later for getting things.
		// So if this needs to change later, you'll have to move this stuff into its own public function.
		
		// I decided to offload this to a library chris chuld wrote...
		require_once('utility/browser.php');
		$ua = new Browser($_SERVER['HTTP_USER_AGENT']);
		$data->ip = $_SERVER['REMOTE_ADDR'];
		$data->os = /*$ua->getPlatform() ? $ua->getPlatform() :*/ $this->getOS(); // Liked mine better
		$data->browser = $ua->getBrowser() ? $ua->getBrowser() : $this->getBrowser();
		$data->browserversion = $ua->getVersion() ? $ua->getVersion() : $this->getBrowserVersion();
		$data->language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
		$myModDate    = date("YmdHis");
    	$myCreateDate = date("YmdHis");
		
		$DB = new DB();
		//if($data->guid != null || $data->guid != 'undefined') {
    		$result = $DB->query("SELECT myID, myVisits FROM profile_history WHERE myUserID='$data->guid'");
    	//} else {
    	//	$result = $DB->query("SELECT myID, myVisits FROM profile_history WHERE myIP='$data->ip'");
    	//}
    	$num = mysql_numrows($result);
    	if ($num == 0) {
		    // New
		    $myVisits = 1;
		    $SQLInsert = "INSERT INTO profile_history (myUserID, myIP, myPlatform, myBrowser, myBrowserVersion, myScreenSize, myScreenPixelDepth, myLanguage, myFlash, myVisits, myModDate, myCreateDate) VALUES ('$data->guid', '$data->ip', '$data->os', '$data->browser', '$data->browserversion', '$data->screensize', '$data->pixeldepth', '$data->language', '', '$myVisits', '$myModDate', '$myCreateDate')";
				if (!($DB->query($SQLInsert))) { // Error
	                 //= mysql_fetch_object($SQLInsert);

				} else { 
					// Don't care
				}
	    } else {
	    	// Update
		    for($i=0; $i<$num; $i++) {
			    $myID         = mysql_result($result,$i,"myID");
			    $myVisits     = mysql_result($result,$i,"myVisits");		
		    }
		    // Only want to update one record (Just in case)
		    $myVisits++;
			$SQLUpdate = "UPDATE profile_history SET myUserID='$data->guid', myIP='$data->ip', myPlatform='$data->os', myBrowser='$data->browser', myBrowserVersion='$data->browserversion', myScreenSize='$data->screensize', myScreenPixelDepth='$data->pixeldepth', myLanguage='$data->language', myFlash='', myVisits='$myVisits', myModDate='$myModDate' WHERE myID='$myID'";
			if (!($DB->query($SQLUpdate))) {
               // = mysql_fetch_object($sqlUpdate);
			} else {
				// Don't care
			}
	    }
    }
    
    
    private function getOS($agent) {
		// the order of this array is important
		$oses = array(
			'Windows 311' => 'Win16',
			'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
			'Windows ME' => '(Windows 98)|(Win 9x 4.90)|(Windows ME)',
			'Windows 98' => '(Windows 98)|(Win98)',
			'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
			'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
			'Windows Server 2003' => '(Windows NT 5.2)',
			'Windows Vista' => '(Windows NT 6.0)',
			'Windows 7' => '(Windows NT 6.1)',
			'Windows NT' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
			'Windows Smartphone' => '(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)',
			'OpenBSD' => 'OpenBSD',
			'SunOS' => 'SunOS',
			'Linux' => '(Linux)|(X11)',
			'iPad'  => '(ipad)',
			'iPod'  => '(ipod)',
			'iPhone' => '(iphone)',
			'Android' => '(android)',
			'Blackberry' => '(blackberry)',
			'Mac OSX' => '(Mac OS X)',
			'Mac OS' => '(Mac_PowerPC)|(Macintosh)',
			'QNX' => 'QNX',
			'BeOS' => 'BeOS',
			'OS2' => 'OS/2',
			'SearchBot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves/Teoma)|(ia_archiver)'
		);
		$agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
		foreach($oses as $os=>$pattern) {
			if (preg_match('/' . strtolower($pattern) . '/i', $agent)) { 
				return $os;
			}
		}
		return 'Unknown OS';
	}
	
	private function getBrowser($agent) {
		$browsers = array(
			'Internet Explorer' => '(msie)',
			'FireFox'           => '(firefox)',
			'Chrome'            => '(chrome)',
			'Safari'            => '(safari)',
			'Opera'             => '(opera)',
			'Netscape'          => '(netscape)', 
			'Konqueror'         => '(konqueror)',
			'Gecko'             => '(gecko)'
		);
		$agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
		foreach($browsers as $browser=>$pattern) {
			if (preg_match('/' . strtolower($pattern) . '/i', $agent)) { 
				return $browser;
			}
		}
		return 'Unknown Browser';
	}
	
	private function getBrowserVersion($agent) {
		// What version? 
		$agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
        // This was giving me funky numbers
        /*if (preg_match('/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/', $agent, $matches)) { 
            return $matches[1]; 
        } else {
        	return 'Unknown';
        }*/
        // Try something new
        $aresult = explode('/',stristr($agent,'Version'));
	    if( isset($aresult[1]) ) {
		    $aversion = explode(' ',$aresult[1]);
		    return $aversion[0];
	    }
	    else {
		    return 'Unknown';
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