<?php

// echo '<pre>'; var_dump($_SERVER); die;

/**
 * @param $agent
 * @return string "pc","wx","qq","mqqbrowser"
 */
function userAgent($agent) {
    $agent = strtolower($agent);

    $wx = strstr($agent, 'micromessenger');
    if (is_string($wx)) {
        return "wx";
    }
    $qq = strstr($agent, ' qq');
    $qqBrowser = strstr($agent, 'mqqbrowser');
    if (is_string($qq)) {
        if ($qqBrowser === false) {
            return 'qq';
        }
    } else {
        if (is_string($qqBrowser)) {
            return 'mqqbrowser';
        }
    }
    return 'pc';
}

// $agent = "Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/7.0.5(0x17000523) NetType/WIFI Language/en";
// echo userAgent($agent);
$agent = userAgent($_SERVER['HTTP_USER_AGENT']);
if ($agent === 'wx') {
    header("Location: https://weixin.qq.com");
} else if ($agent === 'qq') {
    header("Location: https://www.qq.com");
} else {
    header("Location: https://www.baidu.com");
}