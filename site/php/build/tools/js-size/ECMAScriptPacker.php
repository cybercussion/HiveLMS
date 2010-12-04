<?php

// http://dean.edwards.name/packer/

/*
	packer, version 2.0 (beta) (2005/02/01)
	Copyright 2004-2005, Dean Edwards
	Web: http://dean.edwards.name/

	This software is licensed under the CC-GNU LGPL
	Web: http://creativecommons.org/licenses/LGPL/2.1/
	
	Ported to C# by Jesse Hansen, twindagger2k@msn.com

	Ported to PHP by Valentin Agachi, http://agachi.name/
*/

define('PACKER_ENCODING_NONE', 0);
define('PACKER_ENCODING_NUMERIC', 10);
define('PACKER_ENCODING_MID', 36);
define('PACKER_ENCODING_NORMAL', 62);
define('PACKER_ENCODING_HIGHASCII', 95);

require(dirname(__FILE__).'/ParseMaster.php');

class ECMAScriptPacker
{
	private $encoding = PACKER_ENCODING_NORMAL;
	private $fastDecode = true;
	private $specialChars = false;
	private $enabled = true;

	var $IGNORE = '$1';

	private $encodingLookup;

	function __construct($encoding = PACKER_ENCODING_NORMAL, $fastDecode = true, $specialChars = false)
	{
		$this->encoding = $encoding;
		$this->fastDecode = $fastDecode;
		$this->specialChars = $specialChars;
	}

	function Pack($script)
	{
		$script .= "\n";
		$script = $this->basicCompression($script);
		if ($this->specialChars) {
			$script = $this->encodeSpecialChars($script);
		}
		if ($this->encoding != PACKER_ENCODING_NONE) {
			$script = $this->encodeKeywords($script);
		}
		return $script;
	}

	//zero encoding - just removal of whitespace and comments
	private function basicCompression($script)
	{
		$parser = new ParseMaster();
		// make safe
		$parser->escapeChar = "\\";
		// protect strings
		$parser->Add("'[^'\\n\\r]*'", $this->IGNORE);
		$parser->Add("\"[^\"\\n\\r]*\"", $this->IGNORE);
		// remove comments
		$parser->Add("\\/\\/[^\\n\\r]*[\\n\\r]");
		$parser->Add("\\/\\*[^*]*\\*+([^\\/][^*]*\\*+)*\\/");
		// protect regular expressions
		$parser->Add("\\s+(\\/[^\\/\\n\\r\\*][^\\/\\n\\r]*\\/g?i?)", '$2');
		$parser->Add("[^\\w\\$\\/'\"*)\\?:]\\/[^\\/\\n\\r\\*][^\\/\\n\\r]*\\/g?i?", $this->IGNORE);
		// remove: ;;; doSomething();
		if ($this->specialChars) {
			$parser->Add(";;[^\\n\\r]+[\\n\\r]");
		}
		// remove redundant semi-colons
		$parser->Add(";+\\s*([};])", '$2');
		// remove white-space
		$parser->Add("(\\b|\\$)\\s+(\\b|\\$)", '$2 $3');
		$parser->Add("([+\\-])\\s+([+\\-])", '$2 $3');
		$parser->Add("\\s+");
		// done
		return $parser->Exec($script);
	}

	private function encodeSpecialChars($script)
	{
		$parser = new ParseMaster();
		// replace: $name -> n, $$name -> na
		$parser->Add("((\\$+)([a-zA-Z\\$_]+))(\\d*)", array(&$this, 'encodeLocalVars'));

		// replace: _name -> _0, double-underscore (__name) is ignored
		$regex = "~\\b_[A-Za-z\\d]\\w*~";
		
		// build the word list
		$this->encodingLookup = $this->analyze($script, $regex, array(&$this, 'encodePrivate'));

		$parser->Add("\\b_[A-Za-z\\d]\\w*", array(&$this, 'encodeWithLookup'));
		
		$script = $parser->Exec($script);
		return $script;
	}
	private function encodeKeywords($script)
	{
		// escape high-ascii values already in the script (i.e. in strings)
		if ($this->encoding == PACKER_ENCODING_HIGHASCII) {
			$script = $this->escape95($script);
		}
		// create the parser
		$parser = new ParseMaster();
		$encode = $this->getEncoder($this->encoding);

		// for high-ascii, don't encode single character low-ascii
		$regex = '~'.(($this->encoding == PACKER_ENCODING_HIGHASCII) ? "\\w\\w+" : "\\w+").'~';

		// build the word list
		$this->encodingLookup = $this->analyze($script, $regex, $encode);

		// encode
		$parser->Add(($this->encoding == PACKER_ENCODING.HIGHASCII) ? "\\w\\w+" : "\\w+", array(&$this, 'encodeWithLookup'));

		// if encoded, wrap the script in a decoding function
		return ($script == '') ? "" : $this->bootStrap($parser->Exec($script), $this->encodingLookup);
	}

