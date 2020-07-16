<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 */

namespace Nopis\Lib\Latte;


/**
 * Template loader.
 */
interface ILoader
{

	/**
	 * Returns template source code.
	 * @return string
	 */
	function getContent($name);

	/**
	 * Checks whether template is expired.
	 * @return bool
	 */
	function isExpired($name, $time);

	/**
	 * Returns fully qualified template name.
	 * @return string
	 */
	function getChildName($name, $parent = NULL);

}
