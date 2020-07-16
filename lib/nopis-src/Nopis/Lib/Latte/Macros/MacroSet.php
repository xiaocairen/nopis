<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nopis\Lib\Latte\Macros;

use Nopis\Lib\Latte,
	Nopis\Lib\Latte\MacroNode,
    Nopis\Lib\Latte\Compiler,
    Nopis\Lib\Latte\LatteObject,
    Nopis\Lib\Latte\IMacro,
    Nopis\Lib\Latte\PhpWriter,
    Nopis\Lib\Latte\Helpers;


/**
 * Base IMacro implementation. Allows add multiple macros.
 *
 * @author     David Grudl
 */
class MacroSet extends LatteObject implements IMacro
{
	/** @var Latte\Compiler */
	private $compiler;

	/** @var array */
	private $macros;

    /**
     * Constructor.
     *
     * @param Compiler $compiler
     */
    public function __construct(Compiler $compiler)
	{
		$this->compiler = $compiler;
	}


	public function addMacro($name, $begin, $end = NULL, $attr = NULL)
	{
		if (!$begin && !$end && !$attr) {
			throw new \InvalidArgumentException("At least one argument must be specified for macro '$name'.");
		}
		foreach (array($begin, $end, $attr) as $arg) {
			if ($arg && !is_string($arg)) {
				Helpers::checkCallback($arg);
			}
		}

		$this->macros[$name] = array($begin, $end, $attr);
		$this->compiler->addMacro($name, $this);
		return $this;
	}

    /**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
	}


	/**
	 * Finishes template parsing.
     *
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
	}


	/**
	 * New node is found.
	 * @return bool
	 */
	public function nodeOpened(MacroNode $node)
	{
		list($begin, $end, $attr) = $this->macros[$node->name];
		$node->isEmpty = !$end;

		if ($attr && $node->prefix === $node::PREFIX_NONE) {
			$node->isEmpty = TRUE;
			$this->compiler->setContext(Compiler::CONTEXT_DOUBLE_QUOTED_ATTR);
			$res = $this->compile($node, $attr);
			if ($res === FALSE) {
				return FALSE;
			} elseif (!$node->attrCode) {
				$node->attrCode = "<?php $res ?>";
			}
			$this->compiler->setContext(NULL);

		} elseif ($begin) {
			$res = $this->compile($node, $begin);
			if ($res === FALSE || ($node->isEmpty && $node->prefix)) {
				return FALSE;
			} elseif (!$node->openingCode) {
				$node->openingCode = "<?php $res ?>";
			}

		} elseif (!$end) {
			return FALSE;
		}
	}


	/**
	 * Node is closed.
	 * @return void
	 */
	public function nodeClosed(MacroNode $node)
	{
		if (isset($this->macros[$node->name][1])) {
			$res = $this->compile($node, $this->macros[$node->name][1]);
			if (!$node->closingCode) {
				$node->closingCode = "<?php $res ?>";
			}
		}
	}


	/**
	 * Generates code.
	 * @return string
	 */
	private function compile(MacroNode $node, $def)
	{
		$node->tokenizer->reset();
		$writer = PhpWriter::using($node, $this->compiler);
		if (is_string($def)) {
			return $writer->write($def);
		} else {
			return call_user_func($def, $node, $writer);
		}
	}


	/**
	 * @return Nopis\Lib\Latte\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

}
