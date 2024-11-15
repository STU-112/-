<?php
$db_host = "localhost:3307"; // 主机和端口
$db_id = "root";              // 数据库用户名
$db_pw = "3307";                  // 数据库密码
$db_name = "indy";         // 数据库名称

// 连接到数据库
$db_link = mysqli_connect($db_host, $db_id, $db_pw);

if (!$db_link) {
    die("连接失败: " . mysqli_connect_error());
}

// 创建数据库
$sql = "CREATE DATABASE IF NOT EXISTS indy"; // 修改为 IF NOT EXISTS
if (mysqli_query($db_link, $sql)) {
    echo "创建数据库成功!!<br>";
} else {
    echo "创建数据库失败: " . mysqli_error($db_link) . "<br>"; // 显示错误信息
}

// 选择要使用的数据库
mysqli_select_db($db_link, "indy");

// 创建数据表
$create_table_sql = "CREATE TABLE IF NOT EXISTS 註冊資料表 (
    使用者id CHAR(20) NOT NULL,
    姓名 CHAR(30) NOT NULL,
    電話 CHAR(20),
    地址 CHAR(30),
    帳號 CHAR(20),
    密碼 CHAR(20),
    PRIMARY KEY (使用者id) -- 考虑为更好的结构添加主键
)";
	
if ($db_link->query($create_table_sql) === TRUE) {
    echo "创建「资料表」成功!!<br>";
} else {
    echo "创建「资料表」失败: " . $db_link->error . "<br>"; // 显示错误信息
}

// 获取表单数据
$userid = mysqli_real_escape_string($db_link, $_POST['userid']);
$name = mysqli_real_escape_string($db_link, $_POST['name']);
$phone = mysqli_real_escape_string($db_link, $_POST['phone']);
$address = mysqli_real_escape_string($db_link, $_POST['address']);
$username = mysqli_real_escape_string($db_link, $_POST['username']);
$password = mysqli_real_escape_string($db_link, $_POST['password']);

// 插入记录的SQL语句
$insert_record_sql = "INSERT INTO 註冊資料表 (使用者id, 姓名, 電話, 地址, 帳號, 密碼) 
VALUES ('$userid', '$name', '$phone', '$address', '$username', '$password')";

if (mysqli_query($db_link, $insert_record_sql)) {
    // 数据插入成功
    echo "<p><h1>提交完成! 正在返回主頁面...</h1></p>";
    echo '<script>
        setTimeout(function() {
            window.location.href = "index.html";
        }, 1000); // 1秒后重定向
    </script>';
} else {
    echo "<h1>提交失敗: 正在返回主頁面...請重新註冊</h1>" . mysqli_error($db_link) . "<br>";
	echo '<script>
        setTimeout(function() {
            window.location.href = "index.html";
        }, 3000); // 1秒后重定向
    </script>';
}

// 关闭数据库连接
mysqli_close($db_link);
?>