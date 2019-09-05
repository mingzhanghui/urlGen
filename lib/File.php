<?php
/**
 * Created by PhpStorm.
 * User: mingzhanghui
 * Date: 8/29/2019
 * Time: 15:53
 */

class File {
    
    const BUFSIZE = 1024;

    /**
     * @param $path
     * @param $handler
     * @param string $comment
     * @throws Exception
     */
    public static function forEachRow($path, $handler, $comment = '#') {
        $handle = fopen($path, "r");
        if (!$handle) {
            return;
        }
        while (($buffer = fgets($handle, self::BUFSIZE)) !== false) {
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
     * 从一个文件中读取随机行
     * @param $path
     * @param string $comment
     * @return string
     * @throws Exception
     */
    public static function getRandomLine($path, $comment = '#') {
        $handle = fopen($path, "r");
        if (!$handle) {
            return '';
        }
        $n = 0;
        while (($buffer = fgets($handle, self::BUFSIZE)) !== false) {
            if (!self::lineIsComment($buffer, $comment)) {
                $n += 1;
            }
        }
        // printf("line count=%d\n", $n);
        if (!feof($handle)) {
            fclose($handle);
            throw new Exception("Error: unexpected fgets() fail", 77);
        }
        rewind($handle);

        $linum = rand(0, $n-1);
        for ($i = 0; $i <= $linum; ) {
            $buffer = fgets($handle, self::BUFSIZE);
            if ($buffer === false) {break;}
            if (!self::lineIsComment($buffer)) {$i++;}
        }
        $buffer = rtrim($buffer, " \t\r\n");
        return $buffer ? $buffer : '';
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
        while (($buffer = fgets($handle, self::BUFSIZE)) !== false) {
            $buffer = trim($buffer);
            if (self::lineIsComment($buffer, $comment)) {
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

    private static function lineIsComment($buffer, $comment='#') {
        preg_match('/^'.$comment.'.*$/', $buffer, $matches);
        return !empty($matches);
    }

}
