<?php
/* ========== 0. 啟動並檢查登入狀態 ========== */
session_start();                  // 開 Session：瀏覽器才能存取登入資訊

if (!isset($_SESSION['帳號'])) {   // 如果 Session 裡沒有「帳號」這個值
    header("Location: 申請紀錄.php"); // 自動跳回申請紀錄（或可改指向登入頁）
    exit;                          // 後面程式全部不要跑了
}

/* 這裡開始可以確定使用者已登入 */
$current_user = $_SESSION['帳號']; // 把目前登入者的帳號存起來，等等要查資料

/* ========== 1. 準備連線到資料庫 ========== */
$servername = "localhost:3307"; // MySQL 伺服器 + 埠號
$username   = "root";           // MySQL 帳號
$password   = "3307";           // MySQL 密碼
$dbname_預支 = "基金會";          // 我們要用的資料庫名稱（同一顆 DB 放所有表）

// 建立一條連線（mysqli：物件導向寫法）
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

/* 如果連線過程失敗，就直接顯示錯誤後停止 */
if ($db_link_預支->connect_error) {
    die("資料庫連線失敗：" . $db_link_預支->connect_error);
}

/* ========== 2. 讀取網址上的搜尋條件（若有） ==========
   使用者如果在網址後面加 ?search_serial=xxx&search_item=yyy
   就能幫他做條件篩選，沒有就留空字串 */
$search_serial = $_GET['search_serial'] ?? ''; // 單號
$search_item   = $_GET['search_item']   ?? ''; // 支出項目

/* ========== 3. 用帳號查員工編號 ==========
   註冊資料表：帳號→員工編號，之後篩資料要用 */
$sql_註冊 = "SELECT 員工編號 FROM 註冊資料表 WHERE 帳號 = ?";
$stmt = $db_link_預支->prepare($sql_註冊); // 準備預處理
$stmt->bind_param("s", $current_user);    // 把帳號塞進 ?
$stmt->execute();                         // 執行
$stmt->bind_result($員工編號);            // 把結果綁到變數
$stmt->fetch();                           // 讀取一行
$stmt->close();                           // 收工

/* ========== 4. 組主查詢（只抓自己填的單） ========== */
$sql = "
SELECT 
    b.*,  /* 受款人資料檔：b 開頭 */
    s.*,  /* 經辦人交易檔：s 開頭（真正金額） */
    d.*   /* 經辦業務檔：d 開頭（表單主檔） */
FROM 受款人資料檔 AS b
LEFT JOIN 經辦人交易檔 AS s ON b.受款人代號 = s.受款人代號
LEFT JOIN 經辦業務檔 AS d ON b.受款人代號 = d.受款人代號
WHERE 
    s.金額 IS NOT NULL          /* 確定交易檔真的有金額 */
    AND d.經辦代號 = '$員工編號' /* 只顯示「我」經辦的單 */
";

/* 4-1. 如果使用者輸入搜尋條件，就再加到 SQL */
if ($search_serial !== '') {
    $sql .= " AND `交易單號` LIKE '%$search_serial%'";
}
if ($search_item !== '') {
    $sql .= " AND `支出項目` = '$search_item'";
}

/* ========== 5. 執行查詢，結果存在 $result ========== */
$result = $db_link_預支->query($sql);

