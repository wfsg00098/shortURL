<!DOCTYPE HTML>
<html lang="zh-cn">
<head>
    <title>王七喜の网址映射</title>
    <meta name="theme-color" content="#1c1c1c"/>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="stylesheet" href="assets/css/main.css"/>
</head>
<body>

<!-- Header -->
<header id="header">
    <a href="#" class="logo"><strong>王七喜の网址映射</strong></a>
</header>

<?php
include "settings.php";
date_default_timezone_set("Asia/Shanghai");
$sql = mysqli_connect($sqladdr, $sqluser, $sqlpass);
mysqli_query($sql, "set names utf8mb4;");
mysqli_select_db($sql, $sqldbnm);

function generate_string($length)
{
    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $randStr = str_shuffle($str);
    $rands = substr($randStr, 0, $length);
    return $rands;
}

function generate_short()
{
    global $len, $sql;
    $str = generate_string($len);
    $result = mysqli_query($sql, "select * from url where short='" . $str . "'");
    mysqli_data_seek($result, 0);
    while (mysqli_num_rows($result)) {
        $str = generate_string($len);
        $result = mysqli_query($sql, "select * from url where short='" . $str . "'");
        mysqli_data_seek($result, 0);
    }
    return $str;
}


?>

<!-- Main -->
<section id="main">
    <div class="inner">
        <form method="post" onreset="reset_div()">
            <label for="origin">原网址</label>
            <input type="text" id="origin" name="origin" oninput="origin_url()"/>
            <p id="tip" style="display:none;color:red;">
                原网址格式不正确，请加上协议，如：http://www.guaiqihen.com或https://www.guaiqihen.com</p>
            <br>
            <input type="checkbox" id="custom_short" name="custom_short" onclick="mouseClick()" value="1"/>
            <label for="custom_short">自定义短网址</label>
            <input type="checkbox" id="custom_times" name="custom_times" onclick="mouseClick2()" value="1"/>
            <label for="custom_times">限制使用次数</label>
            <input type="checkbox" id="custom_pass" name="custom_pass" onclick="mouseClick3()" value="1"/>
            <label for="custom_pass">密码保护</label><br>

            <div id="short_div" style="display:none">
                <label for="short">短网址后缀(支持汉字字母数字与下划线)</label>
                <input type="text" id="short" name="short"/><br>
            </div>

            <div id="times_div" style="display:none">
                <label for="times">可使用次数</label>
                <input type="text" id="times" name="times"/><br>
            </div>

            <div id="pass_div" style="display:none">
                <label for="pass">密码(支持字母数字)</label>
                <input type="text" id="pass" name="pass"/><br>
            </div>
            <br>
            <div class="g-recaptcha" data-sitekey="6LcVO2wUAAAAAD2gGSU2-wFKZOD34P3qV3MaFF4z"></div>
            <br>
            <input type="submit" id="submit" name="submit" value="生成"/>
            <input type="reset" value="清空"/>
        </form>
        <?php
        if (isset($_POST["submit"])) {
            session_start();
            if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
                $secret = '6LcVO2wUAAAAALSuPXEDfjxWwCe4HQEfLG9VYySg';
                $gRecaptcha = $_POST['g-recaptcha-response'];
                $gRecaptcha = "https://recaptcha.net/recaptcha/api/siteverify?secret=" . $secret . "&response=" . $_POST['g-recaptcha-response'];
                $response = file_get_contents($gRecaptcha);
                $responseData = json_decode($response);
                if ($responseData->success) {
                    $origin = $_POST["origin"];
                    if (substr($origin, 0, 7) == "http://" or substr($origin, 0, 8) == "https://") {
                        $ip = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"];
                        $date = date('Y-m-d');
                        $time = date('H:i:s');
                        $custom_short = $_POST["custom_short"];
                        $custom_times = $_POST["custom_times"];
                        $custom_pass = $_POST["custom_pass"];
                        $times = -1;
                        if ($custom_times == 1) {
                            $times = $_POST["times"];
                            if (!preg_match("/^[0-9]+$/u", $times)) {
                                echo("<script language=\"JavaScript\">alert(\"次数限制输入有误\");</script>");
                                goto eof;
                            }
                        }
                        $pass = "";
                        if ($custom_pass == 1) {
                            $pass = $_POST['pass'];
                            if (!preg_match("/^[A-Za-z0-9]+$/u", $pass)) {
                                echo("<script language=\"JavaScript\">alert(\"密码输入不符合要求！\");</script>");
                                goto eof;
                            }
                        }
                        $short = "";
                        if ($custom_short == 1) {
                            $short = $_POST["short"];
                            if (!preg_match("/^[A-Za-z0-9_\x{4e00}-\x{9fa5}]+$/u", $short)) {
                                echo("<script language=\"JavaScript\">alert(\"短网址后缀输入不符合要求！\");</script>");
                                goto eof;
                            } else {
                                $result = mysqli_query($sql, "select * from url where short='" . $short . "'");
                                mysqli_data_seek($result, 0);
                                if (mysqli_num_rows($result)) {
                                    echo("<script language=\"JavaScript\">alert(\"短网址后缀已被使用\");</script>");
                                    goto eof;
                                }
                            }
                        } else {
                            $short = generate_short();
                        }
                        mysqli_query($sql, "insert url values('" . $origin . "','" . $short . "'," . $times . ",'" . $ip . "','" . $date . "','" . $time . "','" . $pass . "','v2')");
                        echo("<script language=\"JavaScript\">alert(\"成功！\");</script>");
                        echo("<p>您的短网址为：</p><a href='https://guaiqihen.com/?" . $short . "'>https://guaiqihen.com/?" . $short . "</a>");
                    } else {
                        echo("<script language=\"JavaScript\">alert(\"原网址格式不正确，请加上协议，如：http://www.guaiqihen.com或https://www.guaiqihen.com\");</script>");
                    }
                } else {
                    echo("<script language=\"JavaScript\">alert(\"人机校验失败！\");</script>");
                }

            } else {
                echo("<script language=\"JavaScript\">alert(\"请进行人机校验！\");</script>");
            }
        }
        eof:
        ?>
    </div>
