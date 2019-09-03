<?php

function ul($arList) {
    echo "<ul>";
    foreach ($arList as $el) {
        echo "<li>".htmlspecialchars($el)."</li>";
    }
    echo "</ul>";
}

$redis = new Redis();

$handle = $redis->connect("127.0.0.1", 6379);
$redis->auth("shi_kuretto");

if (!$handle) {
    echo "<p>Conenct to redis failed!</p>";
}

echo "Connect to server successfull<br />";
echo "<pre>";
var_dump($handle);
echo "</pre>";

echo "<p>Server is running: ".$redis->ping()."</p>";

$redis->del("test:string");
$redis->set("test:string", "Redis Tutorial");
echo "Stored string in redis::".$redis->get("test:string")."<br />";

$redis->del("test:list");
$a = ["Redis", "MongoDB", "MySQL"];
array_walk($a, function($item, $key) use ($redis) {
    $redis->lpush("test:list", $item);
});

$arList = $redis->lrange("test:list", 0, 5);
ul($arList);

$keys = $redis->keys("test:*");
ul($keys);