/* ========== 6. 螢幕輸出 ========== */
if ($result && $result->num_rows > 0) {
    /* 6-1. 先印 CSS 與頁首（banner + 搜尋列） */
    echo "
    <style>
      /* 全域小設定 */
      *{margin:0;padding:0;box-sizing:border-box;}
      body{
        width:100%;height:100%;font-family:'Noto Sans TC',Arial,sans-serif;
        background:#f5d3ab;color:#5a4a3f;
      }
      /* 上方橫幅 */
      .banner{
        width:100%;background:linear-gradient(to bottom,#fbe3c9,#f5d3ab);
        color:#5a3d2b;display:flex;align-items:center;
        padding:10px 20px;box-shadow:0 2px 5px rgba(0,0,0,0.2);
      }
      .banner a{color:#5a3d2b;text-decoration:none;font-weight:bold;font-size:1.2em;padding:5px 20px;}
      .banner a:hover{color:#007bff;}
      /* 表格樣式 */
      table{
        width:80%;margin:20px auto;border-collapse:collapse;
        font-family:Arial,sans-serif;
      }
      table,th,td{border:1px solid #ddd;}
      th,td{padding:12px;text-align:center;}
      th{background:#f2f2f2;color:#333;}
      tr.second-row{background:#fff;}
      tr:nth-child(even){background:#f9f9f9;}
      tr:hover{background:#f1f1f1;}
      caption{font-size:1.5em;margin:10px;font-weight:bold;}
    </style>

    <div class='banner'>
        <a onclick='history.back()'>◀</a>  <!-- 返回上一頁 -->
        <div style='margin-left:auto;'>歡迎，" . htmlspecialchars($current_user) . "！</div>
    </div>

    <!-- 搜尋列：GET 重新整理頁面 -->
    <form method='get' style='text-align:center;margin:20px 0;'>
        <label>單號: <input type='text' name='search_serial' value='$search_serial'></label>
        <label>支出項目:
            <select name='search_item'>
                <option value=''>-- 全部 --</option>
                <option value='活動費用'" . ($search_item=='活動費用' ? ' selected' : '') . ">活動費用</option>
                <option value='獎學金'"   . ($search_item=='獎助學金' ? ' selected' : '') . ">獎學金</option>
                <option value='經濟扶助'" . ($search_item=='經濟扶助' ? ' selected' : '') . ">經濟扶助</option>
                <option value='其他'"     . ($search_item=='其他'     ? ' selected' : '') . ">其他</option>
            </select>
        </label>
        <button type='submit'>搜尋</button>
    </form>
    ";

    /* 6-2. 表格表頭 */
    echo "
    <table>
      <caption>申請紀錄</caption>
      <tr>
        <th>單號</th><th>填表人</th><th>受款人</th><th>金額</th>
        <th>填表日期</th><th>支出項目</th><th>審核狀態</th><th>操作</th>
      </tr>
    ";

    /* 6-3. 逐筆列出資料 */
    while ($row = $result->fetch_assoc()) {
        $serial_count = $row["受款人代號"];  // 以「受款人代號」當查審核意見的鍵

        /* ========== 6-3-1. 計算審核狀態 ==========
           依序檢查各級審核表，只要遇到「不通過」就停止往下 */
        $status = "<span style='color:orange;'>待審核</span>";

        // 部門主管
        $chk = $db_link_預支->query("SELECT 狀態 FROM 部門主管審核意見 WHERE 單號='$serial_count' LIMIT 1");
        if ($chk && $chk->num_rows) {
            $op = $chk->fetch_assoc()["狀態"];
            if ($op == "通過") {
                $status = "<span style='color:green;'>主任審核中</span>";
                // 主任
                $chk = $db_link_預支->query("SELECT 狀態 FROM 主任審核意見 WHERE 單號='$serial_count' LIMIT 1");
                if ($chk && $chk->num_rows && $chk->fetch_assoc()["狀態"]=="通過") {
                    $status = "<span style='color:green;'>執行長審核中</span>";
                    // 執行長
                    $chk = $db_link_預支->query("SELECT 狀態 FROM 執行長審核意見 WHERE 單號='$serial_count' LIMIT 1");
                    if ($chk && $chk->num_rows && $chk->fetch_assoc()["狀態"]=="通過") {
                        $status = "<span style='color:green;'>董事長審核中</span>";
                        // 董事長
                        $chk = $db_link_預支->query("SELECT 狀態 FROM 董事長審核意見 WHERE 單號='$serial_count' LIMIT 1");
                        if ($chk && $chk->num_rows && $chk->fetch_assoc()["狀態"]=="通過") {
                            $status = "<span style='color:green;'>會計審核中</span>";
                            // 會計
                            $chk = $db_link_預支->query("SELECT 狀態 FROM 會計審核意見 WHERE 單號='$serial_count' LIMIT 1");
                            if ($chk && $chk->num_rows && $chk->fetch_assoc()["狀態"]=="通過") {
                                $status = "<span style='color:green;'>出納審核中</span>";
                                // 出納
                                $chk = $db_link_預支->query("SELECT 狀態 FROM 出納審核意見 WHERE 單號='$serial_count' LIMIT 1");
                                if ($chk && $chk->num_rows) {
                                    $status = $chk->fetch_assoc()["狀態"]=="通過"
                                        ? "<span style='color:green;'>審核通過</span>"
                                        : "<span style='color:red;'>出納不通過</span>";
                                }
                            } else {
                                $status = "<span style='color:red;'>會計不通過</span>";
                            }
                        } else {
                            $status = "<span style='color:red;'>董事長不通過</span>";
                        }
                    } else {
                        $status = "<span style='color:red;'>執行長不通過</span>";
                    }
                } else {
                    $status = "<span style='color:red;'>主任不通過</span>";
                }
            } else {
                $status = "<span style='color:red;'>部門主管不通過</span>";
            }
        }

        /* 6-3-2. 輸出一列表格 */
        echo "
        <tr class='second-row'>
          <td>{$row['交易單號']}</td>
          <td>{$row['經辦代號']}</td>
          <td>{$row['受款人代號']}</td>
          <td>{$row['金額']}</td>
          <td>{$row['填表日期']}</td>
          <td>{$row['支出項目']}</td>
          <td>$status</td>
          <td>
            <div style='display:flex;justify-content:center;gap:10px;'>
              <!-- 查看按鈕 -->
              <form method='post' action='查看.php'>
                <input type='hidden' name='受款人代號' value='{$row['受款人代號']}' />
                <button type='submit'>查看</button>
              </form>
              <!-- 意見按鈕 -->
              <form method='post' action='意見.php'>
                <input type='hidden' name='受款人代號' value='{$row['受款人代號']}' />
                <button type='submit'>意見</button>
              </form>
            </div>
          </td>
        </tr>";
    }

    echo "</table>";
} else {
    /* 查無資料時顯示一句話 */
    echo "<p style='text-align:center;'>沒有資料可顯示。</p>";
}

/* ========== 7. 結束：關閉資料庫連線 ========== */
$db_link_預支->close();
?>
