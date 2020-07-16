<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\FileSystem;

class Image
{

    /**
     * make a text watermark
     *
     * @param string $srcImage
     * @param string $waterTxt
     * @param string $fontType
     * @param int $fontSize
     * @param string $txtColor
     * @param int $pos
     * @param int $offset
     * @return boolean
     * @throws \Exception
     */
    public static function watermarkTxt(string $srcImage, string $waterTxt, string $fontType,
                                        int $fontSize = 30, string $txtColor = '#eeeeee',
                                        int $pos = 2, int $offset = 0)
    {
        if (!is_file($srcImage) || empty($waterTxt)) {
            return false;
        }

        $red   = hexdec('0x' . $txtColor{1} . $txtColor{2});
        $green = hexdec('0x' . $txtColor{3} . $txtColor{4});
        $blue  = hexdec('0x' . $txtColor{5} . $txtColor{6});

        $box         = imagettfbbox($fontSize, 0, $fontType, $waterTxt);
        $waterWeight = max($box[2], $box[4]) - min($box[0], $box[6]);
        $waterHeight = max($box[1], $box[3]) - min($box[5], $box[7]);

        if (false === ($srcInfo = getimagesize($srcImage))) {
            throw new \InvalidArgumentException('The image ' . $srcImage . ' is not a valid image');
        }
        $srcWeight = $srcInfo[0];
        $srcHeight = $srcInfo[1];

        switch ($srcInfo[2]) {
            case 1:
                $dst = imagecreate($srcWeight, $srcHeight);
                $src = imagecreatefromgif($srcImage);
                break;

            case 2:
                $dst = imagecreatetruecolor($srcWeight, $srcHeight);
                $src = imagecreatefromjpeg($srcImage);
                break;

            case 3:
                $dst = imagecreatetruecolor($srcWeight, $srcHeight);
                $src = imagecreatefrompng($srcImage);
                break;

            default :
                throw new \InvalidArgumentException("Image\'s extension not Found");
        }

        switch ($pos) {
            case 1:
                $x = $offset;
                $y = $srcHeight - $offset;
                break;

            case 2:
                $x = $srcWeight - ($waterWeight + $offset);
                $y = $srcHeight - $offset;
                break;

            case 3:
                $x = $srcWeight - ($waterWeight + $offset);
                $y = $waterHeight + $offset;
                break;

            case 4:
                $x = $offset;
                $y = $waterHeight + $offset;
                break;

            case 5:
                $x = ($srcWeight - $waterWeight) / 2;
                $y = ($srcHeight - $waterHeight) / 2;
                break;

            default :
                $x = $srcWeight - ($waterWeight + $offset);
                $y = $srcHeight - $offset;
        }

        if (!imagecopy($dst, $src, 0, 0, 0, 0, $srcWeight, $srcHeight)) {
            imagedestroy($dst);
            imagedestroy($src);
            return false;
        }

        $color = imagecolorallocate($dst, $red, $green, $blue);
        imagettftext($dst, $fontSize, 0, $x, $y, $color, $fontType, $waterTxt);

        switch ($srcInfo[2]) {
            case 1:
                if (!imagegif($dst, $srcImage)) {
                    return false;
                }
                break;

            case 2:
                if (!imagejpeg($dst, $srcImage)) {
                    return false;
                }
                break;

            case 3:
                if (!imagepng($dst, $srcImage)) {
                    return false;
                }
                break;
        }
        imagedestroy($dst);
        imagedestroy($src);

        return true;
    }

