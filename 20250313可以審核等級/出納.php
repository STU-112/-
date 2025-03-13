<?php
session_start(); // 開啟 Session

// 檢查使用者是否已登入
if (!isset($_SESSION["帳號"])) {
    header("Location: 登入.php"); // 如果沒有登入，跳轉到登入頁面
    exit();
}
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";


include '連線部分.php';

// 取得登入者資訊
$帳號 = $_SESSION["帳號"];
$職位查詢 = "SELECT 員工編號, 部門, 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$職位_result_使用者 = $db_link_註冊->query($職位查詢);

$員工編號 = "";
$部門 = "";
$職位名稱 = "";
$上限 = 0;
$下限 = 0;

if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row = $職位_result_使用者->fetch_assoc();
    $員工編號 = $row["員工編號"];
    $部門 = $row["部門"];
    $職位名稱 = $row["權限管理"];
}

// 讀取對應職位的上限與下限
$範圍_sql = "SELECT 上限, 下限 FROM 職位設定表 WHERE 職位名稱 = '$職位名稱' LIMIT 1";
$範圍_result = $db_link_職位設定->query($範圍_sql);

if ($範圍_result && $範圍_result->num_rows > 0) {
    $範圍_data = $範圍_result->fetch_assoc();
    $上限 = $範圍_data["上限"];
    $下限 = $範圍_data["下限"];
}

// 合併查詢語句
$sql = "
SELECT 
b.受款人代號 ,
s.交易單號,
d.支出項目,
d.填表日期,
s.金額
FROM 
受款人資料檔 AS b
LEFT JOIN 
經辦人交易檔 AS s ON b.受款人代號 = s.受款人代號
LEFT JOIN 
經辦業務檔 AS d ON b.受款人代號 = d.受款人代號
WHERE 
s.金額 IS NOT NULL";

