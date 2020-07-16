<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nopis\Lib\Latte\Runtime;

use Nopis\Lib\Latte\LatteObject;


/**
 * HTML literal.
 *
 * @author     David Grudl
 */
class Html extends LatteObject implements IHtmlString
{
	/** @var string */
	private $value;


	public function __construct($value)
	{
		$this->value = (string) $value;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