    /**
     * make a image watermark
     *
     * @param string $srcImage
     * @param string $waterImage
     * @param int    $pos
     * @param int    $offset
     * @return boolean
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public static function watermarkWithImg(string $srcImage, string $waterImage, int $pos = 2, int $offset = 0)
    {
        if (!is_file($srcImage) || !is_file($waterImage)) {
            return false;
        }

        $pos    = (int) $pos;
        $offset = (int) $offset;

        if (false === ($srcInfo = getimagesize($srcImage))) {
            throw new \InvalidArgumentException('The image ' . $srcImage . ' is not a valid image');
        }
        $srcWeight = $srcInfo[0];
        $srcHeight = $srcInfo[1];

        if (false === ($waterInfo = getimagesize($waterImage))) {
            throw new \InvalidArgumentException('The image ' . $waterImage . ' is not a valid image');
        }
        $waterWeight = $waterInfo[0];
        $waterHeight = $waterInfo[1];

        switch ($srcInfo[2]) {
            case 1:
                $src = imagecreatefromgif($srcImage);
                $dst = imagecreate($srcWeight, $srcHeight);
                break;
            case 2:
                $src = imagecreatefromjpeg($srcImage);
                $dst = imagecreatetruecolor($srcWeight, $srcHeight);
                break;
            case 3:
                $src = imagecreatefrompng($srcImage);
                $dst = imagecreatetruecolor($srcWeight, $srcHeight);
                break;
            default :
                throw new \InvalidArgumentException("Image\'s extension not Found");
        }

        switch ($waterInfo[2]) {
            case 1:
                $waterImage = imagecreatefromgif($waterImage);
                break;
            case 2:
                $waterImage = imagecreatefromjpeg($waterImage);
                break;
            case 3:
                $waterImage = imagecreatefrompng($waterImage);
                break;
            default :
                throw new \InvalidArgumentException("Image\'s extension not Found");
        }

        switch ($pos) {
            case 1:
                $x = $offset;
                $y = $srcHeight - ($waterHeight + $offset);
                break;
            case 2:
                $x = $srcWeight - ($waterWeight + $offset);
                $y = $srcHeight - ($waterHeight + $offset);
                break;
            case 3:
                $x = $srcWeight - ($waterWeight + $offset);
                $y = $offset;
                break;
            case 4:
                $x = $offset;
                $y = $offset;
                break;
            case 5:
                $x = ($srcWeight - $waterWeight) / 2;
                $y = ($srcHeight - $waterHeight) / 2;
                break;
            default :
                $x = $srcWeight - ($waterWeight + $offset);
                $y = $srcHeight - ($waterHeight + $offset);
                break;
        }

        if (!imagecopy($dst, $src, 0, 0, 0, 0, $srcWeight, $srcHeight)) {
            imagedestroy($dst);
            imagedestroy($src);
            imagedestroy($waterImage);
            return false;
        }

        if (!imagecopy($dst, $waterImage, $x, $y, 0, 0, $waterWeight, $waterHeight)) {
            imagedestroy($dst);
            imagedestroy($src);
            imagedestroy($waterImage);
            return false;
        }

        switch ($srcInfo[2]) {
            case 1:
                if (!imagegif($dst, $srcImage)) {
                    return false;
                }
                break;

            case 2:
                if (!imagejpeg($dst, $srcImage)) {
                    return false;
                }
                break;

            case 3:
                if (!imagepng($dst, $srcImage)) {
                    return false;
                }
                break;

            default :
                throw new \InvalidArgumentException("Image\'s extension not Found");
        }
        imagedestroy($dst);
        imagedestroy($src);
        imagedestroy($waterImage);

        return true;
    }

    /**
     * shrink image
     *
     * $reserve decide whether to back up the source image
     *
     * @param string $srcImage
     * @param int    $width
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public static function shrink(string $srcImage, int $width)
    {
        if ($width <= 0) {
            throw new \InvalidArgumentException('width must be give');
        }

        if (false === ($info = getimagesize($srcImage))) {
            throw new \InvalidArgumentException('the origin image not exists');
        }

        if ($info[0] < $width) {
            return true;
        }

        $height = ceil(($info[1] * $width) / $info[0]);

        switch ($info[2]) {
            case 1:
                $dst = imagecreate($width, $height);
                $src = imagecreatefromgif($srcImage);
                if (!imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $info[0], $info[1])) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                imagegif($dst, $srcImage);
                imagedestroy($dst);
                imagedestroy($src);
                break;

            case 2:
                $dst = imagecreatetruecolor($width, $height);
                $src = imagecreatefromjpeg($srcImage);
                if (!imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $info[0], $info[1])) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                imagejpeg($dst, $srcImage);
                imagedestroy($dst);
                imagedestroy($src);
                break;

            case 3:
                $dst = imagecreatetruecolor($width, $height);
                $src = imagecreatefrompng($srcImage);
                if (!imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $info[0], $info[1])) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                imagepng($dst, $srcImage);
                imagedestroy($dst);
                imagedestroy($src);
                break;

            default :
                throw new \InvalidArgumentException("Image\'s extension not Found");
        }

        return true;
    }

    /**
     * clip image
     *
     * @param string  $srcImage
     * @param int     $x       x coordinate
     * @param int     $y       y coordinate
     * @param int     $w       clip weight
     * @param int     $h       clip height
     * @return bool
     */
    public static function clip(string $srcImage, int $x, int $y, int $w, int $h)
    {
        if (!is_file($srcImage)) {
            return false;
        }

        if (false === ($srcInfo = getimagesize($srcImage))) {
            throw new \InvalidArgumentException('The image ' . $srcImage . ' is not a valid image');
        }
        if ($x >= $srcInfo[0] || $y >= $srcInfo[1]) {
            throw new \Exception('the coordinate overflow');
        }
        $x = ($x < 0) ? 0 : $x;
        $y = ($y < 0) ? 0 : $y;
        $w = (($x + $w) > $srcInfo[0]) ? ($srcInfo[0] - $x) : $w;
        $h = (($y + $h) > $srcInfo[1]) ? ($srcInfo[1] - $y) : $h;

        switch ($srcInfo[2]) {
            case 1:
                $dst = imagecreate($w, $h);
                $src = imagecreatefromgif($srcImage);
                if (!imagecopy($dst, $src, 0, 0, $x, $y, $w, $h)) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                if (!imagegif($dst, $srcImage)) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                break;

            case 2:
                $dst = imagecreatetruecolor($w, $h);
                $src = imagecreatefromjpeg($srcImage);
                if (!imagecopy($dst, $src, 0, 0, $x, $y, $w, $h)) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                if (!imagejpeg($dst, $srcImage)) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                break;

            case 3:
                $dst = imagecreatetruecolor($w, $h);
                $src = imagecreatefrompng($srcImage);
                if (!imagecopy($dst, $src, 0, 0, $x, $y, $w, $h)) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                if (!imagepng($dst, $srcImage)) {
                    imagedestroy($dst);
                    imagedestroy($src);
                    return false;
                }
                break;

            default :
                throw new \InvalidArgumentException("Image\'s extension not Found");
        }
        imagedestroy($dst);
        imagedestroy($src);

        return true;
    }

