<?php

include '審查紀錄頭.php';//include OK

// 合併查詢語句
$sql = "
SELECT 
    b.`count`,
    b.受款人,
    b.填表日期,
    s.支出項目,
    d.說明,
    p.金額
FROM 
    基本資料 AS b
LEFT JOIN 
    支出項目 AS s ON b.`count` = s.`count`
LEFT JOIN 
    說明 AS d ON b.`count` = d.`count`
LEFT JOIN 
    支付方式 AS p ON b.`count` = p.`count`
WHERE 
    p.金額 IS NOT NULL";
	
	
	
	
	include '審核記錄加入搜尋條件.php';//include OK





// 顯示資料
if ($result && $result->num_rows > 0) {
	
	
	include '審查紀錄style.php';//include OK
	
    
	
		
 echo "
    <div class='banner'>
        <a style='align-items: left;' onclick='history.back()'>◀</a>
    </div>";
	
	
	

    echo "<table>";
    echo "<caption>督導已審查紀錄</caption>";
    echo "<tr>";
    echo "<th>單號</th><th>受款人</th><th>金額</th><th>填表日期</th><th>支出項目</th><th>審核狀態</th><th>操作</th>";
    echo "</tr>";
	
	
	
	// 顯示搜尋表單
include '審核紀錄顯示搜尋表單.php';//include OK



    // 顯示每一行資料 
    while ($row = $result->fetch_assoc()) {
        $serial_count = $row["count"];

        // 查詢 Review_comments 資料庫中的督導審核意見
        $sql_review_opinion = "SELECT 狀態 FROM 督導審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $review_result = $db_link_review->query($sql_review_opinion);

        // 預設狀態
        $status = "<span style='color: orange;'>待審核</span>";




        include '審核記錄判斷審核狀態.php';//include OK




        echo "<tr class='second-row'>";
        echo "<td>" . $row["count"] . "</td>";
        echo "<td>" . $row["受款人"] . "</td>";
        echo "<td>" . $row["金額"] . "</td>";
        echo "<td>" . $row["填表日期"] . "</td>";
        echo "<td>" . $row["支出項目"] . "</td>";
        echo "<td>" . $status . "</td>"; // 顯示審核狀態
        echo "<td>
		<div style='display: flex; justify-content: center; align-items: center; height: 100xp;'>
		<div style='display: flex; gap: 10px;'>
            <form method='post' action='審核查看.php'>
                <input type='hidden' name='count' value='" . $row["count"] . "'>
                <button type='submit' name='review'>查看</button>
            </form>
			
			<form method='post' action='審核意見.php'>
                <input type='hidden' name='count' value='" . $row["count"] . "'>
                <button type='submit' name='意見'>意見</button>
            </form>
			</div>
			</div>
			
        </td>";
        echo "</tr>";
}
       
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>沒有資料可顯示。</p>";
}

// 關閉資料庫連線
$db_link_預支->close();
$db_link_review->close();
?>
