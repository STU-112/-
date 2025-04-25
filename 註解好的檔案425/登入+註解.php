<?php
session_start();                // 先開 Session，這樣才記得你登入的身分

/* ---- 資料庫的連線設定 ---- */
$db_host = "localhost:3307";    // 資料庫主機跟埠號
$db_id   = "root";              // 登入用的帳號
$db_pw   = "3307";              // 帳號的密碼
$db_name = "基金會";             // 要用的資料庫名稱

/* ---- 連線到資料庫 ---- */
$連接 = new mysqli($db_host, $db_id, $db_pw, $db_name);  // 用上面那些資訊去連線

/* 連線失敗就直接擋下來 */
if ($連接->connect_error) {
    die("資料庫連線失敗：" . $連接->connect_error);     // 失敗就顯示錯誤，程式停
}

/* ---- 只有表單用 POST 送過來才會進來 ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 確定表單真的有傳帳號跟密碼過來 */
    if (isset($_POST['db_id'], $_POST['db_pw'])) {
        $帳號 = $_POST['db_id'];   // 把使用者輸入的帳號抓下來
        $密碼 = $_POST['db_pw'];   // 把密碼也抓下來

        /* ==== 先檢查是不是管理員 ==== */
        $admin_username = 'admin'; // 管理員固定帳號
        $admin_password = '1';     // 管理員固定密碼

        /* 如果帳號密碼都對，就直接當管理員登入 */
        if ($帳號 === $admin_username && $密碼 === $admin_password) {
            $_SESSION['帳號'] = $帳號;          // 把帳號寫進 Session
            echo "<script>
                    alert('登入成功，歡迎管理員！');
                    window.location.href = '系統管理員.php'; // 直接跳去後台
                  </script>";
        } else {

            /* ==== 一般使用者流程 ==== */

            /* 先用 real_escape_string 做安全處理，避免 SQL 注入 */
            $帳號 = $連接->real_escape_string($帳號);
            $密碼 = $連接->real_escape_string($密碼);

            /* 用帳號去資料庫找看看有沒有這個人 */
            $select_sql = "SELECT * FROM 註冊資料表 WHERE 帳號 = '$帳號'";
            $帳號查詢   = $連接->query($select_sql);

            /* 找得到帳號就進一步比對密碼 */
            if ($帳號查詢->num_rows > 0) {
                $row = $帳號查詢->fetch_assoc(); // 抓那筆資料

                /* 這邊是直接比明文密碼，正式環境記得改用 hash */
                if ($密碼 === $row['密碼']) {

                    $權限 = $row['權限管理'];      // 他的角色是「經辦人」還是「審核人」
                    $_SESSION['帳號'] = $帳號;    // 把登入狀態寫進 Session

                    /* 依照權限決定要去的頁面 */
                    $跳轉頁面 = ($權限 === '經辦人') ? '申請.php' : '審核人.php';

                    echo "<script>
                            alert('登入成功！歡迎 {$權限} {$帳號}');
                            window.location.href = '{$跳轉頁面}';
                          </script>";
                } else {
                    /* 密碼不對就叫他重試 */
                    echo "<script>
                            alert('密碼錯囉，再試一次！');
                            window.location.href = '登入.html';
                          </script>";
                }
            } else {
                /* 帳號根本找不到，提醒他先去註冊 */
                echo "<script>
                        alert('查無此帳號，可能還沒註冊喔！');
                        window.location.href = '登入.html';
                      </script>";
            }
        }
    }
}

/* ---- 全部都處理完，記得把資料庫連線關掉 ---- */
$連接->close();
?>
