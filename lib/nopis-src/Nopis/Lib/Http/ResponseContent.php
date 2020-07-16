<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Http;

class ResponseContent
{
    /**
     * @var array
     */
    protected $content = [];

    /**
     * Constructor.
     *
     * @param type $content
     */
    public function __construct($content)
    {
        $this->setContent($content);
    }

    /**
     * Set content
     *
     * @param mixed $content
     * @throws \Exception
     */
    public function setContent($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
            throw new \Exception(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($content)));
        }

        $this->content[] = (string) $content;
    }

    /**
     * Get contents
     *
     * @return array
     */
    public function getContents()
    {
        return $this->content;
    }

    public function __toString()
    {
        return implode('', $this->content);
    }
}