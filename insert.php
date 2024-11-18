<?php
$db_host = "localhost:3307"; // 主机和端口
$db_id = "root";              // 数据库用户名
$db_pw = " ";                  // 空白鍵
$db_name = "預支請款單";         // 数据库名称


// 连接到数据库
$db_link = mysqli_connect($db_host, $db_id, $db_pw);

if (!$db_link) {
    die("连接失败: " . mysqli_connect_error());
}

// 创建数据库
$sql = "CREATE DATABASE chan";
if (mysqli_query($db_link, $sql)) {
    echo "创建数据库成功!!<br>";
} else {
    echo "创建数据库失败: " . mysqli_error($db_link) . "<br>"; // 显示错误信息
}



// 选择要使用的数据库
mysqli_select_db($db_link, "indy");

// 创建数据表
$create_table_sql = "CREATE TABLE 註冊資料表 (
    使用者id CHAR(8),
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
$userid = $_POST['userid'];
$name = $_POST['name'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$username = $_POST['username'];
$password = $_POST['password'];

// 插入记录的SQL语句
$insert_record_sql = "INSERT INTO 註冊資料表 (使用者id, 姓名, 電話, 地址, 帳號, 密碼) 
VALUES ('$userid', '$name', '$phone', '$address', '$username', '$password')";

if (mysqli_query($db_link, $insert_record_sql)) {
    echo "插入记录成功!!<br>";
} else {
    echo "插入记录失败: " . mysqli_error($db_link) . "<br>";
}

// 关闭数据库连接
mysqli_close($db_link);
?>
