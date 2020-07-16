<?php

/**
 *
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @license GPLv3
 * @link    http://code.google.com/p/cool-php-captcha
 * @package captcha
 * @version 0.3
 *
 */
/*

session_start();

$captcha = new SimpleCaptcha();
$captcha->setCaptchaTxt($captchaTxt);
$captcha->setCaptchaSize(120, 30);
$captcha->CreateImage();

 *  */

namespace Nopis\Lib\Captcha;

/**
 * SimpleCaptcha class
 *
 */
class SimpleCaptcha
{

    /** the text be show in captcha */
    protected $captchaTxt;

    /** Width of the image */
    protected $width = 90;

    /** Height of the image */
    protected $height = 28;

    /**
     * Path for resource files (fonts, words, etc.)
     *
     * "resources" by default. For security reasons, is better move this
     * directory to another location outise the web server
     *
     */
    protected $resourcesPath = 'resources';

    /** Min word length (for non-dictionary random text generation) */
    public $minWordLength = 4;

    /**
     * Max word length (for non-dictionary random text generation)
     *
     * Used for dictionary words indicating the word-length
     * for font-size modification purposes
     */
    public $maxWordLength = 5;

    /** Background color in RGB-array */
    protected $backgroundColor = array(255, 255, 255);

    /** Foreground colors in RGB-array */
    protected $colors = array(
        array(27, 78, 181), // blue
        array(22, 163, 35), // green
        array(214, 36, 7), // red
    );

    /** Shadow color in RGB-array or null */
    protected $shadowColor = null; //array(0, 0, 0);

    /** Horizontal line through the text */
    public $lineWidth = 0;

    /**
     * Font configuration
     *
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */
    protected $fonts = array(
        'Jura'     => array('spacing' => -1,   'minSize' => 15, 'maxSize' => 16, 'font' => 'Jura.ttf'),
        'Times'    => array('spacing' => -1,   'minSize' => 15, 'maxSize' => 16, 'font' => 'TimesNewRomanBold.ttf'),
        'VeraSans' => array('spacing' => -1,   'minSize' => 15, 'maxSize' => 16, 'font' => 'VeraSansBold.ttf'),
    );

    /** Wave configuracion in X and Y axes */
    protected $Yperiod = 8;
    protected $Yamplitude = 10;
    protected $Xperiod = 8;
    protected $Xamplitude = 4;

    /** letter rotation clockwise */
    protected $maxRotation = 4;

    /**
     * Internal image size factor (for better image quality)
     * 1: low, 2: medium, 3: high
     */
    protected $scale = 3;

    /**
     * Blur effect for better image quality (but slower image processing).
     * Better image results with scale=3
     */
    public $blur = false;

    /** Debug? */
    public $debug = false;

    /** Image format: jpeg or png */
    protected $imageFormat = 'jpeg';

    /** GD image */
    protected $im;

    /**
     * output image
     */
    public function CreateImage()
    {
        $ini = microtime(true);

        /** Initialization */
        $this->ImageAllocate();

        /** Text insertion */
        $text = $this->GetCaptchaTxt();
        $fontcfg = $this->fonts[array_rand($this->fonts)];
        $this->WriteText($text, $fontcfg);

        /** Transformations */
        if (!empty($this->lineWidth)) {
            $this->WriteLine();
        }
        $this->WaveImage();
        if ($this->blur && function_exists('imagefilter')) {
            imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $this->ReduceImage();


        if ($this->debug) {
            imagestring($this->im, 1, 1, $this->height - 8, "$text {$fontcfg['font']} " . round((microtime(true) - $ini) * 1000) . "ms", $this->GdFgColor
            );
        }


        /** Output */
        $this->WriteImage();
        $this->Cleanup();
    }

    /**
     *
     * @param string $captchaTxt
     */
    public function setCaptchaTxt($captchaTxt)
    {
        $captchaTxt = trim($captchaTxt);
        $captchaTxt && $this->captchaTxt = $captchaTxt;
    }

    /**
     *
     * @param int $width
     * @param int $height
     */
    public function setCaptchaSize($width = 0, $height = 0)
    {
        $width = (int) $width > 0 ? (int) $width : 0;
        $height = (int) $height > 0 ? (int) $height : 0;

        $width && $this->width = $width;
        $height && $this->height = $height;
    }

    /**
     * Creates the image resources
     */
    protected function ImageAllocate()
    {
        // Cleanup
        if (!empty($this->im)) {
            imagedestroy($this->im);
        }

        $this->im = imagecreatetruecolor($this->width * $this->scale, $this->height * $this->scale);

        // Background color
        $this->GdBgColor = imagecolorallocate($this->im, $this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]
        );
        imagefilledrectangle($this->im, 0, 0, $this->width * $this->scale, $this->height * $this->scale, $this->GdBgColor);

        // Foreground color
        $color = $this->colors[mt_rand(0, sizeof($this->colors) - 1)];
        $this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

        // Shadow color
        if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
            $this->GdShadowColor = imagecolorallocate($this->im, $this->shadowColor[0], $this->shadowColor[1], $this->shadowColor[2]
            );
        }
    }

    /**
     * Text generation
     *
     * @return string Text
     */
    protected function GetCaptchaTxt()
    {
        return $this->captchaTxt ?: $this->GetRandomCaptchaTxt();
    }

    /**
     * Random text generation
     *
     * @return string Text
     */
    protected function GetRandomCaptchaTxt($length = null)
    {
        if (empty($length)) {
            $length = rand($this->minWordLength, $this->maxWordLength);
        }

        $string = '23456789abcdefghjkmnpqrstuvwxyz';
        $len    = strlen($string);

        $text   = "";
        for ($i = 0; $i < $length; $i++) {
            $text .= $string[mt_rand(0, $len - 1)];
        }
        return $text;
    }

    /**
     * Horizontal line insertion
     */
    protected function WriteLine()
    {

        $x1 = $this->width * $this->scale * .15;
        $x2 = $this->textFinalX;
        $y1 = rand($this->height * $this->scale * .40, $this->height * $this->scale * .65);
        $y2 = rand($this->height * $this->scale * .40, $this->height * $this->scale * .65);
        $width = $this->lineWidth / 2 * $this->scale;

        for ($i = $width * -1; $i <= $width; $i++) {
            imageline($this->im, $x1, $y1 + $i, $x2, $y2 + $i, $this->GdFgColor);
        }
    }

    /**
     * Text insertion
     */
    protected function WriteText($text, $fontcfg = array())
    {
        // Select the font configuration
        empty($fontcfg) && $fontcfg = $this->fonts[array_rand($this->fonts)];

        // Full path of font file
        $fontfile = __DIR__ . '/' . $this->resourcesPath . '/fonts/' . $fontcfg['font'];


        /** Increase font-size for shortest words: 9% for each glyp missing */
        $lettersMissing = $this->maxWordLength - strlen($text);
        $fontSizefactor = 1 + ($lettersMissing * 0.09);

        // Text generation (char by char)
        $x = 18 * $this->scale;
        $y = round(($this->height * 29 / 40) * $this->scale);
        $length = strlen($text);
        for ($i = 0; $i < $length; $i++) {
            $degree = rand($this->maxRotation * -1, $this->maxRotation);
            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize']) * $this->scale * $fontSizefactor;
            $letter = substr($text, $i, 1);

            if ($this->shadowColor) {
                $coords = imagettftext($this->im, $fontsize, $degree, $x + $this->scale, $y + $this->scale, $this->GdShadowColor, $fontfile, $letter);
            }
            $coords = imagettftext($this->im, $fontsize, $degree, $x, $y, $this->GdFgColor, $fontfile, $letter);
            $x += ($coords[2] - $x) + ($fontcfg['spacing'] * $this->scale);
        }

        $this->textFinalX = $x;
    }

    /**
     * Wave filter
     */
    protected function WaveImage()
    {
        // X-axis wave generation
        $xp = $this->scale * $this->Xperiod * rand(1, 3);
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->width * $this->scale); $i++) {
            imagecopy($this->im, $this->im, $i - 1, sin($k + $i / $xp) * ($this->scale * $this->Xamplitude), $i, 0, 1, $this->height * $this->scale);
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        $yp = $this->scale * $this->Yperiod * rand(1, 2);
        for ($i = 0; $i < ($this->height * $this->scale); $i++) {
            imagecopy($this->im, $this->im, sin($k + $i / $yp) * ($this->scale * $this->Yamplitude), $i - 1, 0, $i, $this->width * $this->scale, 1);
        }
    }

    /**
     * Reduce the image to the final size
     */
    protected function ReduceImage()
    {
        $imResampled = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($imResampled, $this->im, 0, 0, 0, 0, $this->width, $this->height, $this->width * $this->scale, $this->height * $this->scale
        );
        imagedestroy($this->im);
        $this->im = $imResampled;
    }

    /**
     * File generation
     */
    protected function WriteImage()
    {
        if ($this->imageFormat == 'png' && function_exists('imagepng')) {
            header("Content-type: image/png");
            imagepng($this->im);
        } else {
            header("Content-type: image/jpeg");
            imagejpeg($this->im, null, 80);
        }
    }

    /**
     * Cleanup
     */
    protected function Cleanup()
    {
        imagedestroy($this->im);
        die;
    }

}
