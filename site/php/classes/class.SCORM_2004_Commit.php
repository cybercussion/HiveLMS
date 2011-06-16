<?php
/*class SCORM_2004_Commit {
	function __construct($data) {
		$cmi = $data->cmi;
		
		//$this->save_as_json($cmi);
	
		$this->response->status = "success";
		$this->response->msg = "cmi object saved!";
        return $this->response;
    }
    
    private function save_as_json($obj) {
    	$json_str = json_encode($obj);
    	$myFile = "../data/id_id_cmi.json"; // come back and tack on Student data, maybe base64/MD5 it
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $json_str);
		fclose($fh);
    }
    
    private function indent($json) {

	    $result      = '';
	    $pos         = 0;
	    $strLen      = strlen($json);
	    $indentStr   = '  ';
	    $newLine     = "\n";
	    $prevChar    = '';
	    $outOfQuotes = true;
	
	    for ($i=0; $i<=$strLen; $i++) {
	
	        // Grab the next character in the string.
	        $char = substr($json, $i, 1);
	
	        // Are we inside a quoted string?
	        if ($char == '"' && $prevChar != '\\') {
	            $outOfQuotes = !$outOfQuotes;
	        
	        // If this character is the end of an element, 
	        // output a new line and indent the next line.
	        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
	            $result .= $newLine;
	            $pos --;
	            for ($j=0; $j<$pos; $j++) {
	                $result .= $indentStr;
	            }
	        }
	        
	        // Add the character to the result string.
	        $result .= $char;
	
	        // If the last character was the beginning of an element, 
	        // output a new line and indent the next line.
	        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
	            $result .= $newLine;
	            if ($char == '{' || $char == '[') {
	                $pos ++;
	            }
	            
	            for ($j = 0; $j < $pos; $j++) {
	                $result .= $indentStr;
	            }
	        }
	        
	        $prevChar = $char;
	    }
	
	    return $result;
	}
    
    public function __get($name) {
		return $this->$name;
	}
	public function __set($name, $value) {
		return $this->$name = $value;
	}	
}*/

class SCORM_2004_Commit {
	private $cmi;
	private $student_id;
	private $sco_id;
	
	function __construct($data) {
		$this->data    = $data->data;
		$this->student_id = $this->data->cmi->learner_id;
		$this->sco_id     = $data->sco_id;
		
		// Error control later
		
		$this->saveFile();
		
		$this->response->status = "success";
		$this->response->msg = "cmi object saved!";
        return $this->response;
    }
    
    private function saveFile() {
    	$json_str = json_encode($this->data);
    	$cmi_path = "../data/" . $this->student_id . "_" . $this->sco_id . "_cmi.json"; // come back and tack on Student data, maybe base64/MD5 it
		$fh = fopen($cmi_path, 'w') or $this->sendError("Can't open file");
		fwrite($fh, $json_str);
		fclose($fh);
    }
    private function sendError($msg) {
    	$this->response->status = "error";
    	$this->response->msg = $msg;
    	return $this->response;
    }
}
?>