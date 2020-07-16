<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 */

namespace Nopis\Lib\Latte;


/**
 * Latte macro.
 *
 * @author     David Grudl
 */
interface IMacro
{

	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	function initialize();

	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	function finalize();

	/**
	 * New node is found. Returns FALSE to reject.
	 * @return bool
	 */
	function nodeOpened(MacroNode $node);

	/**
	 * Node is closed.
	 * @return void
	 */
	function nodeClosed(MacroNode $node);

}
