<?php
session_start();                     // 先開 Session，才會記得使用者狀態

/* ---- 表單送出才會進來 ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $帳號 = $_POST['帳號'];          // 抓表單傳進來的帳號
    $密碼 = $_POST['密碼'];          // 同理，抓密碼

    /* ---- 這裡用超簡單的範例驗證 ----
       正式環境請改資料庫查詢＋雜湊比對
    */
    if ($帳號 === 'admin' && $密碼 === '1234') {  // 帳號密碼都對

        $_SESSION['帳號'] = $帳號;    // 把登入身分寫進 Session

        /* 生成一組亂數 token，之後可拿來做 CSRF 或「記得我」功能 */
        $token = bin2hex(random_bytes(32)); // 32 bytes 亂數 → 64 字元十六進位
        $_SESSION['token'] = $token;        // 也存進 Session 方便比對

        /* —— 如果要長期追蹤，可把 token 寫到資料庫
           這裡示範用簡單的文字檔記：
           每行格式：帳號|token
        */
        file_put_contents(
            'tokens.txt',
            $帳號 . '|' . $token . PHP_EOL, // PHP_EOL = 換行字元
            FILE_APPEND                     // 用附加，不會覆蓋舊資料
        );

        /* 登入成功就直接導去系統首頁 */
        header("Location: 系統首頁.php");
        exit();                             // 記得 exit，後面就不用跑了
    } else {
        /* 帳密錯誤就用 alert 提醒，跳回登入頁 */
        echo "<script>
                alert('帳號或密碼錯誤，請再試一次！');
                window.location.href = '登入.html';
              </script>";
    }
}
?>
