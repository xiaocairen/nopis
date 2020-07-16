<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nopis\Lib\Config\Neon;

/**
 * Parser for neon file.
 *
 * @author     Wangbin
 * @internal
 */
class Decoder
{
	/** @var array */
	public static $patterns = [
		'
			\'[^\'\n]*\' |
			"(?: \\\\. | [^"\\\\\n] )*"
		', // string
		'
			(?: [^#"\',:=[\]{}()\x00-\x20!`-] | [:-][^"\',\]})\s] )
			(?:
				[^,:=\]})(\x00-\x20]+ |
				:(?! [\s,\]})] | $ ) |
				[\ \t]+ [^#,:=\]})(\x00-\x20]
			)*
		', // literal / boolean / integer / float
		'
			[,:=[\]{}()-]
		', // symbol
		'?:\#.*', // comment
		'\n[\t\ ]*', // new line + indent
		'?:[\t\ ]+', // whitespace
	];

	private $brackets = [
        '[' => ']',
        '{' => '}',
        '(' => ')',
    ];

    private $consts = [
        'true'  => true,  'True'  => true,  'TRUE'  => true,
        'yes'   => true,  'Yes'   => true,  'YES'   => true,
        'on'    => true,  'On'    => true,  'ON'    => true,
        'false' => false, 'False' => false, 'FALSE' => false,
        'no'    => false, 'No'    => false, 'NO'    => false,
        'off'   => false, 'Off'   => false, 'OFF'   => false,
        'null'  => null,  'Null'  => null,  'NULL'  => null,
    ];

    /** @var string */
    private $input;

    /** @var array */
    private $tokens;

    /** @var array */
    private $lines;

    /** @var int */
    private $curLineNo;

    /** @var int */
    private $base;

    /** @var array */
    private $ref;

    /** @var int */
    private $pos;

    /** @var array */
    private $result;

    /**
     * Decodes a NEON string.
     * @param  string
     * @return mixed
     */
    public function decode($input)
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException(sprintf('Argument must be a string, %s given.', gettype($input)));
        } elseif (substr($input, 0, 3) === "\xEF\xBB\xBF") { // BOM
            $input = substr($input, 3);
        }
        $this->input = "\n" . str_replace("\r", '', $input); // \n forces indent detection

        $pattern      = '~(' . implode(')|(', self::$patterns) . ')~Amix';
        $this->tokens = preg_split($pattern, $this->input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE);
        $last         = end($this->tokens);
        if ($this->tokens && !preg_match($pattern, $last[0])) {
            $this->pos = count($this->tokens) - 1;
            $this->error();
        }

        $key = -1;
        $indents = [];
        foreach ($this->tokens as $k => $el) {
            if ($el[0][0] === "\n") {
                $key++;
                $this->lines[$key]['indent'] = strlen((string) substr($el[0], 1));
                $this->lines[$key]['indent'] !== 0 && $indents[] = $this->lines[$key]['indent'];
            } else {
                $this->lines[$key][$k] = $el[0];
            }
        }
        if ($this->lines[0]['indent'] !== 0) {
            throw new \Exception('First line must has no indentation');
        }

        if (empty($indents)) {
            $this->curLineNo = 0;
            $this->result[$this->lines[0][1]] = $this->getInlineValue();
        } else {
            $this->curLineNo = 0;
            $this->base      = min($indents);
            $this->pos       = 0;
            $this->parse($this->result);
        }

        return $this->result;
    }

    protected function parse(& $result)
    {
        $line      = $this->lines[$this->curLineNo];
        $curIndent = array_shift($line);
        if (empty($line)) { // Skip blank line
            $this->curLineNo++;
            $this->parse($result);
            return;
        }
        if (isset($line[0]) && isset($this->brackets[$line[0]])) { // First char is { [ (
            $this->error($line[0], 'Item can\'t start with \'' . $line[0] . '\'');
        }

        $refKey  = $this->getRefKey($curIndent, $this->curLineNo);
        $lineStr = implode('', $line);

        if (false !== array_search($line[0], $this->brackets)) { // Array closing eg. } ] )
            if (null !== ($nextIndent = $this->getNextIndent())) {
                if ($nextIndent === $curIndent) {
                    $refKey = $this->getRefKey($curIndent, $this->curLineNo) - 1;
                    if (isset($this->ref[$refKey])) {
                        $this->curLineNo++;
                        $this->parse($this->ref[$refKey]);
                    }
                } elseif ($nextIndent < $curIndent) {
                    $this->parseNext($nextIndent);
                } else {
                    $this->error($this->lines[$this->getNextLineKey()][1], 'Bad Indentation');
                }
            }
        } elseif ($line[0] === '-') {
            if (isset($this->brackets[$line[1]])) { // Value is array
                if (count($line) > 2) {
                    $result[] = $this->getInlineValue();
                    if (null !== ($nextIndent = $this->getNextIndent())) {
                        if ($nextIndent === $curIndent) {
                            $this->curLineNo++;
                            $this->parse($result);
                        } elseif ($nextIndent < $curIndent) {
                            $this->parseNext($nextIndent);
                        } else {
                            $this->error($this->lines[$this->getNextLineKey()][1], 'Bad Indentation');
                        }
                    }
                } else { // The line is only  ' -{ '
                    $this->curLineNo++;
                    $this->getMultiLineArray($curIndent, $curIndent, $result);
                }
            } else {    // Value is string
                $result[] = $line[1];
                if (null !== ($nextIndent = $this->getNextIndent())) {
                    if ($nextIndent === $curIndent) {
                        $this->curLineNo++;
                        $this->parse($result);
                    } elseif ($nextIndent < $curIndent) {
                        $this->parseNext($nextIndent);
                    } else {
                        $this->error($this->lines[$this->getNextLineKey()][1], 'Bad Indentation');
                    }
                }
            }
        } elseif (preg_match('/^([^:]+):(.*)$/i', $lineStr, $match)) {

            $key = $line[0];
            if (!empty($result) && array_key_exists($key, $result)) {
                $this->error($line[0], 'The same key is exists');
            }

            $match[2] = trim($match[2]);
            if (!empty($match[2]) || $match[2] === '0') {
                $result[$key] = $this->getInlineValue();
                if (null !== ($nextIndent   = $this->getNextIndent())) {
                    if ($nextIndent === $curIndent) {
                        $this->curLineNo++;
                        $this->parse($result);
                    } elseif ($nextIndent < $curIndent) {
                        $this->parseNext($nextIndent);
                    } else {
                        $this->error($this->lines[$this->getNextLineKey()][1], 'Bad Indentation');
                    }
                }
            } elseif (null !== ($nextIndent = $this->getNextIndent())) {
                $this->ref[$refKey] = & $result[$key];
                if ($nextIndent === $curIndent) {
                    $this->curLineNo++;
                    $result[$key] = '';
                    $this->parse($result);
                } elseif ($nextIndent < $curIndent) {
                    $result[$key] = '';
                    $this->parseNext($nextIndent);
                } else {
                    $this->curLineNo++;
                    $this->parse($result[$key]);
                }
            } else {
                $result[$key] = '';
                return;
            }
        } else {
            $this->error($line[0], 'Item can\'t start with \'' . $line[0] . '\'');
        }
    }

    protected function getMultiLineArray($openIndent, $preIndent, & $result)
    {
        $openIndent = (int) $openIndent;
        $preIndent  = (int) $preIndent;
        $line       = $this->lines[$this->curLineNo];
        $curIndent  = (int) array_shift($line);
        if (isset($this->brackets[$line[0]])) {
            $this->error($line[0], 'Item can\'t start with \'' . $line[0] . '\'');
        }

        if ($curIndent === $openIndent) {
            $refKey = $this->getRefKey($curIndent, $this->curLineNo);

            if ($line[0] === '-') {
                if (isset($this->brackets[$line[1]])) { // Value is array
                    if (count($line) > 2) {
                        $result[] = $this->getInlineValue();
                        if (null !== ($nextIndent = $this->getNextIndent())) {
                            if ($nextIndent === $curIndent) {
                                $this->curLineNo++;
                                $this->parse($result);
                            } elseif ($nextIndent < $curIndent) {
                                $this->parseNext($nextIndent);
                            } else {
                                $this->error($this->lines[$this->getNextLineKey()][1], 'Bad Indentation');
                            }
                        }
                    } else { // The line is only  ' -{ '
                        $this->curLineNo++;
                        $this->getMultiLineArray($openIndent, $curIndent, $result);
                    }
                } else { // Value is string
                    $result[] = $line[1];
                    if (null !== ($nextIndent = $this->getNextIndent())) {
                        if ($nextIndent === $curIndent) {
                            $this->curLineNo++;
                            $this->parse($result);
                        } elseif ($nextIndent < $curIndent) {
                            $this->parseNext($nextIndent);
                        } else {
                            $this->error($this->lines[$this->getNextLineKey()][1], 'Bad Indentation');
                        }
                    }
                }
            } else {
                $this->error($line[0], 'In array, numeric\'s key and string\'s key is not compatible');
            }
        } elseif ($curIndent > $openIndent) {
            $this->parse($result[]);
        } else {
            return;
//            $refKey = $this->getRefKey($curIndent, $this->curLineNo) - 1;
//            if (isset($this->ref[$refKey])) {
//                $this->parse($this->ref[$refKey]);
//                return;
//            }
        }
    }

    protected function parseNext($nextIndent)
    {
        if (0 !== $nextIndent) {
            $refKey = $this->getRefKey($nextIndent, $this->getNextLineKey()) - 1;
            if (isset($this->ref[$refKey])) {
                $this->curLineNo++;
                $this->parse($this->ref[$refKey]);
            }
        } else {
            $this->curLineNo++;
            $this->parse($this->result);
        }
    }

    protected function getInlineValue()
    {
        $line = $this->lines[$this->curLineNo];
        array_shift($line);
        if ($line[0] === '-') {
            $line = array_slice($this->lines[$this->curLineNo], 2);
        } else {
            $line = array_slice($this->lines[$this->curLineNo], 3);
        }
        if (empty($line) && 0 === $this->curLineNo) {
            return null;
        }

        if (count($line) === 1 || (count($line) === 2 && $line[1] === ',')) {
            return $this->translateValue($line[0]);
        } elseif (isset($this->brackets[$line[0]])) {
            return $this->translateJson2Array($line);
        } else {
            $this->error($line[0], 'Item can\'t start with \'' . $line[0] . '\'');
        }
    }

    protected function getRefKey($curIndent, $lineNo)
    {
        if ($this->base === 0) {
            return 0;
        }
        $key = $curIndent / $this->base;
        if (!is_int($key)) {
            $_  = array_slice($this->lines[$lineNo], 1, 1);
            $el = array_pop($_);
            $this->error($el, 'Bad Indentation', $lineNo);
        }
        return $key;
    }

    protected function getNextIndent($offset = 1)
    {
        while (isset($this->lines[$this->curLineNo + 1]) && count($this->lines[$this->curLineNo + 1]) === 1) {
            // Skip blank line
            $this->curLineNo++;
        }
        $key = $this->curLineNo + $offset;
        return isset($this->lines[$key]) ? $this->lines[$key]['indent'] : null;
    }

    protected function getNextLineKey($offset = 1)
    {
        while (isset($this->lines[$this->curLineNo + 1]) && count($this->lines[$this->curLineNo + 1]) === 1) {
            // Skip blank line
            $this->curLineNo++;
        }
        $key = $this->curLineNo + $offset;
        return isset($this->lines[$key]) ? $key : null;
    }

    protected function translateJson2Array($line)
    {
        $value = [];
        $line  = array_slice($line, 1, -1);
        $count = count($line);
        for ($key = 0; $key < $count; $key++) {// echo $key, "\r\n<br>";
            if (isset($this->brackets[$line[$key]])) {
                if (isset($block)) {
                    $block[] = $line[$key];
                    $openBlock++;
                } else {
                    $block[]   = $line[$key];
                    $openBlock = 1;
                }
            } elseif (isset($block)) {
                if (0 === $openBlock) {
                    if (isset($assocKey)) {
                        $value[$assocKey] = $this->translateJson2Array($block);
                        unset($assocKey, $block);
                    } else {
                        $value[] = $this->translateJson2Array($block);
                        unset($block);
                    }
                } elseif (false !== array_search($line[$key], $this->brackets)) {
                    $block[] = $line[$key];
                    $openBlock--;
                    if (0 === $openBlock) {
                        if (isset($assocKey)) {
                            $value[$assocKey] = $this->translateJson2Array($block);
                            unset($assocKey, $block);
                        } else {
                            $value[] = $this->translateJson2Array($block);
                            unset($block);
                        }
                    }
                } elseif (isset($this->brackets[$line[$key]])) {
                    $block[] = $line[$key];
                    $openBlock++;
                } else {
                    $block[] = $line[$key];
                }
            } elseif ($line[$key] === ',' || $line[$key] === ':') {
                continue;
            } else {
                if (!isset($assocKey)) {
                    if (isset($line[$key + 1])) {
                        if ($line[$key + 1] === ':') {
                            $assocKey = $line[$key];
                            continue;
                        } else {
                            $value[] = $this->translateValue($line[$key]);
                        }
                    } else {
                        $value[] = $this->translateValue($line[$key]);
                        continue;
                    }
                } else {
                    $value[$assocKey] = $this->translateValue($line[$key]);
                    unset($assocKey);
                    continue;
                }
            }
        }

        return $value;
    }

    protected function translateValue($val)
    {
        if ($val[0] === '"') {
            $value = preg_replace_callback('#\\\\(?:ud[89ab][0-9a-f]{2}\\\\ud[c-f][0-9a-f]{2}|u[0-9a-f]{4}|x[0-9a-f]{2}|.)#i', array($this, 'cbString'), substr($t, 1, -1));
        } elseif ($val[0] === "'") {
            $value = substr($val, 1, -1);
        } elseif (isset($this->consts[$val]) || array_key_exists($val, $this->consts)) {
            $value = $this->consts[$val];
        } elseif (is_numeric($val)) {
            $value = $val * 1;
        } elseif (preg_match('#\d\d\d\d-\d\d?-\d\d?(?:(?:[Tt]| +)\d\d?:\d\d:\d\d(?:\.\d*)? *(?:Z|[-+]\d\d?(?::\d\d)?)?)?\z#A', $val)) {
            $value = new \DateTime($val);
        } else { // literal
            $value = $val;
        }

        return $value;
    }

    protected function cbString($m)
    {
		static $mapping = array('t' => "\t", 'n' => "\n", 'r' => "\r", 'f' => "\x0C", 'b' => "\x08", '"' => '"', '\\' => '\\', '/' => '/', '_' => "\xc2\xa0");
		$sq = $m[0];
		if (isset($mapping[$sq[1]])) {
			return $mapping[$sq[1]];
		} elseif ($sq[1] === 'u' && strlen($sq) >= 6) {
			$lead = hexdec(substr($sq, 2, 4));
			$tail = hexdec(substr($sq, 8, 4));
			$code = $tail ? (0x2400 + (($lead - 0xD800) << 10) + $tail) : $lead;
			if ($code >= 0xD800 && $code <= 0xDFFF) {
				$this->error("Invalid UTF-8 (lone surrogate) $sq");
			}
			return iconv('UTF-32BE', 'UTF-8//IGNORE', pack('N', $code));
		} elseif ($sq[1] === 'x' && strlen($sq) === 4) {
			return chr(hexdec(substr($sq, 2)));
		} else {
			$this->error("Invalid escaping sequence $sq");
		}
    }

    protected function error($el, $message = "Unexpected '%s'", $line = null)
    {
        if (null === $line) {
            $line = $this->curLineNo;
        }
        $this->pos = array_search($el, $this->lines[$line]);
        $last      = isset($this->tokens[$this->pos]) ? $this->tokens[$this->pos] : null;
        $offset    = $last ? $last[1] : strlen($this->input);
        $text      = substr($this->input, 0, $offset);
        $line      = substr_count($text, "\n");
        $col       = $offset - strrpos("\n" . $text, "\n") + 1;
        $token     = $last ? str_replace("\n", '<new line>', substr($last[0], 0, 40)) : 'end';
        throw new \Exception(str_replace('%s', $token, $message) . " on line $line, column $col.");
    }

}
