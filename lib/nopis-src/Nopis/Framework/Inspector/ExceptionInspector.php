<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Framework\Inspector;

use Exception;
use Nopis\Lib\Config\ConfiguratorInterface;
use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Http\Response;

/**
 * Description of ExceptionInspector
 *
 * @author wb
 */
class ExceptionInspector implements ExceptionInspectorInterface
{
    /**
     * @param \Exception $e
     * @param \Nopis\Lib\Http\RequestInterface $request
     * @param \Nopis\Lib\Config\ConfiguratorInterface $configurator
     *
     * @return \Nopis\Lib\Http\Response
     */
    public function handleException(Exception $e, RequestInterface $request, ConfiguratorInterface $configurator)
    {
        $exception_class = get_class($e);
        $exception_msg = $e->getMessage();
        $exception_handler = $configurator->getConfig('framework.global_exception_handler');
        if ($exception_handler && is_subclass_of($exception_handler, 'Nopis\Framework\Exception\GlobalHandler')) {
            $response = (new $exception_handler($e, $request, $configurator))->handleException();
            if ($response instanceof \Nopis\Lib\Http\ResponseInterface) {
                return $response;
            } else if (is_string($response) && strlen($response) > 0) {
                $exception_msg = $response;
            }
        }

        $accept = $request->getHeader('Accept');
        $response = new Response();
        if (false !== (stripos($accept, 'application/json'))) {
            $errorMsg = IS_DEBUG ? sprintf(
                '%s: Exception "%s" occured in %s on line %d', $exception_class, $exception_msg, str_replace($configurator->getRootDir(), '', $e->getFile()), $e->getLine()
            ) : $exception_msg;

            $response->getHeaders()->setContentType('application/json');
            $response->setContent(json_encode([
                'success'    => false,
                'error_code' => $e->getCode(),
                'error_msg'  => $errorMsg,
            ], JSON_UNESCAPED_UNICODE));
        } else {
            $response->setContent(IS_DEBUG ? $this->getExceptionMessage($e, $configurator, $exception_class, $exception_msg) : $exception_msg);
        }
        return $response;
    }

    private function getExceptionMessage(Exception $e, ConfiguratorInterface $configurator, $exception_class, $exception_msg)
    {
        $traces = $e->getTrace();
        $traces[0]['file'] = str_replace($configurator->getRootDir(), '', $e->getFile());
        $traces[0]['line'] = $e->getLine();

        $main_arg = '';
        foreach ($traces[0]['args'] as $arg) {
            if (is_array($arg))
                $main_arg .= 'Array,';
            elseif (is_object($arg))
                $main_arg .= get_class($arg) . ',';
            elseif (is_resource($arg))
                $main_arg .= '#resource,';
            elseif (is_null($arg))
                $main_arg .= 'NULL,';
            elseif (is_bool($arg))
                $main_arg .= $arg ? 'TRUE,' : 'FALSE,';
            elseif (is_string($arg) && empty ($arg))
                $main_arg .= '"",';
            else
                $main_arg .= ((string) $arg) . ',';
        }

        $main_str = $traces[0]['file']
                . ' (' . $traces[0]['line'] . ')'
                . ' [' . (isset($traces[0]['class']) ? $traces[0]['class'] : '')
                . (isset($traces[0]['type']) ? $traces[0]['type'] : '') . $traces[0]['function']
                . ' (' . substr($main_arg, 0, -1) . ')] throw by ' . $exception_class
                . ' :: <font color="#ff0000">' . $exception_msg . '</font>';
        $error_str = <<<EOF
<html>
<head>
<title>Debug Error</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
<style type="text/css">
body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
#container { width: 100%; }
#message   { width: 1024px; color: black; }
p{margin:0}
.red  {color: red;}
a:link     { font: 9pt/11pt verdana, arial, sans-serif; color: red; }
a:visited  { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
h1 { color: #F00; font: 18pt "Verdana"; margin-bottom: 0.5em;}
.bg1{ background-color: #FFFFCC;}
.bg2{ background-color: #EEEEEE;}
.table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
.info {
    background: none repeat scroll 0 0 #F3F3F3;
    color: #000000;
    font-size: 11pt;
    line-height: 160%;
    margin-bottom: 1em;
    padding: 1em;
}
table{margin-top:1em}
.cirbox{
    position: relative;
    border: 1px solid #ccc;
}
i.lt, i.rt, i.lb, i.rb{
    height:5px;
    width:5px;
    position: absolute;
}
i.lt{ background-position: left top; left: -1px; top:-1px;}
i.rt{ background-position: right top; right: -1px; top:-1px;}
i.lb{ background-position: left bottom; left:-1px; bottom: -1px;}
i.rb{ background-position: right bottom; right: -1px; bottom: -1px;}
</style>
</head>
<body>
<div id="container">
<h1>PHP Debugger Error</h1>

<div class="info cirbox">
    <p><strong>{$main_str}</strong></p>
    <table cellpadding="5" cellspacing="1" width="100%" class="table">
        <tr class="bg2">
            <td>No.</td>
            <td>File</td>
            <td>Line</td>
            <td>Code</td>
        </tr>
EOF;
        $trace_str = '<tr class="bg1">'
                    . '<td>%d</td>'
                    . '<td>%s</td>'
                    . '<td>%d</td>'
                    . '<td>%s%s%s(%s)</td>'
                    . '</tr>';
        foreach ($traces as $k => $r) {
            $args = '';
            foreach ($r['args'] as $arg) {
                if (is_array($arg))
                    $args .= 'Array,';
                elseif (is_object($arg))
                    $args .= get_class($arg) . ',';
                elseif (is_resource($arg))
                    $args .= '#resource,';
                elseif (is_null($arg))
                    $args .= 'NULL,';
                elseif (is_bool($arg))
                    $args .= $arg ? 'TRUE,' : 'FALSE,';
                elseif (is_string($arg) && empty ($arg))
                    $args .= '"",';
                else
                    $args .= ((string) $arg) . ',';
            }
            $error_str .= sprintf($trace_str,
                    $k,
                    isset($r['file']) ? $r['file'] : '',
                    isset($r['line']) ? $r['line'] : '',
                    isset($r['class']) ? $r['class'] : '',
                    isset($r['type']) ? $r['type'] : '',
                    $r['function'],
                    substr($args, 0, -1));
        }
        $error_str .= <<<EOF
    </table>
    <i class="lt"></i>
    <i class="rt"></i>
    <i class="lb"></i>
    <i class="rb"></i>
</div>
</div>
</body>
</html>
EOF;

        return $error_str;
    }
}
