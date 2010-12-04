<!DOCTYPE html>
<html>
	<head>
		<title>Demo2 (Literature) Build</title>		
		<style type="text/css">
			*{margin: 0px; padding: 0px; -webkit-tap-highlight-color: rgba(0,0,0,0); -webkit-touch-callout: none; -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none;}
			img, canvas, .img{border-style: none; -webkit-transform: translate3d(0,0,0);}
			html,body{width: 100%; height: 100%; border: 0;}
			a:link {color:#000;}   
			a:visited {color:#000;}
			a:hover {color:#CCC;}
			a:active {color:#0000FF;}
			.blackgrad {background: -webkit-gradient(linear, left top, left bottom, from(#000), to(#3a3a3a)); background: -moz-linear-gradient(top,  #000,  #3a3a3a); filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#000000', endColorstr='#3a3a3a'); background-color: #3a3a3a;}
			.bluegrad  {background: -webkit-gradient(linear, left top, left bottom, from(#0f71b1), to(#17567f)); background: -moz-linear-gradient(top,  #0f71b1,  #17567f); filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#0f71b1', endColorstr='#17567f'); background-color: #17567f;}
			.ninja-bg{position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url(img/Ninja.png) 95% 5% no-repeat transparent; z-index: 5;}
			input, textarea {-webkit-user-select: text; -webkit-appearance: none; padding: 3px;}
			body{font-family: Arial, Verdana, sans; font-size: 12px; color: #000;}
			#build{min-height: 100%; height: auto !important; width: 100%;}
			.log{position: relative; margin: 0 0 0 10px;  width: 380px; height: 100%; padding: 5px 0 5px 0; z-index: 100; background: url(img/paper-bg.jpg) repeat #FFF; border-style: solid; border-color: #000; -webkit-box-shadow: rgba(0,0,0,.5) 0px 1px 3px; -moz-box-shadow: rgba(0,0,0,.5) 0px 1px 3px;}
			p{text-shadow:1px 1px 1px rgba(0,0,0,.2);}
			.large{font-size: 14px;}
			.log h1{font-size: 16px; font-weight: bold;}
			.ntitle{position: relative; width: 100%; height: auto; font-size: 30px; text-align: center; font-weight: bold;}
			.nheader, .nfooter{padding: 2px 0 2px 0;}
			.nfooter p {margin: 4px 10px 0 10px;}
			.indent {margin-left: 10px;}
			.moreindent {margin-left: 20px;}
			.appheader{margin-left: 50px; padding-top: 10px; font-size: 20px; color: #CCC; font-weight: bold; width: 360px; height: 65px;}
			.appheader .apptitle p{margin-left: 6px; text-shadow:2px 2px 2px rgba(0,0,0,.5);}
			.appheader .apptitle p.subtitle{margin-left: 12px; font-size: 14px;}
			.yellow{color: #eef84b;}
			.red{color: #f84b4b;}
			.green{color: #5dd66b;}
			.imphasis{font-size: 20px;}
			.nblackbar{background-color: #000; width: 100%;}
			.thick{height: 8px;}
			.thin{height: 4px;}
			.nrow{width: 100%; min-height: 20px; border-bottom: #000; border-bottom-style: solid; border-bottom-width: 1px;}
			.nrow p{margin: 4px 10px 0 10px;}
			.floatL{float: left;}
			.floatR{float: right;}
			.icon{background: url(../../img/hive_icon.png) no-repeat transparent; width: 60px; height: 60px;}
			
			@-webkit-keyframes fadein {from { opacity: 0; } to { opacity: 1; }}
			@-webkit-keyframes fadeout {from { opacity: 1; } to { opacity: 0; }}
			.in, .out{-webkit-animation-timing-function:ease-in-out; -webkit-animation-duration:350ms;}
			.fade.in {z-index: 10; -webkit-animation-name: fadein;}
			.fade.out {	z-index: 0;}
			.dissolve.in {-webkit-animation-name: fadein;}
			.dissolve.out {-webkit-animation-name: fadeout;}
		</style>
	</head>
	<body class="blackgrad">
		<div id="build" class="bluegrad dissolve in">
			<div class="ninja-bg"></div>
			<div class="appcontainer">
				<div class="appheader">
					<div class="floatL icon"</div>
					<div class="floatL apptitle">
						<p>Demo2 Automated Build</p>
						<p class="subtitle">HTML/CSS/JavaScript Packaging</p>
						<p class="subtitle">Real-time processing</p>
					</div>
				</div>
				<div class="log">
				
<?php
	/**
	 * Developer Build Automation
	 * This is the first run of a automated build from developer code that will
	 * be merged, then minified/compressed into the index.html.
	 * The main goal is to reduce http calls and creature comfort formatting to save bandwidth.
	*/
	// Tools used
 	//require_once('tools/cssmin/cssmin-v2.0.1.0064.php');
 	error_reporting(E_ERROR); // PHP not happy with <audio> html5 elements.  Hide warnings.
 	require_once('tools/jsmin/jsmin.php');
	require_once('tools/js-size/ECMAScriptPacker.php');
	
	require_once("tools/jslint/_jsl_online.php");
	
	function minifycss( $css ) {
		$css = preg_replace( '#\s+#', ' ', $css );
		$css = preg_replace( '#/\*.*?\*/#s', '', $css );
		$css = str_replace( '; ', ';', $css );
		$css = str_replace( ': ', ':', $css );
		$css = str_replace( ' {', '{', $css );
		$css = str_replace( '{ ', '{', $css );
		$css = str_replace( ', ', ',', $css );
		$css = str_replace( '} ', '}', $css );
		$css = str_replace( ';}', '}', $css );
	
		return trim( $css );
	}
	
	function stripwhitespace($bff){
		$pzcr=0;
		$pzed=strlen($bff)-1;
		$rst="";
		while($pzcr<$pzed){
			$t_poz_start=stripos($bff,"<textarea",$pzcr);
			if($t_poz_start===false){
				$bffstp=substr($bff,$pzcr);
				$temp=stripBuffer($bffstp);
				$rst.=$temp;
				$pzcr=$pzed;
			} else {
				$bffstp=substr($bff,$pzcr,$t_poz_start-$pzcr);
				$temp=stripBuffer($bffstp);
				$rst.=$temp;
				$t_poz_end=stripos($bff,"</textarea>",$t_poz_start);
				$temp=substr($bff,$t_poz_start,$t_poz_end-$t_poz_start);
				$rst.=$temp;
				$pzcr=$t_poz_end;
			}
		}
		return $rst;
	}
	
	function stripBuffer($bff){
		/* carriage returns, new lines */
		$bff=str_replace(array("\r\r\r","\r\r","\r\n","\n\r","\n\n\n","\n\n", " \r", " \n"),"\n",$bff);
		/* tabs */
		$bff=str_replace(array("\t\t\t\t\t\t","\t\t\t\t\t","\t\t\t\t","\t\t\t","\t\t", "\t", "\t\n","\n\t"),"",$bff);
		/* opening HTML tags */
		$bff=str_replace(array(">\r<a",">\r <a",">\r\r <a","> \r<a",">\n<a","> \n<a","> \n<a",">\n\n <a"),"><a",$bff);
		$bff=str_replace(array(">\r<b",">\n<b"),"><b",$bff);
		$bff=str_replace(array(">\r \t\t<d",">\r<d",">\n<d","> \n<d",">\n <d",">\r <d",">\n\n<d"),"><d",$bff);
		$bff=str_replace(array(">\r<f",">\n<f",">\n <f"),"><f",$bff);
		$bff=str_replace(array(">\r<h",">\n<h",">\t<h","> \n\n<h"),"><h",$bff);
		$bff=str_replace(array(">\r<i",">\n<i",">\n <i"),"><i",$bff);
		$bff=str_replace(array(">\r<i",">\n<i"),"><i",$bff);
		$bff=str_replace(array(">\r<l","> \r<l",">\n<l","> \n<l",">  \n<l","/>\n<l","/>\r<l", ">\r <l", ">\n <l"),"><l",$bff);
		$bff=str_replace(array(">\t<l",">\t\t<l", ">\t\t\t<l", ">\t\t\t\t<l",">\t\t\t\t\t<l"),"><l",$bff);
		$bff=str_replace(array(">\n	<d",">\n\t<d",">\n \t<d",">\n \t<d", "><\t<d", "> \t\t<d", ">\t\t<d", ">\t\t\t<d", ">\t\t\t\t<d", ">\t\t\t\t\t<d"),"><d",$bff);
		$bff=str_replace(array(">\r<m",">\n<m"),"><m",$bff);
		$bff=str_replace(array(">\r<n",">\n<n"),"><n",$bff);
		$bff=str_replace(array(">\r<p",">\n<p",">\n\n<p","> \n<p","> \n <p"),"><p",$bff);
		$bff=str_replace(array(">\r<s",">\n<s"),"><s",$bff);
		$bff=str_replace(array(">\r<t",">\n<t"),"><t",$bff);
		/* closing HTML tags */
		$bff=str_replace(array(">\r</a",">\n</a"),"></a",$bff);
		$bff=str_replace(array(">\r</b",">\n</b"),"></b",$bff);
		$bff=str_replace(array(">\r</u",">\n</u"),"></u",$bff);
		$bff=str_replace(array(">\r</d",">\n</d",">\n </d"),"></d",$bff);
		$bff=str_replace(array(">\r</f",">\n</f"),"></f",$bff);
		$bff=str_replace(array(">\r</l",">\n</l"),"></l",$bff);
		$bff=str_replace(array(">\r</n",">\n</n"),"></n",$bff);
		$bff=str_replace(array(">\r</p",">\n</p"),"></p",$bff);
		$bff=str_replace(array(">\r</s",">\n</s"),"></s",$bff);
		/* other */
		$bff=str_replace(array(">\r<!",">\n<!"),"><!",$bff);
		$bff=str_replace(array("\n<div")," <div",$bff);
		$bff=str_replace(array(">\r\r \r<"),"><",$bff);
		$bff=str_replace(array("> \n \n <"),"><",$bff);
		$bff=str_replace(array(">\r</h",">\n</h"),"></h",$bff);
		$bff=str_replace(array("\r<u","\n<u"),"<u",$bff);
		$bff=str_replace(array("/>\r","/>\n","/>\t"),"/>",$bff);
		$bff=ereg_replace(" {2,}",' ',$bff);
		$bff=ereg_replace("  {3,}",'  ',$bff);
		$bff=str_replace("> <","><",$bff);
		$bff=str_replace("  <","<",$bff);
		/* non-breaking spaces */
		$bff=str_replace(" &nbsp;","&nbsp;",$bff);
		$bff=str_replace("&nbsp; ","&nbsp;",$bff);
		
		$bff=str_replace(" \r", '', $bff);
		$bff=str_replace(" \n", '', $bff);
		$bff=str_replace(array(">\n <",">\n\n<",">\n\n\n<"), ">\n<", $bff);
		$bff=str_replace(array(">\r <",">\r\r<",">\r\r\r<"), ">\r<", $bff);
		
		/* Example of EXCEPTIONS where I want the space to remain
		between two form buttons at */ 
		/* <!-- http://websitetips.com/articles/copy/loremgenerator/ --> */
		/* name="select" /> <input */
		$bff=str_replace(array("name=\"select\" /><input"),"name=\"select\" /> <input",$bff);
		
		return $bff;
	}
	
	function format_bytes($size) {
	    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
	    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
	    return round($size, 2).$units[$i];
	}
	
	function percent($num_amount, $num_total) {
		$count1 = $num_amount / $num_total;
		$count2 = $count1 * 100;
		$count = number_format($count2, 0);
		return $count;
	}
	
	$root                = "../../content/ic/demo2/";
	$savefiles           = true;
	$totalfiles          = 0;
	$css_min_file        = "css/styles.min.css";
	$css_minified        = $root . $css_min_file;
	$cssSavings          = 0;
	
	$js_pack_file        = "js/pearson.sco.pack.js";
	$js_packed           = $root . $js_pack_file;
	$js_minified         = $root . "js/pearson.sco.min.js";
	$jsSavings           = 0;
	
	$srchtmlfile         = "player_dev.html";
	$htmlfile            = "player.html";
	$htmlSavings         = 0;
	// Intent is to load the index_dev.html parse and compress.
	$doc                 = new DOMDocument();
	$doc->loadHTMLFile($root . $srchtmlfile);
	$doc->preserveWhiteSpace = false;
	$doc->formatOutput       = false;
	
	// Open message:
	//echo("<h1 class=\"dissolve in\"><p>Yes, I'm a ninja. No, I won't show you my moves...</p></h1><br />");
	
	// Lets get the CSS file(s) and compress them //////////////////////////////////////////////////////
	//echo("<h1 class=\"dissolve in\"><p>Let's take a look at your CSS...</p></h1>");
	$linkElements = $doc->getElementsbyTagName('link');
	$css = '';
	$css_size = '';
	$cssContents;
	$cssFiles = array();
	if(!is_null($linkElements)) {
		//echo("<p class=\"dissolve in\">" . $linkElements->length . " <code>&lt;link&gt;</code> tag(s) exist:</p>");
		$i = 0;
		foreach($linkElements as $link) {
			$rel = $link->getAttribute('rel');
			if($rel == "stylesheet") {
				$file = $link->getAttribute('href');
				// We want to load, compress and save out the minified version.
				/* Note: Considering placing this in the HTML file instead in a <style> element but it may mess up the pathing of the image references
				   Since there relative to the css/ path.  May need to scrub and replace if that ends up happening. */
				if(strpos($file, 'html')!== false) { // for now I don't want to mess with sub level CSS files at the player level. Scoping will be off.
					// Ignore this for now
					//echo("Ignoring buried CSS file for product " . $file);
				} else {
					$this_script_size = filesize($root. $file);
					array_push($cssFiles, array('file'=>$file, 'filesize'=>$this_script_size));
					$css_size = $css_size + $this_script_size;
					//echo("<p class=\"dissolve in\">" . $file . "(" . format_bytes($this_script_size) . ")</p>");
					$cssContents .= file_get_contents($root . $file);
					$link->setAttribute('href', $css_min_file);
					// If more Link nodes we need to remove those elements
					$totalfiles++;
				}
			}
		}
		//echo("<p class=\"large yellow dissolve in\"><b>Total CSS:</b> " . format_bytes($css_size) . " </p>");
		//echo("<p class=\"dissolve in\">CSS File(s) have been merged... minifying with skills learned from <a href=\"http://www.lateralcode.com/css-minifier\" target=\"_blank\">here</a>...</p>");
		//$css = CssMin::minify($cssContents); // This broke alot of the webkit stuff
		$css = minifycss($cssContents); // New one
		if($savefiles) {
			$handle = fopen($css_minified, "w") or die("<p>Can't open file. </p>");
			if (fwrite($handle, trim($css)) == FALSE) {
				//echo("<p class=\"dissolve in\">Error writing to " . $css_minified . "</p>");
			}
			fclose($handle);
			//echo("<p class=\"dissolve in\">" . $css_minified . " saved.</p>");
		}
		$css_minified_size = filesize($css_minified);
		$css_reduced_size = $css_size - $css_minified_size;
		$cssSavings = percent($css_reduced_size, $css_size);
		//echo("<p class=\"large green dissolve in\"><b>CSS Merged/Minified size:</b> " . format_bytes($css_minified_size) . ".  <span class=\"imphasis\">Reduction of " . $cssSavings . "%!</span></p><br />");	
	}
	// End CSS
	
	// Lets get all the JavaScript  <script> elements ////////////////////////////////////////////
	//echo("<h1 class=\"dissolve in\"><p>Let's take a look at your JS...</p></h1>");
	$scriptElements      = $doc->getElementsbyTagName('script');
	$javascript          = '';
	$js_size     = 0;
	$jsFiles = array();
	$domElemsToRemove    = array();
	$refNode;
	if(!is_null($scriptElements)) {
		//echo("<p class=\"dissolve in\">" . $scriptElements->length . " <code>&lt;script&gt;</code> tag(s) exist:</p>");
		$i = 0;
		foreach($scriptElements as $script) {
			$file = $script->getAttribute('src');
			if($file != '') {
				$needle = strpos($file, "pack");
				if($needle) {
					die("<p class=\"large red dissolve in\"><b>Error</b>: Developer, please don't re-pack a packed javascript file.</p>");
				} else {
					$this_script_size = filesize($root. $file);
					$js_size = $js_size + $this_script_size;
					$jsContents = file_get_contents($root . $file);
					$numlines = 0;
					$msg = "OK";
					// JSLint Online: Note this requires that the server have access to the WWW
					/*$engine = new JSLEngine('tools/jslnt/jsl', 'tools/jslnt/jsl.default.conf');
					$result = $engine->Lint($jsContents);
					
					if ($result === true) {
					   //OutputLintHTML($engine); // this is for showing the code
					   $numlines = $engine->getNumLines();
					   //$msg = $engine->getMessage();
					} else {
					   echo '<b>' . htmlentities($result) . '</b>';
					}*/
					array_push($jsFiles, array('file'=>$file, 'filesize'=>$this_script_size, 'numlines'=>$numlines, 'message'=>$msg));
					//echo("<p class=\"dissolve in\">" . $file . "(" . format_bytes($this_script_size) . ")</p>");
					// We need to check for BOM issues in these files.  They end up putting in rogue characters and breaking code.
					$bom = strpos($jsContents, "﻿");
					if($bom) {
						//echo("<p class=\"yellow dissolve in\">Developer, you have UTF8 BOM in your " . $file . ".<br />Please remove the BOM, however, I'm scrubbing it off for you.</p>");
						$jsContents = str_replace("﻿", "", $jsContents);
					}
					$javascript .= JSMin::minify($jsContents);
					
					// Remove the <script> element from the HTML
					$domElemsToRemove[] = $script;
					$totalfiles++;
				}
			} else {
				$refNode = $script; // store the element we want to put the new <script> tag before
				// Need to switch enableDebug off
				$embeddedJSVars = $script->nodeValue;
				//echo("<br />");
				//echo("<p class=\"dissolve in\"><b>Embedded JavaScript in HTML:</b> <code>" . $embeddedJSVars . "</code></p>");
				$enableDebugPos = strpos($embeddedJSVars, "enableDebug=1");
				if($enableDebugPos) {
					//echo("<p class=\"yellow dissolve in\"><b>Developer:</b> I'm switching enableDebug from 1 for production release.<br />Remember you can pass '.html?enableDebug=1' to turn it back on.</p>");
					$embeddedJSVars = str_replace("enableDebug=1,", "", $embeddedJSVars);
					$script->nodeValue = $embeddedJSVars;
				}
			}
		}
		//echo("<br />");
		// Clean up the <script> tags
		foreach($domElemsToRemove as $domElement) {
			$domElement->parentNode->removeChild($domElement);
		}
		
		//echo("<p class=\"large yellow dissolve in\"><b>Total JS:</b> " . format_bytes($js_size) . " </p>");	
	}
	
	if($savefiles) {
		$handle = fopen($js_minified, "w") or die("<p>Can't open file. </p>");
		if (fwrite($handle, $javascript) == FALSE) {
			//echo("<p class=\"dissolve in\">Error writing to " . $js_minified . "</p>");
		}
		fclose($handle);
	}
	//echo("<p class=\"dissolve in\">JS File(s) have been merged... packing with skills learned from <a href=\"http://joliclic.free.fr/php/javascript-packer/en/\" target=\"_blank\">here</a>...</p>");
	$packer = new ECMAScriptPacker();
	$packed = trim($packer->pack($javascript));
	if($savefiles) {
		$handle = fopen($js_packed, "w") or die("<p>Can't open file. </p>");
		if (fwrite($handle, $packed) == FALSE) {
			echo("<p class=\"dissolve in\">Error writing to " . $js_packed . "</p>");
		}
		fclose($handle);
		//echo("<p class=\"dissolve in\">" . $js_packed . " saved</p>");
		$js_packed_size = filesize($js_packed);
		$js_reduced_size = $js_size - $js_packed_size;
		$jsSavings = percent($js_reduced_size, $js_size);
		//echo("<p class=\"large green dissolve in\"><b>JS Merged/Packed size:</b> " . format_bytes($js_packed_size) . ".  <span class=\"imphasis\">Reduction of " . $jsSavings . "%!</span></p><br />");
	
	}
	// Developer: The following has been abandoned since External CSS/JS files are more optimally cached by the browser
	/*
	// Create the new <script> element to contain the packed data
	$scriptElem = $doc->createElement("script"); // $packed
	$packedText = $doc->createTextNode($packed);
	$scriptElem->appendChild($packedText);
	$scriptElem->setAttribute("type", "text/javascript");
	$refNode->parentNode->insertBefore($scriptElem, $refNode);
	*/
	// Instead lets just point it to the file.
	$scriptElem = $doc->createElement("script"); // $packed
	$scriptElem->setAttribute('type', 'text/javascript');
	$scriptElem->setAttribute('src', $js_pack_file);
	$refNode->parentNode->insertBefore($scriptElem, $refNode); // consider placing at bottom of HTML document
	// End JavaScript handling
		
	// Need to Strip Comments from HTML
	//echo("<h1 class=\"dissolve in\"><p>Let's take a look at your HTML...</p></h1>");
	$html_size = filesize($root . $srchtmlfile);
	//echo("<p class=\"large yellow dissolve in\"><b>Total HTML:</b> " . format_bytes($html_size) . " </p>");	
	//echo("<p class=\"\">Removing HTML Comments with skills I learned <a href=\"http://www.aota.net/forums/printthread.php?t=12016&pp=10\" target=\"\">here</a>...</p>");
	$htmlContent             = preg_replace('/<!--(.|\s)*?-->/', '', $doc->saveHTML());
	//echo("<p class=\"\">Removing Whitespace with skills I learned <a href=\"http://websitetips.com/articles/optimization/html/crunch/\" target=\"\">here</a>...</p>");
	
	$htmlContent             = stripwhitespace($htmlContent);
	$doc                     = new DOMDocument();
	$doc->loadHTML(trim($htmlContent));
	$doc->preserveWhiteSpace = false;
	$doc->formatOutput       = false;
	$doc->saveHTMLFile($root . $htmlfile);
	$totalfiles++;
	//echo("<p class=\"dissolve in\">" . $htmlfile . " saved.</p>");

	$html_min_size = filesize($root . $htmlfile);
	$html_reduced_size = $html_size - $html_min_size;
	$htmlSavings = percent($html_reduced_size, $html_size);
	//echo("<p class=\"large green dissolve in\"><b>HTML Minified size:</b> " . format_bytes($html_min_size) . ".  <span class=\"imphasis\">Reduction of " . $htmlSavings . "%!</span></p>");
		
	//echo("<br />");
	//echo("<h1><p class=\"green dissolve in\">The production build has finished, review it <a href=\"" . $root . $htmlfile . "\" target=\"_blank\">here</a>!</p></h1><br />");
	//echo("<h1><p>The way of the dragon.</p></h1>");
	//$combinedSavings = $htmlSavings + $cssSavings + $jsSavings;  Technically not good math.
	$combinedTotalSize    = $css_size + $js_size + $html_size;
	$combinedTotalReduced = $css_reduced_size + $js_reduced_size + $html_reduced_size;
	$combinedTotalMinSize = $css_minify_size + $js_packed_size + $html_min_size;
	$combinedTotalReducedDiff = $combinedTotalSize - $combinedTotalMinSize; //$combinedTotalReduced; 
	
	$estimatedCSSGZIPDiff = 0.7 * $css_minified_size; // roughly 70% average based on http://aruljohn.com/gziptest.php
	$css_gzip_size        = $css_minified_size - $estimatedCSSGZIPDiff;
	
	$estimatedJSGZIPDiff     = 0.55 * $js_packed_size; // roughly 55% average based on http://aruljohn.com/gziptest.php
	$js_gzip_size            = $js_packed_size - $estimatedJSGZIPDiff;
	
	$estimatedHTMLGZIPDiff   = 0.66 * $html_min_size; // roughly 70% average based on http://aruljohn.com/gziptest.php
	$html_gzip_size          = $html_min_size - $estimatedHTMLGZIPDiff;
	
	//echo("<p> Total Original: " . $combinedTotalSize . " Total Reduced: " . $combinedTotalReduced . "</p>");
	//echo("<h1><p>I saved you <span class=\"imphasis\">" . percent($combinedTotalReducedDiff, $combinedTotalSize) . "%</span> total, you owe me a cookie.</p></h1>");
	$total_gzip_size = $css_gzip_size + $js_gzip_size + $html_gzip_size;
	$estimatedGZIPDiff     = $combinedTotalSize - $total_gzip_size; // roughly 40% total average (this is just bad math .. use the totals above for accuracy
	//echo("<h1><p>With Gzipping on the server you can expect to have this entire package around " . format_bytes($estimatedGZIP)  . " <i>vs</i>. the " . format_bytes($combinedTotalSize) . " before.</p></h1>");
	//echo("<h1><p>Estimated total savings:  <span class=\"imphasis\">" . percent($estimatedGZIPDiff, $combinedTotalSize)  . "%</span>!</p></h1>");
	
	// Lets write out into the new Nutrition Facts
	echo("<div class=\"ntitle\">Nutrition Facts</div>");
		echo("<div class=\"nheader indent\">");
			echo("<p>Reduced Serving Size 1 site " . format_bytes($combinedTotalMinSize) . " / *(<i>" . format_bytes($total_gzip_size)  . ") gzip estimated</i></p>");
			echo("<p>Reduced HTTP Servings Per Batch 3 Files</p>");
		echo("</div>");
		
		echo("<div class=\"nblackbar thick\"></div>");
		
			echo("<div class=\"nrow\">");
				echo("<div class=\"ncell floatL\"><p>Original Amount Per Serving</p></div>");
		echo("</div>");
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p>HTTP Hits " . $totalfiles . "</p></div>");
			echo("<div class=\"ncell floatR\"><p>Total Kilobytes " . format_bytes($combinedTotalSize) . "</p></div>");
		echo("</div>");
		
		echo("<div class=\"nblackbar thin\"></div>");
				
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatR\"><p><b>% Reduction Value *</b></p></div>");
		echo("</div>");
		
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p><b>Total Fat</b> " . format_bytes($combinedTotalReducedDiff) . " / *(<i>" . format_bytes($estimatedGZIPDiff) . "</i>)</p></div>");
			echo("<div class=\"ncell floatR\"><p></p></div>"); // " . percent($combinedTotalReducedDiff, $combinedTotalSize) . "%
		echo("</div>");
		
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p><b>Cascading Style Sheet Fat</b> " . format_bytes($css_reduced_size) . "</p></div>");
			echo("<div class=\"ncell floatR\"><p>" . $cssSavings . "%</p></div>"); // Use $combinedTotalSize to get a mathematical %
		echo("</div>");
			for ($i=0; $i<count($cssFiles); $i++) {
				echo("<div class=\"nrow\">");
					echo("<div class=\"ncell floatL moreindent\"><p>" . $cssFiles[$i]['file'] . " (" . format_bytes($cssFiles[$i]['filesize']) . ")</p></div>");
				echo("</div>");
			}
		
		
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p><b>JavaScript Fat</b> " . format_bytes($js_reduced_size) . "</p></div>");
			echo("<div class=\"ncell floatR\"><p>" . $jsSavings . "%</p></div>");
		echo("</div>");
			for ($i=0; $i<count($jsFiles); $i++) {
				echo("<div class=\"nrow\">");
					echo("<div class=\"ncell floatL moreindent\"><p>" . $jsFiles[$i]['file'] . " (" . format_bytes($jsFiles[$i]['filesize']) . ")</p></div>");
				echo("</div>");
			}
		
		
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p><b>HyperText Markup Language Fat</b> " . format_bytes($html_reduced_size) . "</p></div>");
			echo("<div class=\"ncell floatR\"><p>" . $htmlSavings . "%</p></div>");
		echo("</div>");
				
				echo("<div class=\"nrow\">");
					echo("<div class=\"ncell floatL moreindent\"><p>" . $htmlfile . " (" . format_bytes($html_size) . ")</p></div>");
				echo("</div>");
	
		echo("<div class=\"nblackbar thin\"></div>");
				
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p>Total Savings</p></div>");
			echo("<div class=\"ncell floatR\"><p>" .  percent($combinedTotalReducedDiff, $combinedTotalSize) . "%</p></div>");
		echo("</div>");
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p>Estimated Gzip Savings</p></div>");
			echo("<div class=\"ncell floatR\"><p>" .  percent($estimatedGZIPDiff, $combinedTotalSize) . "%</p></div>");
		echo("</div>");	
		
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p>Vitamin CSS:</p></div>");
			echo("<div class=\"ncell floatR\"><p>" .  format_bytes($css_minified_size) . " / *(<i>" . format_bytes($css_gzip_size) . "</i>)</p></div>");
		echo("</div>");

		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p>Vitamin JS:</p></div>");
			echo("<div class=\"ncell floatR\"><p>" .  format_bytes($js_packed_size) . " / *(<i>" . format_bytes($js_gzip_size) . "</i>)</p></div>");
		echo("</div>");
		
		echo("<div class=\"nrow\">");
			echo("<div class=\"ncell floatL\"><p>Vitamin HTML:</p></div>");
			echo("<div class=\"ncell floatR\"><p>" .  format_bytes($html_min_size) . " / *(<i>" . format_bytes($html_gzip_size) . "</i>)</p></div>");
		echo("</div>");

?>
				
					<!--div class="nblackbar thin"></div-->
					<div class="nfooter">
						<p>* The Percent Reduction Values are based on a the amount of Whitespace, Comments, Variable Shrinking and File Merging, along with estimated gzipping. The values here may not be 100% accurate because the recipes have not been professionally evaluated nor have they been evaluated by the U.S. FDA.</p>
					</div>
					<div class="nblackbar thin"></div>
					<div class="nfooter">
						<p><b>Ingredients:</b> Pre-Hypertext-Processor(PHP),  <a href="http://www.lateralcode.com/css-minifier" target="_blank">Stunningly Simple CSS Minifier</a>, <a href="https://github.com/rgrove/jsmin-php/" target="_blank">JSMin</a>, <a href="http://joliclic.free.fr/php/javascript-packer/en/" target="_blank">Packer</a>, <a href="http://www.aota.net/forums/printthread.php?t=12016&pp=10" target="_blank">HTML Minify</a>.</p>
						<br />
						<p><b>MAY CONTAIN TRACES OF PEANUTS AND LOOSE COUPLING</b></p>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>