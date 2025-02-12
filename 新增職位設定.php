<?php

session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}

// 獲取用戶帳號
$current_user = $_SESSION['帳號'];

// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到 op2 資料庫
$dbname_職位 = "職位設定";


$db_link_職位 = new mysqli($servername, $username, $password, $dbname_職位);


// 檢查資料庫連線
if ($db_link_職位->connect_error) {
    die("連線到 職位 資料庫失敗: " . $db_link_職位->connect_error);
}


// 合併查詢語句
$sql = "SELECT 編號,職位名稱,上限,下限 FROM 職位設定表";
$result = $db_link_職位->query($sql);

?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <title>新增職位</title>
  <!-- Google Fonts：可以換成自己喜歡的字體 -->
  <link rel="preconnect" href="https://fonts.gstatic.com" />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
    rel="stylesheet"
  />
  <style>
    /* 全局設定 */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: "Inter", "Noto Sans TC", sans-serif;
      background: linear-gradient(160deg, #e3f2fd 0%, #fafafa 100%);
      color: #333;
    }

    /* 版心容器 */
    .container {
      max-width: 800px;
      margin: 50px auto;
      padding: 40px;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden; /* 防止內容超出圓角 */
    }

    /* 標題區 */
    .form-header {
      text-align: center;
      margin-bottom: 30px;
    }
    .form-header h1 {
      font-size: 2rem;
      margin-bottom: 8px;
      font-weight: 600;
    }
    .form-header p {
      color: #666;
      font-size: 0.9rem;
    }

    /* 表單設定 */
    form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px; /* 欄位間距 */
    }
    /* 單欄顯示（小螢幕時） */
    @media (max-width: 600px) {
      form {
        grid-template-columns: 1fr;
      }
    }

    /* label 與欄位容器 */
    .form-group {
      display: flex;
      flex-direction: column;
    }

    label {
      font-weight: 600;
      margin-bottom: 8px;
      color: #444;
    }

    /* 輸入欄位與文字區域 */
    input[type="text"],
    textarea {
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 0.95rem;
      transition: border-color 0.3s;
    }
    input[type="text"]:focus,
    textarea:focus {
      outline: none;
      border-color: #90caf9; /* 聚焦時高亮 */
    }

    /* textarea 大小可拖曳 */
    textarea {
      resize: vertical;
      min-height: 100px;
    }

    /* 全寬欄位：如職位描述、福利這種較長文位置於兩欄寬度 */
    .full-width {
      grid-column: 1 / -1;
    }

    /* 提交按鈕容器：置中 */
    .submit-btn-container {
      grid-column: 1 / -1;
      text-align: center;
      margin-top: 20px;
    }
    /* 提交按鈕 */
    input[type="submit"] {
      padding: 14px 32px;
      border: none;
      border-radius: 8px;
      background-color: #42a5f5;
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.2s;
    }
    input[type="submit"]:hover {
      background-color: #1e88e5;
      transform: translateY(-2px);
    }
    input[type="submit"]:active {
      transform: translateY(0);
    }

    /* 底部提示訊息 */
    .form-footer {
      margin-top: 20px;
      text-align: center;
      font-size: 0.85rem;
      color: #888;
    }
	
	tr,td{
		border: 1px solid #ddd;
		
	}	
	.banner {
            
            background-color: #ffffff;
            color: #333;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
			
        }
        .banner a {
            color: #5a3d2b;
            text-decoration: none;
            font-weight: bold;
			text-align:left;
            font-size: 1.2em;
			padding: 5px 20px;
        }
		
		
		
        	
		
  </style>
</head>
<body>
<div class="banner">
<a style='align-items: left;' onclick='history.back()'>◀</a>
    <span>歡迎，<?php echo htmlspecialchars($current_user); ?>！</span> 
   
</div>
  <div class="container">
    <div class="form-header">
      <h1>新增職位</h1>
      <p>請填寫以下資訊，並確認無誤後再送出</p>
    </div>
    <form action="新增職位設定表.php" method="POST">
	
	<!-- 編號-->
      <div class="form-group">
        <label for="title">編號<span style="color: red;">*</span></label>
        <input
          type="text"
          id="編號"
          name="編號"
          placeholder="請輸入編號順序"
          required
		  />
      </div>
	
      <!-- 職位名稱 -->
      <div class="form-group">
        <label for="title">職位名稱 <span style="color: red;">*</span></label>
        <input
          type="text"
          id="title"
          name="title"
          placeholder="請輸入職位名稱"
          required
		  />
      </div>
	  
	  <!-- 金額上限 -->
<div class="form-group">
    <label for="上限">上限<span style="color: red;">*</span></label>
    <input
        type="number"
        id="上限"
        name="上限"
        placeholder="請輸入金額上限"
        min="0"
        required
    />
</div>
	  
	  <!-- 金額下限 -->
<div class="form-group">
    <label for="下限">下限<span style="color: red;">*</span></label>
    <input
        type="number"
        id="下限"
        name="下限"
        placeholder="請輸入金額下限"
        min="0"
        required
    />
</div>
	  
	  
	  
	 <!-- 目前職位 -->
    <h2>目前職位列表</h2>
    <table>
        <thead>
            <tr>
			
                <th>編號</th>	
                <th>職位名稱</th>
				<th>上限</th>
				<th>下限</th>
				<th>編輯</th>
            </tr>
        </thead>
        <tbody>
		

		
            <?php
           if ($result && $result->num_rows > 0) {	
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td style='text-align:center;'>" . $row["編號"] . "</td>";
                    echo "<td>" . $row["職位名稱"] . "</td>";
					echo "<td>" . $row["上限"] . "</td>";
					echo "<td>" . $row["下限"] . "</td>";
					echo "<td style='text-align:center;'>
                <a style='background-color: #007bff;text-decoration: none;border-radius: 5px;color: #fff;' 
				href='刪除職位.php?編號=" . $row["編號"] . "' onclick=\"return confirm('你確定要刪除此職位嗎？');\">
                    刪除
                </a>
              </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>目前無職位資料</td></tr>";
            }
            ?>
        </tbody>
    </table>
	  
	  
	  
	  
      <!-- 提交按鈕 -->
      <div class="submit-btn-container">
        <input type="submit" value="送 出" />
      </div>
    </form>
    <div class="form-footer">
      ※ 確認資料無誤後再點選送出按鈕
    </div>
  </div>
</body>
</html>
<?php
// 關閉資料庫連線
$db_link_職位->close();
?>

