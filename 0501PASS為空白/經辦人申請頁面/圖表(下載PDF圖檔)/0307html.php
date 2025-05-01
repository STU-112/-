
<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 0228html.php");
    exit;
}

// 獲取當前登入的帳號
$current_user = $_SESSION['帳號'];

// 建立資料庫連線
$servername = "localhost:3307"; 
$username = "root"; 
$password = " "; // 使用空白密碼
$dbname = "基金會"; 	

$連接 = new mysqli($servername, $username, $password, $dbname);

// 檢查連接是否成功
if ($連接->connect_error) {
    die("資料庫連接失敗: " . $連接->connect_error);
}

// 查詢當前使用者的員工編號
$sql = "SELECT 員工編號 FROM 註冊資料表 WHERE 帳號 = ?";
$stmt = $連接->prepare($sql);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$stmt->bind_result($員工編號);
$stmt->fetch();
$stmt->close();
$連接->close();
?>

<!DOCTYPE html> 
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>支出項目統計查詢</title>
  <!-- 載入 Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- 載入 html2canvas 與 jspdf (用於匯出PDF) -->
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.0/dist/jspdf.umd.min.js"></script>
  <style>
    /* 基本樣式重設 */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #F0F4F8;
      padding: 20px;
    }
    .container {
      max-width: 700px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h1 { 
      text-align: center; 
      color: #4A90E2; 
      margin-bottom: 20px; 
      font-weight: 600;
    }
    form {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 20px;
    }
    label { 
      font-weight: 600; 
      margin-right: 8px; 
    }
    input[type="month"], button {
      padding: 8px 12px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      background: #4A90E2;
      color: #fff;
      border: none;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover { 
      background: #357ABD; 
    }
    iframe {
      width: 100%;
      height: 600px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    /* PDF 按鈕 */
    .pdf-btn { 
      background: #50C878; 
    }
    
    /* =========== 模態對話框樣式 =========== */
    .modal {
      display: none; 
      position: fixed;
      z-index: 10000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      align-items: center;
      justify-content: center;
    }
    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      max-width: 400px;
      width: 90%;
    }
    .modal-content h2 {
      margin-bottom: 15px;
      font-size: 1.2rem;
      text-align: center;
      color: #4A90E2;
    }
    .modal-close {
      float: right;
      cursor: pointer;
      font-size: 1.2rem;
    }
    .modal-menu {
      list-style: none;
      margin-top: 20px;
      padding-left: 0;
    }
    .modal-menu li {
      padding: 10px;
      border-bottom: 1px solid #ddd;
      cursor: pointer;
      position: relative;
    }
    .modal-menu li:last-child { border-bottom: none; }
    .modal-menu li:hover {
      background: #f2f8ff;
    }
    .submenu {
      list-style: none;
      margin-top: 5px;
      margin-left: 20px;
      display: none;
      padding-left: 0;
    }
    .has-submenu > span.toggle {
      float: right;
      font-size: 0.9rem;
      margin-left: 15px;
      cursor: pointer;
    }
    .has-submenu.open .submenu {
      display: block;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>統計查詢</h1>
    <!-- 送出表單到後端 PHP (0307.php)，並以 iframe 顯示結果 -->
    <form method="GET" action="0307.php" target="resultFrame" id="searchForm">
      <div>
        <label for="expenseItemInput">支出項目：</label>
        <!-- 點擊此按鈕將開啟模態選單 -->
        <button type="button" id="dropdownToggle">全部</button>
        <!-- 隱藏欄位，表單提交時傳送此值 -->
        <input type="hidden" name="expenseItem" id="expenseItemInput" value="全部">
      </div>
      <div>
        <label for="monthFilter">月份：</label>
        <input type="month" id="monthFilter" name="monthFilter">
      </div>
      <button type="submit">查詢</button>
      <button type="button" class="pdf-btn" onclick="exportPDF()">匯出PDF</button>
    </form>
    
    <iframe name="resultFrame" src="0307.php"></iframe>
  </div>
  
  <!-- 模態對話框 -->
  <div id="dropdownModal" class="modal">
    <div class="modal-content">
      <span class="modal-close">&times;</span>
      <h2>請選擇支出項目</h2>
      <ul class="modal-menu">
        <li data-value="全部">全部</li>
        <li class="has-submenu" data-value="W活動費用">
          <span class="parent-label">W活動費用</span>
          <span class="toggle">▸</span>
          <ul class="submenu">
            <li data-value="W活動費用-半日/一日型">半日/一日型</li>
            <li data-value="W活動費用-過夜型">過夜型</li>
            <li data-value="W活動費用-企業贊助活動">企業贊助活動</li>
            <li data-value="W活動費用-多次型">多次型</li>
            <li data-value="W活動費用-其他：體驗活動">其他：體驗活動</li>
          </ul>
        </li>
        <li data-value="X獎學金">X獎學金</li>
        <li class="has-submenu" data-value="Y經濟扶助">
          <span class="parent-label">Y經濟扶助</span>
          <span class="toggle">▸</span>
          <ul class="submenu">
            <li data-value="Y經濟扶助-急難救助">急難救助</li>
            <li data-value="Y經濟扶助-醫療補助">醫療補助</li>
            <li data-value="Y經濟扶助-生活扶助">生活扶助</li>
            <li data-value="Y經濟扶助-其他專案">其他專案</li>
          </ul>
        </li>
        <li data-value="Z其他">Z其他</li>
      </ul>
    </div>
  </div>
  
  <script>
    // 下拉選單處理
    const dropdownToggle = document.getElementById('dropdownToggle');
    const expenseItemInput = document.getElementById('expenseItemInput');
    const dropdownModal = document.getElementById('dropdownModal');
    const modalClose = document.querySelector('.modal-close');
    const modalMenuItems = document.querySelectorAll('.modal-menu li');
    
    dropdownToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      dropdownModal.style.display = 'flex';
    });
    
    modalClose.addEventListener('click', function() {
      dropdownModal.style.display = 'none';
    });
    window.addEventListener('click', function(e) {
      if (e.target === dropdownModal) {
        dropdownModal.style.display = 'none';
      }
    });
    
    modalMenuItems.forEach(item => {
      item.addEventListener('click', function(e) {
        e.stopPropagation();
        if (e.target.classList.contains('toggle')) {
          this.classList.toggle('open');
          const toggle = this.querySelector('span.toggle');
          toggle.textContent = (toggle.textContent === '▸') ? '▾' : '▸';
        } else {
          const value = this.getAttribute('data-value');
          if (value) {
            dropdownToggle.textContent = value;
            expenseItemInput.value = value;
          }
          dropdownModal.style.display = 'none';
        }
      });
    });
    
    // PDF 匯出功能：將 iframe 內的結果置中後加入 PDF
    async function exportPDF() {
      const iframeDoc = document.querySelector('iframe').contentDocument;
      if (!iframeDoc) {
        alert("無法取得 iframe 內容。");
        return;
      }
      const container = iframeDoc.querySelector('.container');
      if (!container) {
        alert("結果尚未載入，或無資料。");
        return;
      }
      try {
        const canvas = await html2canvas(container, { scale: 2 });
        const imgData = canvas.toDataURL("image/png");
        
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'pt', 'a4');
        
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        const canvasWidth = canvas.width;
        const canvasHeight = canvas.height;
        
        let ratio = pdfWidth / canvasWidth;
        let scaledHeight = canvasHeight * ratio;
        if (scaledHeight > pdfHeight) {
          ratio = pdfHeight / canvasHeight;
          scaledHeight = canvasHeight * ratio;
        }
        
        const imgWidth = canvasWidth * ratio;
        const imgHeight = canvasHeight * ratio;
        const xOffset = (pdfWidth - imgWidth) / 2;
        const yOffset = (pdfHeight - imgHeight) / 2;
        
        pdf.addImage(imgData, 'PNG', xOffset, yOffset, imgWidth, imgHeight);
        pdf.save(getPDFFileName());
      } catch (err) {
        console.error(err);
        alert("匯出PDF失敗：" + err);
      }
    }
    
    function getPDFFileName() {
      const now = new Date();
      const y = now.getFullYear();
      const m = String(now.getMonth() + 1).padStart(2, '0');
      const d = String(now.getDate()).padStart(2, '0');
      let expenseItem = expenseItemInput.value || '全部';
      let monthFilter = document.getElementById('monthFilter').value || '全部';
      return `report_${expenseItem}_${monthFilter}_${y}${m}${d}.pdf`;
    }
  </script>
</body>
</html>
