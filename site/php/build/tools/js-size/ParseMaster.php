<?php

/*
	ParseMaster, version 1.0 (pre-release) (2005/02/01) x4
	Copyright 2005, Dean Edwards
	Web: http://dean.edwards.name/

	This software is licensed under the CC-GNU LGPL
	Web: http://creativecommons.org/licenses/LGPL/2.1/

    Ported to C# by Jesse Hansen, twindagger2k@msn.com

	Ported to PHP by Valentin Agachi, http://agachi.name/
*/

class ParseMaster 
{
	// used to determine nesting levels
	var $GROUPS = "~\\(~";
	var $SUB_REPLACE = '~\\$~';
	var $INDEXED = '~^\\$\\d+$~';
	var $ESCAPE = "~\\\\.~";
	var $QUOTE = "~'~";
	var $DELETED = "~\\x01[^\\x01]*\\x01~";

	/// <summary>
	/// Delegate to call when a regular expression is found.
	/// Use match.Groups[offset + &lt;group number&gt;].Value to get
	/// the correct subexpression
	/// </summary>
//	public delegate string MatchGroupEvaluator(Match match, int offset);

	private function DELETE($match, $offset)
	{
		return "\x01".$match[$offset]."\x01";
	}

	var $ignoreCase = false;
	var $escapeChar = '\0';

	private $patterns = array();

	private $escaped = array();
	private $unescapeIndex = 0;

	/// <summary>
	/// Add an expression to be replaced with the replacement string
	/// </summary>
	/// <param name="expression">Regular Expression String</param>
	/// <param name="replacement">Replacement String. Use $1, $2, etc. for groups</param>
	public function Add($expression, $replacement = '')
	{
		if (is_string($replacement)) {
			if ($replacement == '') {
				$this->_add($expression, array(&$this, 'DELETE'));
			}
			$this->_add($expression, $replacement);
		} elseif (is_array($replacement)) {
			$this->_add($expression, $replacement);
		}
	}

	public function Exec($input)
	{
//		return DELETED.Replace(unescape(getPatterns().Replace(escape(input), new MatchEvaluator(replacement))), string.Empty);
		//long way for debugging
		$input = $this->escape($input);
		$patterns = $this->getPatterns();
		$input = preg_replace_callback($patterns, array(&$this, 'replacement'), $input);
		$input = $this->unescape($input);
		$input = preg_replace($this->DELETED, '', $input);
		return $input;
	}

	private function _add($expression, $replacement)
	{
		$pattern = array();
		$pattern['expression'] = $expression;
		$pattern['replacement'] = $replacement;

		//count the number of sub-expressions
		// - add 1 because each group is itself a sub-expression
		$pattern['length'] = preg_match_all($this->GROUPS, $expression, $res) + 1;

		//does the pattern deal with sup-expressions?
		if (is_string($replacement) && preg_match($this->SUB_REPLACE, $replacement))
		{
			$sreplacement = $replacement;
			// a simple lookup (e.g. $2)
			if (preg_match($this->INDEXED, $sreplacement))
			{
				$pattern['replacement'] = ((int)substr($sreplacement, 1)) - 1;
			}
		}

		array_push($this->patterns, $pattern);
	}

	/// <summary>
	/// builds the patterns into a single regular expression
	/// </summary>
	/// <returns></returns>
	private function getPatterns()
	{
		$rtrn = '';
		reset($this->patterns);
		foreach ($this->patterns as $pattern)
		{
			$rtrn .= '('.$pattern['expression'].')|';
		}
		$rtrn = substr($rtrn, 0, -1);
		return '~'.$rtrn.'~'.($this->ignoreCase ? 'i' : '');
	}

	/// <summary>
	/// Global replacement function. Called once for each match found
	/// </summary>
	/// <param name="match">Match found</param>
	private function replacement($match)
	{
//		echo $match[0]."\n";
		$i = 1; $j = 0;
		//loop through the patterns
		while (($pattern = $this->patterns[$j++]) && is_array($pattern) && count($pattern))
		{
			//do we have a result?
			if ($match[$i] != '')
			{
				$replacement = $pattern['replacement'];
				$r = '';
				if (is_array($replacement))
				{
					$r = call_user_func($replacement, $match, $i);
				}
				else if (is_int($replacement))
				{
					$r = $match[((int)($replacement + $i))];
				}
				else
				{
					//string, send to interpreter
					$r = $this->replacementString($match, $i, (string)$replacement, $pattern['length']);
				}
//				print_r(is_Array($replacement) ? $replacement[1] : $replacement);
//				echo ' Result: '.$r."\n";
				return $r;
			}
			else {
				//skip over references to sub-expressions
				$i += $pattern['length'];
			}
		}
		return $match[0]; //should never be hit, but you never know
	}

	/// <summary>
	/// Replacement function for complicated lookups (e.g. Hello $3 $2)
	/// </summary>
	private function replacementString($match, $offset, $replacement, $length)
	{
		while ($length > 0)
		{
			$replacement = str_replace('$'.($length--), $match[$offset + $length], $replacement);
		}
		return $replacement;
	}
	
	//encode escaped characters
	private function escape($str)
	{
		if ($this->escapeChar == '\0') {
			return $str;
		}
		$escaping = "~\\\\(.)~";
		return preg_replace_callback($escaping, array(&$this, 'escapeMatch'), $str);
	}

	private function escapeMatch($match)
	{
//		echo 'ESCAPE: '; print_r($match);
		array_push($this->escaped, $match[1]);
//		print_r($this->escaped);
		return "\\";
	}

	//decode escaped characters
	private function unescape($str)
	{
		if ($this->escapeChar == '\0') {
			return $str;
		}
		$unescaping = "~\\".$this->escapeChar.'~';
		return preg_replace_callback($unescaping, array(&$this, 'unescapeMatch'), $str);
	}

	private function unescapeMatch($match)
	{
		return "\\".$this->escaped[$this->unescapeIndex++];
	}

	private function internalEscape($str)
	{
		return preg_replace($this->ESCAPE, '', $str);
	}
}
?>