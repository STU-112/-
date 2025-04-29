<?php
session_start();

if (!isset($_SESSION['帳號'])) {
    header("Location:登入.php");
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
echo"<div class='btn-container'>
<form method='POST' action='search.php' enctype='multipart/form-data'>";


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
        echo "<h2>核銷表單</h2>";
		
        echo "<table>";
        echo "<tr><th>欄位</th><th>內容</th></tr>";

        $row = $result->fetch_assoc();

       
		echo "<tr>
        <th>交易單號</th>
    <td>" . htmlspecialchars($row["交易單號"]) . "
        <input type='hidden' name='交易單號' value='" . htmlspecialchars($row["交易單號"]) . "'>
    </td>
		</tr>
				
		<tr>
        <th>受款人代號</th>
     <td>" . htmlspecialchars($row["受款人代號"]) . "
        <input type='hidden' name='受款人代號' value='" . htmlspecialchars($row["受款人代號"]) . "'>
    </td>
		</tr>
			
		<tr>
    <th>填表人</th>
    <td>" . htmlspecialchars($row["經辦代號"]) . "
        <input type='hidden' name='經辦代號' value='" . htmlspecialchars($row["經辦代號"]) . "'>
    </td>
</tr>
		<tr>
    <th>支出項目</th>
    <td>" . htmlspecialchars($row["支出項目"]) . "
        <input type='hidden' name='支出項目' value='" . htmlspecialchars($row["支出項目"]) . "'>
    </td>
</tr>
		
		<tr>
    <th>業務代號</th>
    <td>
        " . htmlspecialchars($row["業務代號"]) . "
        <input type='hidden' name='業務代號' value='" . htmlspecialchars($row["業務代號"]) . "'>
    </td>
</tr>
	";
				
		
        // 新增金額欄位，讓 JavaScript 抓取金額
        echo "<tr>
                <th>金額</th>
                <td id='金額'>" . (isset($row["金額"]) ? htmlspecialchars($row["金額"]) : 0) . "
				<input type='hidden' name='金額' value='" . htmlspecialchars($row["金額"]) . "'></td>
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

$今天日期 = date("Y-m-d");  // 取得今天日期
$預設日期 = htmlspecialchars($row["交易日期"] ?? $今天日期);  
// 有資料就用，沒有就預設今天
echo "<tr>
        <th>交易日期</th>
        <td>
            <input type='date' id='交易日期' name='簽收日' value='$預設日期' min='$今天日期'>
        </td>
      </tr>";

// 交易方式下拉選單
echo "<tr>
        <th>交易方式</th>
        <td>
            <select id='交易方式' name='支付方式' onchange='toggleBankFields()' required>
                <option value=''>請選擇</option>
                <option value='現金'>現金</option>
                <option value='轉帳'>轉帳</option>
                <option value='匯款'>匯款</option>
                <option value='支票'>支票</option>
                <option value='劃撥'>劃撥</option>
            </select>
        </td>
      </tr>";

// 額外欄位容器（轉帳時才顯示）
echo "<tr id='銀行資訊區塊' style='display:none;'>
        <td colspan='2'>
            <div style='border-left: 5px solid; padding-left: 12px; margin-top: 10px;'>
                <label>銀行(郵局)：<br>
                    <input type='text' name='銀行郵局' placeholder='請輸入銀行名稱' style='width:90%; padding:8px; margin-bottom:10px;'>
                </label><br>
                <label>分行：<br>
                    <input type='text' name='分行' placeholder='請輸入分行名稱' style='width:90%; padding:8px; margin-bottom:10px;'>
                </label><br>
                <label>戶名：<br>
                    <input type='text' name='戶名' placeholder='請輸入戶名' style='width:90%; padding:8px; margin-bottom:10px;'>
                </label><br>
                <label>帳號：<br>
                    <input type='text' name='帳號' placeholder='請輸入帳號' style='width:90%; padding:8px;'>
                </label>
            </div>
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



function toggleBankFields() {
    const method = document.getElementById('交易方式').value;
    const section = document.getElementById('銀行資訊區塊');
    // 只要是「轉帳、匯款、劃撥」其中之一就顯示
    if (method === '轉帳' || method === '匯款' || method === '劃撥') {
        section.style.display = 'table-row';
    } else {
        section.style.display = 'none';
    }
}

function confirmUpload() {
    const wantUpload = confirm("是否要上傳圖片與檔案？");
    document.getElementById("uploadFiles").value = wantUpload ? "yes" : "no";
    document.getElementById("uploadForm").submit();
}
    // 7) Modal 顯示/關閉
    function showSuccessModal(){ document.getElementById('successModal').style.display='flex'; }
    function closeSuccessModal(){ document.getElementById('successModal').style.display='none'; }
    function showAskFileModal(){ document.getElementById('askFileModal').style.display='flex'; }
    function closeAskFileModal(){ document.getElementById('askFileModal').style.display='none'; }

    // 8) 表單提交 + 防呆：沒上傳檔案 -> 問「是否要上傳？」
    let globalFormData=null;
    document.getElementById('paymentForm').addEventListener('submit',function(e){
      e.preventDefault();
      // 檢查是否有上傳任何檔案
      const imageFiles = document.getElementById('image_files').files;
      const csvFiles   = document.getElementById('csv_files').files;
      const hasFile = (imageFiles.length > 0 || csvFiles.length > 0);

      if(!hasFile){
        // 未上傳檔案 -> 先問使用者
        globalFormData=new FormData(this);
        showAskFileModal();
        return;
      }
      // 有上傳 -> 直接送出
      doFinalSubmit(new FormData(this));
    });
    document.getElementById('yesFileBtn').addEventListener('click',function(){
      // 按「是」 -> 關閉Modal、回到表單讓使用者上傳
      closeAskFileModal();
    });
    document.getElementById('noFileBtn').addEventListener('click',function(){
      // 按「否」-> 直接送出 (不改單據張數)
      closeAskFileModal();
      if(globalFormData){
        doFinalSubmit(globalFormData);
      }
    });

    function doFinalSubmit(formData){
      const submitBtn=document.getElementById('submitBtn');
      if(submitBtn){
        submitBtn.disabled=true;
        submitBtn.textContent="提交中...";
      }
      fetch(document.getElementById('paymentForm').action,{
        method:'POST', body:formData
      })
      .then(res=>res.text())
      .then(data=>{
        if(data.indexOf('表單提交成功')!==-1){
          showSuccessModal();
          finalizeRecipientCode();
          document.getElementById('paymentForm').reset();
          document.getElementById('選擇表單').value='';
          document.getElementById('formSections').classList.add('hidden');
          const newCode=loadOrCreateRecipientCode();
          displayRecipientCode(newCode);
          setDateConstraints();
        } else {
          alert(data);
        }
        if(submitBtn){
          submitBtn.disabled=false;
          submitBtn.textContent="提交表單";
        }
      })
      .catch(err=>{
        alert('提交失敗，請稍後再試。\n'+err);
        if(submitBtn){
          submitBtn.disabled=false;
          submitBtn.textContent="提交表單";
        }
      });
    }

    // 9) onload 初始化
    window.onload=function(){
      document.getElementById('formSections').classList.add('hidden');
      const code=loadOrCreateRecipientCode();
      displayRecipientCode(code);
      setDateConstraints();
      updateConditionalFields();
      togglePaymentFields();
    };
</script>


</body>
</html>
