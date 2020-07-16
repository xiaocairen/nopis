<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Utils;

use Nopis\Lib\Utils\Scws\Pscws4;

/**
 * @author wangbin
 */
class WordSegment
{
    /**
     * @var \Nopis\Lib\Utils\Scws\Pscws4
     */
    private $scws;

    /**
     * @var string
     */
    private $text = null;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->scws = new Pscws4('utf8');
        $this->scws->set_dict(__DIR__ . '/Scws/etc/dict.utf8.xdb');
        $this->scws->set_rule(__DIR__ . '/Scws/etc/rules.utf8.ini');
    }

    /**
     * 设置字符集(ztab)
     *
     * @param string $charset
     */
    public function set_charset(string $charset = 'utf8')
    {
        $this->scws->set_charset($charset);
    }

    /**
     * 设置是否显示分词调试信息
     *
     * @param bool $yes
     */
    public function set_debug(bool $yes)
    {
        $this->scws->set_debug($yes);
    }

    /**
     * 设置是否自动将散字二元化
     *
     * @param bool $yes
     */
    public function set_duality(bool $yes)
    {
        $this->scws->set_duality($yes);
    }

    /**
     * 设置忽略符号与无用字符
     *
     * @param bool $yes
     */
    public function set_ignore(bool $yes)
    {
        $this->scws->set_ignore($yes);
    }

    /**
     * 设置复合分词等级 ($level = 0,15)
     *
     * @param int $level
     */
    public function set_multi(int $level)
    {
        $this->scws->set_multi($level);
    }

    /**
     * 设置要分词的内容
     *
     * @param string $text
     */
    public function sendText($text)
    {
        $text = trim((string) $text);
        if (empty($text))
            return;

        $this->text = $text;
    }

    /**
     * 以数组形式返回分割后的所有单词
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getSimpleResult()
    {
        if (null === $this->text) {
            throw new \InvalidArgumentException(__CLASS__ . '\'s property $text is empty');
        }

        $this->scws->send_text($this->text);
        $ret = [];
        while (false !== ($row = $this->scws->get_result())) {
           foreach ($row as $r) {
               $r['word'] = trim($r['word']);
               !empty($r['word']) && $ret[] = $r['word'];
           }
        }
        $this->scws->close();

        return $ret;
    }
}
