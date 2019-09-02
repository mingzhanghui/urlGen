<?php
/**
 * Created by PhpStorm.
 * User: mingzhanghui
 * Date: 8/29/2019
 * Time: 16:49
 */

class Response {
    public static function success($msg, $data) {
        echo json_encode([
            'code' => 200,
            'data' => $data,
            'msg' => $msg
        ], JSON_UNESCAPED_UNICODE);
        exit(0);
    }

    public static function fail($msg, $code) {
        echo json_encode([
            'code' => $code,
            'data' => null,
            'msg' => $msg
        ], JSON_UNESCAPED_UNICODE);
        exit($code);
    }

    public static function result($data) {
        echo json_encode([
            'code' => 200,
            'data' => $data,
            'msg' => 'success'
        ], JSON_UNESCAPED_UNICODE);
        exit(0);
    }
}