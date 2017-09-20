<?php
/**
 * 常用辅助函数
 * @author Evenboy
 */

/**
 * 二维数组根据指定键值排序
 * @param    $ary
 * @param    $key
 * @param    $sort_type  (asc or desc)
 * @param    $ary_type   (obj or ary)
 * @return   array
 */
function array_sort($ary, $key, $sort_type = 'asc', $ary_type = 'obj')
{
    $keys_ary = $new_ary = array();
    foreach ($ary as $row) {
        $keys_ary[] = ($ary_type == 'obj' ? $row->$key : $row[$key]);
    }
    if ($sort_type == 'asc') {
        asort($keys_ary);
    } else {
        arsort($keys_ary);
    }
    reset($keys_ary);
    foreach ($keys_ary as $k => $v) {
        $new_ary[] = $ary[$k];
    }
    return $new_ary;
}

/**
 * 获取两个坐标之间的距离
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @param int $len_type 距离单位(1=m or 2=km)
 * @param int $decimal 保留几位小数
 * @return float (m or km)
 */
function distance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
{
    $earth_radius = 6371; // 地球半径，平均半径为6371km
    $radLat1      = $lat1 * PI() / 180.0;
    $radLat2      = $lat2 * PI() / 180.0;
    $a            = $radLat1 - $radLat2;
    $b            = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
    $s            = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s            = $s * $earth_radius;
    $s            = round($s * 1000);
    if ($len_type > 1) {
        $s = $s / 1000;
    }
    return round($s, $decimal);
}

// 修补长度
function fix_length($string, $len)
{
    $len_str = strlen($string);
    if ($len_str >= $len) {
        return substr($string, 0, $len);
    }
    for ($i = $len_str; $i < $len; $i++) {
        $string .= pack("C", 0);
    }
    return $string;
}

/**
 * 截取中文字符不乱码 需要开启substr扩展
 * @param $str 需要截取的中文字符
 * @param int $start 开始位置 默认为0
 * @param int $length 结束位置 没有被设置则 截取到最后
 * @param string $charset 字符集 默认utf-8
 * @return string
 */
function msubstr($str, $start = 0, $length = false, $charset = "utf-8")
{
    if (!$length) {
        $length = mb_strlen($str, $charset);
    }
    if (mb_strlen($str, $charset) >= $length) {
        if (function_exists("mb_substr")) {
            return mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            return iconv_substr($str, $start, $length, $charset);
        }
        $re['utf-8']  = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
        $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
        $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
        $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
        return $slice;
    } else {
        return $str;
    }
}

/**
 * @param1 string $path 上传的路径 必须要的参数
 * @param2 array|string $file 文件资源
 * @param3 int $max 如许上传的文件最大限度 单位 m
 * @param4 string $fileName  文件名
 * @param5 array $type      可以上传的类型
 * @return bool|string      失败或者成功的路径
 */
function fileUpload($path, $max = 2, $file = '', $fileName = '', $type = [])
{
    if (!is_array($file)) {
        $file = array_keys($_FILES);
        $file = $_FILES[$file[0]];
    }
    $maxlength = $max * 1048576;
    if ($maxlength < $file['size']) {
        return '上传的附件太大了';
    }
    //文件名
    $name = $file['name'];
    //得到文件类型，并且都转化成小写
    $typeName = strtolower(substr($name, strrpos($name, '.') + 1));
    //定义被允许文件后缀名
    if (!$type) {
        $type = ['jpg', 'jpeg', 'png', 'gif'];
    }
    if (!in_array($typeName, $type)) {
        //如果不被允许，则直接停止程序运行
        return false;
    }
    //重新定义文件名
    if (!$fileName) {
        //如果用户没有定义文件名的格式 则使用默认的格式
        $file['name'] = time() . substr(uniqid(), 6) . '.' . $typeName;
    } else {
        //如果有则使用用户定义的文件名格式
        $file['name'] = $fileName . $typeName;
    }
    //判断用户自定义的文件夹是否存在 不存则创建
    if (!file_exists($path)) {
        mkdir($path, 0777);
    }
    //开始移动文件到相应的文件夹
    if (move_uploaded_file($file['tmp_name'], $path . $file['name'])) {
        //返回上传好的路径
        return $path . $file['name'];
    } else {
        return false;
    }
}

/**
 * xml转成数组
 * @param $xml xml数据
 * @return array $arr
 */
function xmlToArray($xml)
{
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA); //这个时候出来的是对象形式
    $arr       = json_decode(json_encode($xmlstring), true); //先把对象转换成json，然后再解析成数组
    return $arr;
}
/**
 * [http_curl curl请求]
 * @param  [type] $url     [请求地址]
 * @param  string $type    [请求的类型 默认get]
 * @param  [type] $data    [数据 type=post的时可用]
 * @param  [type] $timeout [超时时间]
 * @return [type]          [description]
 */
function http_curl($url, $type = 'GET', $data = null, $timeout = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    is_null($data) or curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // $data为数组
    is_null($timeout) or curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $response = curl_exec($ch);
    if ($error = curl_error($ch)) {
        throw new Exception('http_curl error: ' . $error);
    }
    curl_close($ch);
    return $response;
}

/**
 * [cut_str 截取字符串，以'...'填补在后面]
 * @param  [type] $src [要截取的字符串]
 * @param  [type] $len [长度]
 * @return [type]      [截取完后的字符串+....]
 */
function cut_str($src, $len)
{
    $ret = '';
    $i   = $n   = 0;
    //字符串的字节数
    $str_length = strlen($src);

    while (($n < $len) and ($i <= $str_length)) {
        $temp_str = substr($src, $i, 1);
        // 得到字符串中第$i位字符的ascii码
        $ascnum = Ord($temp_str);
        // 如果ASCII位高与224
        if ($ascnum >= 224) {
            // 根据UTF-8编码规范，将3个连续的字符计为单个字符
            $ret = $ret . substr($src, $i, 3);
            //实际Byte计为3
            $i = $i + 3;
            // 字串长度计1
            $n++;
        }
        // 如果ASCII位高与192
        else if ($ascnum >= 192) {
            // 根据UTF-8编码规范，将2个连续的字符计为单个字符
            $ret = $ret . substr($src, $i, 2);
            // 实际Byte计为2
            $i = $i + 2;
            // 字串长度计1
            $n++;
        }
        // 如果是大写字母
        else if ($ascnum >= 65 && $ascnum <= 90) {
            $ret = $ret . substr($src, $i, 1);
            // 实际的Byte数仍计1个
            $i = $i + 1;
            //但考虑整体美观，大写字母计成一个高位字符
            $n++;
        }
        //其他情况下，包括小写字母和半角标点符号
        else {
            $ret = $ret . substr($src, $i, 1);
            // 实际的Byte数计1个
            $i = $i + 1;
            // 小写字母和半角标点等与半个高位字符宽...
            $n = $n + 0.5;
        }
    }
    // 超过长度时在尾处加上省略号
    if ($str_length > $len) {
        $ret = $ret . "...";
    }
    return $ret;
}