$result = $db_link_預支->query($sql);
// 顯示資料
if ($result && $result->num_rows > 0) {
	 include '審核人style.php';

echo "
<div class='banner'>
    <div class='left'>". htmlspecialchars($部門) ." - ". htmlspecialchars($員工編號) ."</div>
    <div class='right'>
        <span>歡迎，". htmlspecialchars($帳號) ."！</span> 
        <a href='出納審查紀錄.php'>審查紀錄</a>
        <a href='登出.php'>登出</a>
    </div>
</div>";
    echo "<table>";
    echo "<caption>出納審核</caption>";
    echo "<tr>";
    echo "<th>單號</th><th>受款人</th><th>金額</th><th>督導意見</th><th>主任意見</th><th>執行長意見</th><th>董事長意見</th><th>會計意見</th><th>操作</th>";
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        $serial_count = $row["受款人代號"];
        $opinion1 = $opinion2 = $opinion3 = $opinion4 = "<span style='color: orange;'>無須審核</span>";

        // 查詢督導審核意見
        $sql_review_opinion1 = "SELECT 審核意見,狀態 FROM 督導審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $review_result = $db_link_review->query($sql_review_opinion1);

        // 查詢主任審核意見
        $sql_director_opinion2 = "SELECT 審核意見,狀態 FROM 主任審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $director_result = $db_link_review->query($sql_director_opinion2);

        // 查詢執行長審核意見
        $sql_exec_opinion3 = "SELECT 審核意見,狀態 FROM 執行長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $exec_result = $db_link_review->query($sql_exec_opinion3);

        // 查詢董事長審核意見
        $sql_chair_opinion4 = "SELECT 審核意見,狀態 FROM 董事長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $chair_result = $db_link_review->query($sql_chair_opinion4);
		
		// 查詢會計審核意見
        $sql_chair_opinion5 = "SELECT 審核意見,狀態 FROM 會計審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $accounting_result = $db_link_review->query($sql_chair_opinion5);

// 查詢會計審核意見
        $sql_chair_opinion6 = "SELECT 審核意見,狀態 FROM 出納審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $cashier_result = $db_link_review->query($sql_chair_opinion6);

        // 檢查條件
        $show_row = false;
        if ($row["金額"] < 1000 && $review_result && $review_result->num_rows > 0 && $accounting_result && $accounting_result->num_rows > 0) {
            $review_row = $review_result->fetch_assoc();
            $opinion1 = $review_row["審核意見"];
			
			$chair_row = $accounting_result->fetch_assoc();
            $opinion5 = $chair_row["審核意見"];
            $show_row = true;
			
			
			
        } elseif ($row["金額"] < 5000 && $review_result && $review_result->num_rows > 0 && $director_result && $director_result->num_rows > 0 && $accounting_result && $accounting_result->num_rows > 0) {
            $review_row = $review_result->fetch_assoc();
            $opinion1 = $review_row["審核意見"];
            $director_row = $director_result->fetch_assoc();
            $opinion2 = $director_row["審核意見"];
			
			$chair_row = $accounting_result->fetch_assoc();
            $opinion5 = $chair_row["審核意見"];
            $show_row = true;
			
			
			
        } elseif ($row["金額"] <= 50000 && $review_result && $review_result->num_rows > 0 && $director_result && $director_result->num_rows > 0 && $exec_result && $exec_result->num_rows > 0 && $accounting_result && $accounting_result->num_rows > 0) {
			$review_row = $review_result->fetch_assoc();
            $opinion1 = $review_row["審核意見"];
            $director_row = $director_result->fetch_assoc();
            $opinion2 = $director_row["審核意見"];
            $exec_row = $exec_result->fetch_assoc();
            $opinion3 = $exec_row["審核意見"];
			
			$chair_row = $accounting_result->fetch_assoc();
            $opinion5 = $chair_row["審核意見"];
            $show_row = true;
			
			
			
        } elseif ($row["金額"] > 50000 && $review_result && $review_result->num_rows > 0 && $director_result && $director_result->num_rows > 0 && $exec_result && $exec_result->num_rows > 0 && $chair_result && $chair_result->num_rows > 0 && $accounting_result && $accounting_result->num_rows > 0) {
			$review_row = $review_result->fetch_assoc();
            $opinion1 = $review_row["審核意見"];
            $director_row = $director_result->fetch_assoc();
            $opinion2 = $director_row["審核意見"];
            $exec_row = $exec_result->fetch_assoc();
            $opinion3 = $exec_row["審核意見"];
            $chair_row = $chair_result->fetch_assoc();
            $opinion4 = $chair_row["審核意見"];
			
			$chair_row = $accounting_result->fetch_assoc();
            $opinion5 = $chair_row["審核意見"];
            $show_row = true;
        }
		
		// 如果尚未有會計的審核意見，則顯示這筆資料
        if ($cashier_result && $cashier_result->num_rows > 0) {
			continue;
        }
			

        // 顯示符合條件的資料
        if ($show_row) {
            echo "<tr class='second-row'>";
            echo "<td>" . $row["交易單號"] . "</td>";
            echo "<td>" . $row["受款人代號"] . "</td>";
            echo "<td>" . $row["金額"] . "</td>";
            echo "<td>" . $opinion1 . "</td>";
            echo "<td>" . $opinion2 . "</td>";
            echo "<td>" . $opinion3 . "</td>";
            echo "<td>" . $opinion4 . "</td>";
			echo "<td>" . $opinion5 . "</td>";
            echo "<td>
                <form method='post' action='出納審查處理.php'>
                    <input type='hidden' name='受款人代號' value='" . $row["受款人代號"] . "'>
                    <button type='submit' name='review'>審查</button>
                </form>
            </td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>無資料顯示</p>";
}

// 釋放結果與關閉連線
if ($result) {
    $result->free();
}
$db_link_預支->close();
$db_link_review->close();
$db_link_註冊->close();
$db_link_職位設定->close();
?>