    /**
     * create image from file or URL
     *
     * @param string $srcImage
     * @param string $dir
     * return bool
     */
    public static function create(string $srcImage, string $dir)
    {
        if (strpos($srcImage, 'http://') !== 0 && !is_file($srcImage)) {
            return false;
        }

        if (false === ($srcInfo = getimagesize($srcImage))) {
            throw new \InvalidArgumentException('The image ' . $srcImage . ' is not a valid image');
        }

        // 根据给定的目录路径，生成一个唯一的带路径的目标图片名称
        $splfile = new \SplFileInfo($srcImage);
        if (!is_dir($dir) && !Dir::create($dir)) {
            throw new \Exception("directory '$dir' does't exist");
        }
        $dir         = trim($dir, '/');
        $imgExt      = $splfile->getExtension();
        $imgBaseName = $splfile->getBasename('.' . $imgExt);
        $rands       = '0123456789ABCDEFGHIJ';
        $dstImage    = $dir . '/' . $splfile->getFilename();
        while (file_exists($dstImage)) {
            $imgBaseName = $imgBaseName . '_';
            for ($i = 0; $i < 4; $i++) {
                $imgBaseName .= $rands[mt_rand(0, 19)];
            }
            $dstImage = $dir . '/' . $imgBaseName . '.' . $imgExt;
        }

        switch ($srcInfo[2]) {
            case 1:
                $src = imagecreatefromgif($srcImage);
                if (!imagegif($src, $dstImage)) {
                    imagedestroy($src);
                    return false;
                }
                break;
            case 2:
                $src = imagecreatefromjpeg($srcImage);
                if (!imagejpeg($src, $dstImage)) {
                    imagedestroy($src);
                    return false;
                }
                break;
            case 3:
                $src = imagecreatefrompng($srcImage);
                if (!imagepng($src, $dstImage)) {
                    imagedestroy($src);
                    return false;
                }
                break;
            default :
                throw new \InvalidArgumentException("Image\'s extension not Found");
        }
        imagedestroy($src);

        return $dstImage;
    }
}
