<?php

error_reporting(E_ALL);

include './lib/Curl.php';
include './lib/File.php';
include './lib/Response.php';
include './lib/Logger.php';

$path = dirname(__FILE__).'/data/hosts.txt';
$outPath = dirname(__FILE__).'/data/short.txt';
$logPath = dirname(__FILE__).'/log/out.log';

$out = fopen($outPath,'w');
$log = fopen($logPath, 'w');
File::forEachRow($path, function($line) use ($out, $log) {
    try {
        $url = Curl::host2URL($line);
    } catch (Exception $e) {
        fwrite($log, sprintf("[%s] %d: %s\n",
            $line, $e->getCode(), $e->getMessage()));
        return 2;
    }

    // printf("HOST=[%s]\tURL=[%s]\n", $line, $url);
    $html = Curl::testURL($url);
    $html = preg_replace('/charset=gb2312/', 'charset=UTF8', $html);
    // $enc = mb_detect_encoding($html);
    $html = iconv('gbk', 'utf-8', $html);
    preg_match('/存在安全风险，为了保障您的安全，已帮你拦截。/', $html, $matches);
    if (!empty($matches)) {
        var_dump($matches);
        Logger::write(sprintf("[%s] %d: %s\n", $url, 403, '网站被封了'));
        return 1;
    }

    try {
        $shortURL = Curl::shortURL($url);
        Logger::write(sprintf("%s => %s\r\n", $url, $shortURL));
        
        fwrite($out, sprintf("%s\n", $shortURL));
    } catch (Exception $e) {
        fwrite($log, sprintf("[%s] %d: %s\n",
            $url, $e->getCode(), $e->getMessage()));
    }
    return 0;
});
