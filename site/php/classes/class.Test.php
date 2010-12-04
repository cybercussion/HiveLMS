<?php
class Test {
	function __construct($data) {
		$this->response->status = "success";
		$this->response->msg = "Dynamic Classes work, this is a test class!";
        return $this->response;
    }		
}
?>