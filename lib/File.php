<?php
/**
 * Created by PhpStorm.
 * User: mingzhanghui
 * Date: 8/29/2019
 * Time: 15:53
 */

class File {

    /**
     * @param $path string
     * @param $handler callable
     * @throws Exception
     */
    public static function forEachRow($path, $handler, $comment = '#') {
        $handle = fopen($path, "r");
        if (!$handle) {
            return;
        }
        while (($buffer = fgets($handle, 1024)) !== false) {
            $buffer = trim($buffer);
            preg_match('/^'.$comment.'.*$/', $buffer, $matches);
            if (!empty($matches)) {
                continue;
            }
            call_user_func($handler, $buffer);
        }
        if (!feof($handle)) {
            fclose($handle);
            throw new Exception("Error: unexpected fgets() fail", 77);
        }
        fclose($handle);
    }

    /**
     * @param $path
     * @param $fn
     * @param string $comment  '#' comment symbol
     * @return bool
     * @throws Exception
     */
    public static function someRow($path, $fn, $comment = '#') {
        $handle = fopen($path, "r");
        if (!$handle) {
            throw new Exception("No such file or directory", 2);
        }
        while (($buffer = fgets($handle, 1024)) !== false) {
            $buffer = trim($buffer);
            preg_match('/^'.$comment.'.*$/', $buffer, $matches);
            if (!empty($matches)) {
                continue;
            }
            if (call_user_func($fn, $buffer)) {
                return true;
            }
        }
        if (!feof($handle)) {
            fclose($handle);
            throw new Exception("Error: unexpected fgets() fail", 77);
        }
        fclose($handle);
        return false;
    }

}
