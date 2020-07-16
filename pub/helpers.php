<?php

/*
|--------------------------------------------------------------------------
| 这里可以定义用户的自定义函数
|--------------------------------------------------------------------------
|
*/

include __DIR__ . '/defines.php';


/**
 * 检查邮箱是否合法.
 *
 * @param string $email
 * @return boolean
 */
function is_email($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
}

/**
 * 检查手机号是否合法.
 *
 * @param string $mobile
 * @return boolean
 */
function is_mobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^1[1|2|3|4|5|6|7|8|9]{1}\d{9}$#', $mobile) ? true : false;
}

/**
 * 检查身份证号码是否合法.
 *
 * @param string $nStr
 * @return boolean
 */
function is_ID_card($nStr)
{
    $vCity = array(
        '11','12','13','14','15','21','22',
        '23','31','32','33','34','35','36',
        '37','41','42','43','44','45','46',
        '50','51','52','53','54','61','62',
        '63','64','65','71','81','82','91'
    );

    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $nStr)) return false;

    if (!in_array(substr($nStr, 0, 2), $vCity)) return false;

    $nStr = preg_replace('/[xX]$/i', 'a', $nStr);
    $vLength = strlen($nStr);

    if ($vLength == 18) {
        $vBirthday = substr($nStr, 6, 4) . '-' . substr($nStr, 10, 2) . '-' . substr($nStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($nStr, 6, 2) . '-' . substr($nStr, 8, 2) . '-' . substr($nStr, 10, 2);
    }

    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday)
        return false;
    if ($vLength == 18) {
        $vSum = 0;

        for ($i = 17; $i >= 0; $i--) {
            $vSubStr = substr($nStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr, 11));
        }

        if ($vSum % 11 != 1)
            return false;
    }

    return true;
}

/**
 * 按时间随机生成一个序列号，长度为20，时间精确到十分之一微秒.
 *
 * @param string $prefix 英文字母前缀 默认 K
 * @return string
 */
function create_sn($prefix = 'X')
{
    $prefix = strtoupper($prefix);
    $prefix = ord($prefix) >= 65 && ord($prefix) <= 90 ? $prefix : 'X';

    $ycode = range(65, 90);

    $sn = $prefix
            . chr($ycode[intval(date('Y')) % count($ycode)])
            . strtoupper(dechex(date('m')))
            . date('d')
            . substr(time(), -5)
            . substr(microtime(), 2, 6)
            . sprintf('%04d', rand(0, 9999));

    return $sn;
}

/**
 * 创建uuid字符串
 *
 * @param string $phone
 * @return string 长度是43位的uuid字符串
 */
function create_uuid(string $phone)
{
    $id = $phone . ' '
            . substr(time(), 2, 4)
            . substr(microtime(), 2, 6)
            . sprintf('%02d', mt_rand(0, 99));

    return \Nopis\Lib\Security\Encryptor::encode($id);
}

/**
 * 计算两个经纬度点的距离，返回值的单位是米.
 *
 * @param float $src_longtitude
 * @param float $src_latitude
 * @param float $dest_longtitude
 * @param float $dest_latitude
 * @return int
 */
function calc_distance($src_longtitude, $src_latitude, $dest_longtitude, $dest_latitude)
{
    // 计算一经度和一纬度圈长
    $longitude_1_distance = 40075360 * sin(deg2rad(90 - $src_latitude)) / 360;
    $latitude_1_distance = 110946;

    $longitude_distance = abs($src_longtitude - $dest_longtitude) * $longitude_1_distance;
    $latitude_distance = abs($src_latitude - $dest_latitude) * $latitude_1_distance;

    return round(hypot($longitude_distance, $latitude_distance));
}


// ========================================================================//
// 自定义函数
// ========================================================================//

/**
 * 将标签textarea中的文本转换成数组，以行为单位.
 *
 * @param string $text
 * @return array
 */
function textarea2array($text)
{
    if (!is_string($text) || empty($text))
        return [];

    $text = str_replace("\r", '', $text);
    $arr = explode("\n", trim($text, "\n"));
    foreach ($arr as &$r)
        $r = trim(trim($r, "\n"));

    return $arr;
}
