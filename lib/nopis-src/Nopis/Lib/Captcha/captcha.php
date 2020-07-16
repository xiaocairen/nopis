<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nopis\Lib\Captcha;

use Nopis\Lib\Http\RequestInterface;
use Nopis\Lib\Http\Session\Session;

/**
 * Description of captcha
 *
 * @author Wangbin
 */
class captcha
{

    /**
     * @var \Nopis\Lib\Http\RequestInterface
     */
    protected $request = null;

    protected $width = 90;

    protected $height = 28;

    /**
     * Constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
        if (!$this->request->hasSession()) {
            $this->request->setSession(new Session());
        }
    }

    /**
     * Set captcha size
     *
     * @param int $width
     * @param int $height
     */
    public function setSize($width = 90, $height = 28)
    {
        $width = max(50, intval($width));
        $height = max(10, intval($height));
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * 生成并显示验证码
     *
     * @param string $captchaName
     * @throws GenerateException
     */
    public function showCaptcha($captchaName)
    {
        $captchaName = trim((string) $captchaName);
        if(!$captchaName) {
            throw new \Exception('The captcha name cannot be empty');
        }

        $captchaTxt = strtolower($this->generateCaptchaTxt(4));
        // 把 $keyStr 保存到 SESSION 中
        $this->request->getSession()->set($captchaName, $captchaTxt);

        // 生成验证码图片
        $captcha = new SimpleCaptcha();
        $captcha->setCaptchaTxt($captchaTxt);
        $captcha->setCaptchaSize($this->width, $this->height);
        $captcha->CreateImage();
    }

    /**
     * 校验用户传回的验证码值与 SESSION 中保存的验证码值是否一样
     *
     * @param string $captchaName
     * @param string $captchaValue
     * @return boolean
     */
    public function checkCaptcha($captchaName, $captchaValue)
    {
        $captchaName = trim((string) $captchaName);
        $captchaValue = trim((string) $captchaValue);
        if(!$captchaName) {
            throw new \Exception('The captcha name cannot be empty');
        }
        if (!$captchaValue) {
            throw new \Exception('The captcha value cannot be empty');
        }

        $sessValue = $this->request->getSession()->get($captchaName, null);

        return $captchaValue === $sessValue;
    }

    /**
     * 返回一个 $pw_length 长度的包含数字字母的字符串
     *
     * @param int $len
     * @return string
     */
    private function generateCaptchaTxt($len)
    {
        $minAscii = 50;
        $maxAscii = 90;
        $noUse = [58, 59, 60, 61, 62, 63, 64, 73, 76, 79];
        $str = '';
        for ($i = 0; $i < $len;) {
            $randAscii = mt_rand($minAscii, $maxAscii);
            if (!in_array($randAscii, $noUse)) {
                $str .= chr($randAscii);
                $i++;
            }
        }
        return strtolower($str);
    }

}
