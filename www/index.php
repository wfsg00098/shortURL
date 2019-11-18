<?php
include "settings.php";
date_default_timezone_set("Asia/Shanghai");
$sql = mysqli_connect($sqladdr, $sqluser, $sqlpass);
mysqli_query($sql, "set names utf8mb4");
mysqli_select_db($sql, $sqldbnm);

$short = explode('&', $_SERVER["QUERY_STRING"])[0];
$result = mysqli_query($sql, "select origin,times,password from url where short='" . $short . "'");
mysqli_data_seek($result, 0);
if (!mysqli_num_rows($result)) die("无此短网址");
$row = mysqli_fetch_row($result);
$origin = $row[0];
$times = $row[1];
$pass = $row[2];
if ($times == 0) die("使用次数达到上限");

if ($pass != "")
    if ($pass != $_COOKIE["guaiqihen_short_pass"]) {
        echo("<script>var v=prompt('请输入密码'); document.cookie=\"guaiqihen_short_pass=\"+v;window.parent.window.location.reload();</script>");
        die("密码错误");
    }
if ($times > 0) mysqli_query($sql, "update url set times=times-1 where short='" . $short . "'");
//echo("<script language=\"JavaScript\"> location.replace('".$origin."');</script>");
header("Location: ".$origin);
eof:
