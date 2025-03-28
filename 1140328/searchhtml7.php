<?php
session_start();

if (!isset($_SESSION['帳號'])) {
    header("Location: 0228html.php");
    exit;
}

$servername = "localhost:3307"; 
$username = "root"; 
$password = "3307"; 
$dbname = "基金會"; 

$連接 = new mysqli($servername, $username, $password, $dbname);

if ($連接->connect_error) {
    die("資料庫連接失敗: " . $連接->connect_error);
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>查詢結果</title>
    <style>
        body {
            font-family: "Poppins", "微軟正黑體", sans-serif;
            background-color: #f4f6fa;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        h2 {
            color: #4A90E2;
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            margin: 0 auto;
            width: 100%;
            max-width: 700px;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            border-radius: 10px;
            overflow: hidden;
            table-layout: fixed;
            word-wrap: break-word;
        }

        th, td {
            padding: 10px 12px;
            font-size: 0.95rem;
            line-height: 1.4;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
            word-break: break-word;
        }

        th {
            background-color: #f1f6fc;
            font-weight: 600;
            color: #2c3e50;
        }

        tr:hover {
            background-color: #f9fcff;
        }

        .no-record {
            text-align: center;
            color: red;
            font-size: 1.1rem;
        }

        .btn-container {
            text-align: center;
            margin-top: 30px;
        }

        .back-button {
            padding: 12px 24px;
            background-color: #4A90E2;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .back-button:hover {
            background-color: #357ABD;
            transform: scale(1.05);
        }

        input[type="number"] {
            width: 90%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[readonly] {
            background-color: #f1f1f1;
        }
		
		
		
		
		
		input[type="number"], input[type="text"], input[type="file"] {
    width: 95%;
    padding: 10px;
    font-size: 0.95rem;
    border-radius: 5px;
    border: 1px solid #ccc;
    background-color: #fff;
}

	
		
    </style>
</head>
<body>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["單號查詢"])) {
    $單號查詢 = $_POST["單號查詢"];

    $sql = "
    SELECT 
        b.*, s.*, d.*
    FROM 
        受款人資料檔 AS b
    LEFT JOIN 
        經辦人交易檔 AS s ON b.受款人代號 = s.受款人代號
    LEFT JOIN 	
        經辦業務檔 AS d ON b.受款人代號 = d.受款人代號
    WHERE 
        交易單號 = ?";

    $stmt = $連接->prepare($sql);
    $stmt->bind_param("s", $單號查詢);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h2>查詢結果</h2>";
		
        echo "<table>";
        echo "<tr><th>欄位</th><th>內容</th></tr>";

        $row = $result->fetch_assoc();

        // 顯示資料表內的欄位
       $顯示欄位 = ['交易單號', '交辦代號', '受款人姓名', '支出項目'];

foreach ($顯示欄位 as $欄位名稱) {
    if (isset($row[$欄位名稱]) && $row[$欄位名稱] !== '') {
        echo "<tr><th>" . htmlspecialchars($欄位名稱) . "</th><td>" . htmlspecialchars($row[$欄位名稱]) . "</td></tr>";
    }
}

		

        // 新增金額欄位，讓 JavaScript 抓取金額
        echo "<tr>
                <th>金額</th>
                <td id='金額'>" . (isset($row["金額"]) ? htmlspecialchars($row["金額"]) : 0) . "</td>
              </tr>";

        // 新增實支金額與結餘欄位
        echo "<tr>
                <th>實支金額</th>
                <td>
                    <input type='number' id='實支金額' name='實支金額' placeholder='請輸入實支金額' oninput='calculateBalance()'>
                </td>
              </tr>";
			  
		echo "<tr>
                <th>結餘</th>
                <td>
                    <input type='number' id='結餘' name='結餘' placeholder='結餘將自動計算' readonly>
                </td>
              </tr>";
		echo "<tr>
                <th>交易日期</th>
                <td>
                    <input type='date' id='交易日期' name='交易日期' readonly>
                </td>
              </tr>";
			  
		echo" <tr>
					<th>單據張數</th>
				<td><input type='number' id='單據張數' name='單據張數' min='1' required></td>
				</tr>";
		echo"<tr>
				<th>上傳圖片 (JPG/PNG/JFIF 等)</th>
				<td><input type='file' id='image_files' name='image_files[]' multiple></td>
			</tr>";
		echo"<tr>
				<th>上傳檔案 (CSV/PDF/WORD/EXCEL)</th>
				<td><input type='file' id='csv_files' name='csv_files[]' multiple></td>
			</tr>";


        echo "</table>
		          ";
    } else {
        echo "<p class='no-record'>查無此單號的支出核銷記錄。</p>";
    }

    $stmt->close();
}

$連接->close();
?>

<div class="btn-container">
    <form action="search.php" method="post">
        <input type="hidden" name="單號查詢" value="<?php echo htmlspecialchars($單號查詢); ?>">
        <input type="hidden" id="hiddenActualAmount" name="實支金額">
        <input type="hidden" id="hiddenBalanceAmount" name="結餘">
        
        <button type="submit" class="back-button">上傳</button>
        <button type="button" onclick="history.back()" class="back-button">返回</button>
    </form>
</div>

<script>
function calculateBalance() {
    // 取得「實支金額」與「金額」
    const actual = parseFloat(document.getElementById('實支金額').value) || 0;
    const full = parseFloat(document.getElementById('金額').textContent) || 0;

    // 計算結餘 (金額 - 實支金額)
    const balance = (full - actual).toFixed(2);
    
    // 設置結餘值到 readonly 欄位
    document.getElementById('結餘').value = balance;

    // 設置結餘值到隱藏欄位
    document.getElementById('hiddenBalanceAmount').value = balance;

    // 設置實支金額值到隱藏欄位
    document.getElementById('hiddenActualAmount').value = actual.toFixed(2);
}
</script>


</body>
</html>