</section>

<script>
    function mouseClick() {
        var status = document.getElementById("custom_short").checked;
        var divv = document.getElementById("short_div");
        if (status) {
            divv.style.display = 'block';
        } else {
            divv.style.display = 'none';
        }
    }

    function mouseClick2() {
        var status = document.getElementById("custom_times").checked;
        var divv = document.getElementById("times_div");
        if (status) {
            divv.style.display = 'block';
        } else {
            divv.style.display = 'none';
        }
    }

    function mouseClick3() {
        var status = document.getElementById("custom_pass").checked;
        var divv = document.getElementById("pass_div");
        if (status) {
            divv.style.display = 'block';
        } else {
            divv.style.display = 'none';
        }
    }

    function reset_div() {
        var divv = document.getElementById("short_div");
        divv.style.display = 'none';
        divv = document.getElementById("times_div");
        divv.style.display = 'none';
        var submit = document.getElementById("submit");
        submit.disable = "true";
    }

    function origin_url() {
        var origin = document.getElementById("origin");
        var http_url = origin.value.substr(0, 7);
        var https_url = origin.value.substr(0, 8);
        var tip = document.getElementById("tip");
        if (http_url === "http://" || https_url === "https://" || origin.value === "") {
            tip.style.display = 'none';
        } else {
            tip.style.display = 'block';
        }
    }
</script>

<!-- Footer -->
<footer id="footer">
    <div class="copyright">Copyright &copy; 2017-<?php echo(date("Y")); ?>. 王七喜 All rights reserved.</div>
</footer>

<!-- Scripts -->
<script src="//recaptcha.net/recaptcha/api.js"></script>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrolly.min.js"></script>
<script src="assets/js/skel.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>


</body>
</html>