	private function bootStrap($packed, $keywords)
	{
		// packed: the packed script

		$packed = "'" . $this->escape($packed) . "'";

		// ascii: base for encoding
		$ascii = min(count($keywords['Sorted']), (int)$this->encoding);
		if ($ascii == 0)
			$ascii = 1;

		// count: number of words contained in the script
		$count = count($keywords['Sorted']);

		// keywords: list of words contained in the script
		foreach ($keywords['Protected'] as $key => $item)
		{
			$keywords['Sorted'][$key] = "";
		}

		// convert from a string to an array
		$keywordsout = "'";
		foreach ($keywords['Sorted'] as $word)
			$keywordsout .= $word.'|';
		$keywordsout = substr($keywordsout, 0, -1)."'.split('|')";

		$encode = '';
		$inline = "c";

		switch ($this->encoding)
		{
			case PACKER_ENCODING_MID:
				$encode = "function(c){return c.toString(36)}";
				$inline .= ".toString(a)";
				break;
			case PACKER_ENCODING_NORMAL:
				$encode = "function(c){return(c<a?\"\":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))}";
				$inline .= ".toString(a)";
				break;
			case PACKER_ENCODING_HIGHASCII:
				$encode = "function(c){return(c<a?\"\":e(c/a))+String.fromCharCode(c%a+161)}";
				$inline .= ".toString(a)";
				break;
			default:
				$encode = "function(c){return c}";
				break;
		}
		
		// decode: code snippet to speed up decoding
		$decode = "";
		if ($this->fastDecode)
		{
			$decode = "if(!''.replace(/^/,String)){while(c--)d[e(c)]=k[c]||e(c);k=[function(e){return d[e]}];e=function(){return'\\\\\w+'};c=1;}";
			if ($this->encoding == PACKER_ENCODING_HIGHASCII) {
				$decode = str_replace('\\\\\w', '[\\xa1-\\xff]', $decode);
			} elseif ($this->encoding == PACKER_ENCODING_NUMERIC) {
				$decode = str_replace("e(c)", $inline, $decode);
			}
			if ($count == 0) {
				$decode = str_replace("c=1", "c=0", $decode);
			}
		}

		// boot function
		$unpack = "function(p,a,c,k,e,d){while(c--)if(k[c])p=p.replace(new RegExp('\\\\b'+e(c)+'\\\\b','g'),k[c]);return p;}";
		$r = '';
		if ($this->fastDecode)
		{
			//insert the decoder
			$r = "~\\{~";
			$unpack = preg_replace($r, '{'.$decode.';', $unpack, 1);
		}

		if ($this->encoding == PACKER_ENCODING_HIGHASCII)
		{
			// get rid of the word-boundries for regexp matches
			$r = "~'\\\\\\\\b'\\s*\\+|\\+\\s*'\\\\\\\\b'~";
			$unpack = preg_replace($r, '', $unpack);
		}
		if ($this->encoding == PACKER_ENCODING_HIGHASCII || $ascii > PACKER_ENCODING_NORMAL || $this->fastDecode)
		{
			// insert the encode function
			$r = "~\\{~";
			$unpack = preg_replace($r, '{e='.$encode.';', $unpack, 1);
		}
		else
		{
			$r = "~e\\(c\\)~";
			$unpack = preg_replace($r, $inline, $unpack);
		}
		// no need to pack the boot function since i've already done it
		$_params = "".$packed.",".$ascii.",".$count.",".$keywordsout;
		if ($this->fastDecode)
		{
			//insert placeholders for the decoder
			$_params .= ",0,{}";
		}
		// the whole thing
		return "eval(".$unpack."(".$_params."));\n";
	}

	private function escape($input)
	{
		$r = "~([\\\'])~";
		return preg_replace($r, '\\\\$1', $input);
	}

	private function getEncoder($encoding)
	{
		switch ($encoding)
		{
			case PACKER_ENCODING_MID:
				return array(&$this, 'encode36');
				break;
			case PACKER_ENCODING_NORMAL:
				return array(&$this, 'encode62');
				break;
			case PACKER_ENCODING_HIGHASCII:
				return array(&$this, 'encode95');
				break;
			default:
				return array(&$this, 'encode10');
		}
	}

	private function encode10($code)
	{
		return (string)$code;
	}

	private $lookup36 = "0123456789abcdefghijklmnopqrstuvwxyz";
	private function encode36($code)
	{
		$encoded = '';
		$i = 0;
		do {
			$digit = ($code / (int)bcpow(36, $i)) % 36;
			$encoded = $this->lookup36{$digit}.$encoded;
			$code -= $digit * (int)bcpow(36, $i++);
		} while ($code > 0);
		return $encoded;
	}

	private $lookup62 = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	private function encode62($code)
	{
		$encoded = '';
		$i = 0;
		do {
			$digit = ($code / (int)bcpow(62, $i)) % 62;
			$encoded = $this->lookup62{$digit}.$encoded;
			$code -= $digit * (int)bcpow(62, $i++);
		} while ($code > 0);
		return $encoded;
	}

	private $lookup95 = "¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿ";
	private function encode95($code)
	{
		$encoded = '';
		$i = 0;
		do {
			$digit = ($code / (int)bcpow(95, $i)) % 95;
			$encoded = $this->lookup95{$digit}.$encoded;
			$code -= $digit * (int)bcpow(95, $i++);
		} while ($code > 0);
		return $encoded;
	}

	private function escape95($input)
	{
		$r = "~[\xa1-\xff]~";
		return preg_replace_callback($r, array(&$this, 'escape95Eval'), $input);
	}

	private function escape95Eval($match)
	{
		return "\\x".dechex(ord($match[0])); //return hexadecimal value
	}

	public function encodeLocalVars($match, $offset)
	{
		$length = strlen($match[$offset + 2]);
		$start = $length - max($length - strlen($match[$offset + 3]), 0);
		return substr($match[$offset + 1], $start, $length) . $match[$offset + 4];
	}

	public function encodeWithLookup($match, $offset)
	{
		return (string)$this->encodingLookup['Encoded'][$match[$offset]];
	}

	private function encodePrivate($code)
	{
		return "_".$code;
	}

	private function analyze($input, $regex, $encodeMethod)
	{
		// retreive all words in the script
		preg_match_all($regex, $input, $all, PREG_SET_ORDER);
		$rtrn = array();
		$rtrn['Sorted'] = array(); // list of words sorted by frequency
		$rtrn['Protected'] = array(); // dictionary of word->encoding
		$rtrn['Encoded'] = array(); // instances of "protected" words
		if (count($all) > 0)
		{
			$unsorted = array(); // same list, not sorted
			$Protected = array(); // "protected" words (dictionary of word->"word")
			$values = array(); // dictionary of charCode->encoding (eg. 256->ff)
			$count = array(); // word->count
			$i = count($all); $j = 0;
			$word = '';
			// count the occurrences - used for sorting later
			do {
				$word = '$'.$all[--$i][0];
				if (!isset($count[$word]))
				{
					$count[$word] = 0;
					array_push($unsorted, $word);
					// make a dictionary of all of the protected words in this script
					//  these are words that might be mistaken for encoding
					$values[$j] = call_user_func($encodeMethod, $j);
					$Protected['$'.$values[$j]] = $j++;
				}
				// increment the word counter
				$count[$word] = (int)$count[$word] + 1;
			} while ($i > 0);
			// prepare to sort the word list, first we must protect words that are also used as codes. we assign them a code
			// equivalent to the word itself.  
			// e.g. if "do" falls within our encoding range then we store keywords["do"] = "do"; 
			// this avoids problems when decoding
			$i = count($unsorted);
			$sortedarr = array();
			$sortedarr = array_pad($sortedarr, count($unsorted), '');
			do {
				$word = $unsorted[--$i];
				if (isset($Protected[$word]))
				{
					$sortedarr[(int)$Protected[$word]] = substr($word, 1);
					$rtrn['Protected'][(int)$Protected[$word]] = true;
					$count[$word] = 0;
				}
			} while ($i > 0);

			$unsortedarr = $unsorted;
			// sort the words by frequency
			$this->sortCount = $count;
			usort($unsortedarr, array(&$this, 'sortCompare'));

			$j = 0;
			// because there are "protected" words in the list
			// we must add the sorted words around them
			do {
				if (!strlen($sortedarr[$i])) {
					$sortedarr[$i] = substr($unsortedarr[$j], 1);
					$j++;
				}
				$rtrn['Encoded'][$sortedarr[$i]] = $values[$i];
			} while (++$i < count($unsortedarr));
			$rtrn['Sorted'] = $sortedarr;
		}
		return $rtrn;
	}

	private function sortCompare($x, $y)
	{
		return (int)$this->sortCount[$y] - (int)$this->sortCount[$x];
	}

}

?>