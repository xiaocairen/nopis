<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nopis\Lib\Config\Neon;

/**
 * Simple parser & generator for Nette Object Notation.
 *
 * @author     David Grudl
 */
class Neon
{

	/**
	 * Decodes a NEON string.
	 * @param  string
	 * @return mixed
	 */
	public static function parse($input)
	{
		$decoder = new Decoder();
		return $decoder->decode($input);
	}

}
