<?php
// http://47.93.27.106:8064/testurl.php?url=https://www.sk666.vip
// {"code":403,"data":null,"msg":"网站https:\/\/www.sk666.vip被封啦"}

// http://47.93.27.106:8064/testurl.php?url=https://www.baidu.com
// {"code":200,"data":"https:\/\/www.baidu.com","msg":"OK"}

function __autoload($className) {
    include dirname(__FILE__).'/lib/'.$className.'.php';
}

header('Content-Type: application/json; charset=UTF8');

$url = $_GET['url'];

try {
    $html = Curl::testURL($url);
    $html = preg_replace('/charset=gb2312/', 'charset=UTF8', $html);
    // $enc = mb_detect_encoding($html);
    $html = iconv('gbk', 'utf-8', $html);
    preg_match('/存在安全风险，为了保障您的安全，已帮你拦截。/', $html, $matches);
    if (empty($matches)) {
        Response::success('OK', $url);
    } else {
        Response::fail(sprintf("网站%s被封啦", $url), 403);
    }

} catch (Exception $e) {
    Response::fail($e->getMessage(), $e->getCode());
}
