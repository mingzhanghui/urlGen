<?php

function __autoload($className) {
    include dirname(__FILE__).'/lib/'.$className.'.php';
}
header('Content-Type: application/json; charset=UTF8');

// php -S 0.0.0.0:8064
// http://47.93.27.106:8064/short.php?url=http://zentao.jiandan100.cn/index.php?m=user&f=login&referer=L2luZGV4LnBocD9tPW1lc3NhZ2UmZj1hamF4R2V0TWVzc2FnZSZ0PWh0bWwmd2luZG93Qmx1cj0w
// http://47.93.27.106:8064/short.php?url=https://so.csdn.net/so/search/s.do?q=redis&t=blog&u=fareast_mzh
$url = $_GET['url'];
// echo $url.'<br />';

$redis = new Redis();
$handle = $redis->connect("127.0.0.1", 6379);
$redis->auth("shi_kuretto");

if (!$handle) {
    Logger::write("Connect redis failed. Check redis.conf");
} else {
    // 试着从redis取得短链接
    $shortURL = $redis->get($url);
    if ($shortURL) {
        $s = sprintf("From redis [%s] => [%s]\n", $url, $shortURL);
        Logger::write($s);

        header("Location: ".$shortURL);
        exit(0);
    }
}

try {
    $html = Curl::testURL($url);
    $html = preg_replace('/charset=gb2312/', 'charset=UTF8', $html);
    // $enc = mb_detect_encoding($html);
    $html = iconv('gbk', 'utf-8', $html);
    preg_match('/存在安全风险，为了保障您的安全，已帮你拦截。/', $html, $matches);
    if (empty($matches)) {
        // Response::success('OK', $url);
    } else {
        Response::fail(sprintf("网站%s被封啦", $url), 403);
    }

} catch (Exception $e) {
    Response::fail($e->getMessage(), $e->getCode());
}

try {
    $shortURL = Curl::shortURL($url);
    // Response::success('获取短链接成功', $shortURL);
    // 保存短链接到redis
    $redis->set($url, $shortURL, 3);
    $redis->expire($url, 7*86400);
    header("Location: ".$shortURL);
} catch (Exception $e) {
    Response::fail($e->getMessage(), $e->getCode());
    Logger::close();
}
