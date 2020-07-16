<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 */

namespace Nopis\Lib\Latte;


/**
 * Macro element node.
 *
 * @author     David Grudl
 */
class MacroNode extends LatteObject
{
	const PREFIX_INNER = 'inner',
		PREFIX_TAG = 'tag',
		PREFIX_NONE = 'none';

	/** @var IMacro */
	public $macro;

	/** @var string */
	public $name;

	/** @var bool */
	public $isEmpty = FALSE;

	/** @var string  raw arguments */
	public $args;

	/** @var string  raw modifier */
	public $modifiers;

	/** @var bool */
	public $closing = FALSE;

	/** @var bool  has output? */
	public $replaced;

	/** @var MacroTokens */
	public $tokenizer;

	/** @var MacroNode */
	public $parentNode;

	/** @var string */
	public $openingCode;

	/** @var string */
	public $closingCode;

	/** @var string */
	public $attrCode;

	/** @var string */
	public $content;

	/** @var \stdClass  user data */
	public $data;

	/** @var HtmlNode  closest HTML node */
	public $htmlNode;

	/** @var string  indicates n:attribute macro and type of prefix (PREFIX_INNER, PREFIX_TAG, PREFIX_NONE) */
	public $prefix;

	public $saved;

    /** @var array */
    private $userFuncs = [];


    public function __construct
    (
        IMacro $macro,
        $name,
        $args = NULL,
        $modifiers = NULL,
        self $parentNode = NULL,
        HtmlNode $htmlNode = NULL,
        $prefix = NULL,
        array $userFuncs = []
    )
	{
		$this->macro = $macro;
		$this->name = (string) $name;
		$this->modifiers = (string) $modifiers;
		$this->parentNode = $parentNode;
		$this->htmlNode = $htmlNode;
		$this->prefix = $prefix;
		$this->data = new \stdClass;
        $this->userFuncs = $userFuncs;
		$this->setArgs($args);
	}


	public function setArgs($args)
	{
		$this->args = (string) $args;
		$this->tokenizer = new MacroTokens($this->args);
        $this->parseUserFuncs($this->tokenizer);
	}

    /**
     * 将模版中用户自定义函数标签替换为已注册到模版引擎中的相应标签的对象调用
     *
     * @param \Nopis\Lib\Latte\MacroTokens $tokenizer
     * @throws \Exception
     *
     * @time 2015-08-07
     */
    private function parseUserFuncs(MacroTokens $tokenizer)
    {
        if (!isset($tokenizer->tokens[0]) || !isset($tokenizer->tokens[0][0]))
            return;

        $fname = $tokenizer->tokens[0][0];
        if ('$' !== $fname[0] && isset($this->userFuncs[$fname])) {
            $funcs = $this->userFuncs[$fname];
            $varname = array_shift($funcs);
            if (!is_callable($funcs)) {
                throw new \Exception(
                    sprint_f('The user defined function "%s::%s" is not callable in template', $funcs[0], $funcs[1])
                );
            }

            $tokenizer->tokens[0][0] = '$' . $varname . '->' . $funcs[1];
        }
    }

}
