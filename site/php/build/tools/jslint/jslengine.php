<?php
// (c) z3n - R1V1@100707 - www.overflow.biz - rodrigo.orph@gmail.com
// Based on the original by Matthias Miller (http://www.JavaScriptLint.com/)

class JSLEngine {
	private $_binarypath;							// jlint exec
	private $_confpath;								// config path
	private $fn;											// temp filename (not used outside class)
	private $r;												// jlint output
	private $has_errors=0;						// error flag

	public function __construct($binarypath="", $confpath="") {

		// default paths
		base_defines(array(
			"jslint_binary_path" => _fn_fix(dirname(dirname(dirname(__FILE__)))."/3rd/jsl-0.3.0/jsl.exe"),
			"jslint_conf_path" => _fn_fix(dirname(dirname(dirname(__FILE__)))."/3rd/jsl-0.3.0/jsl.default.conf")
		));

		// startup
	  $this->_binarypath = $binarypath == "" ? jslint_binary_path : $binarypath;
	  $this->_confpath = $confpath == "" ? jslint_conf_path : $confpath;
	}

	public function __destruct() {
		if ($this->fn != null && file_exists($this->fn))
			unlink($this->fn);
	}

	/* returns error on failure; returns true on success */
	public function Lint($code) {
	  if (!$this->_launchLintBinary($code, $output))
	      die('The JavaScript Lint online service is currently unavailable.');

	  // store lint
	  $this->r=$output;
	  $output=explode("\n",$output); // break lines
	  $x=$output[count($output)-2]; // X error(s), X warning(s) (total lines -2)
	  $x=trim(substr($x,0,strpos($x," ")));
	  if ($x > 0) { // has errors
	  	$this->has_errors=1;
	  	return false;
	  } else { // clean
	  	$this->has_errors=0;
	  	return true;
	  }
	}

	/* assumes path and that SERVER_SOFTWARE env is set */
	private function _launchLintBinary($input, &$output) {
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")
    );

    $this->fn=_fn_fix(dirname(__FILE__).'/tmp.js');

    file_put_contents($this->fn,$input);
    /* launch process */
    $path = PHP_OS == "WINNT" ? $this->_binarypath : escapeshellcmd($this->_binarypath);
    $path.= ' --nologo --conf '.escapeshellarg($this->_confpath).' --process '.escapeshellarg($this->fn);

    $process = proc_open($path, $descriptorspec, $pipes);
    if (!is_resource($process))
        return false;

    $output = '';
    while (!feof($pipes[1]))
       $output .= fgets($pipes[1], 1024);
    fclose($pipes[1]);
    fclose($pipes[2]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($process);
    return true;
	}

	public function output() {
		return $this->r;
	}
}
?>