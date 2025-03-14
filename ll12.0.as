<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>預表單</title>
    <style>
	:root {
    /* 色彩定義 */
    --primary-color: #4A90E2; /* 天藍色 */
    --secondary-color: #50E3C2; /* 淺綠色 */
    --accent-color-1: #F5A623; /* 橙色 */
    --accent-color-2: #9013FE; /* 紫色 */
    --background-color: #F0F4F8; /* 淺灰背景 */
    --card-background-color: rgba(255, 255, 255, 0.95); /* 半透明白色 */
    --input-border-color: #CCCCCC; /* 淺灰邊框 */
    --input-focus-border-color: var(--primary-color); /* 聚焦邊框 */
    --button-background-color: var(--primary-color); /* 按鈕主色 */
    --button-hover-background-color: #357ABD; /* 按鈕懸停色 */
    --label-color: #333333; /* 深灰標籤 */
    --text-color: #333333; /* 深灰文字 */
    --border-color: #DDDDDD; /* 淺灰邊框 */
    --transition-speed: 0.3s;
    --font-family: 'Poppins', sans-serif;
}

* {
    box-sizing: border-box;
}

body {
    font-family: var(--font-family);
    background-color: var(--background-color);
    background-image: url('background-pattern.png'); /* 背景圖案 */
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    padding: 20px;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    color: var(--text-color);
}

.container {
    width: 100%;
    max-width: 800px;
    padding: 60px;
    background: var(--card-background-color);
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border-color);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
}

.container::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    background: var(--accent-color-1);
    border-radius: 50%;
    opacity: 0.2;
}

.container::after {
    content: '';
    position: absolute;
    bottom: -50px;
    left: -50px;
    width: 150px;
    height: 150px;
    background: var(--accent-color-2);
    border-radius: 50%;
    opacity: 0.2;
}

h1 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 40px;
    font-size: 2rem; /* 調小標題字體大小 */
    font-weight: 700;
}

form {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.form-group {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.form-group label {
    margin-bottom: 10px;
    font-weight: 600;
    color: var(--label-color);
    font-size: 1.1rem;
}

input,
select {
    padding: 16px 18px;
    font-size: 1rem;
    border-radius: 10px;
    border: 1px solid var(--input-border-color);
    background-color: #FFFFFF;
    transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
    width: 100%;
}

input::placeholder,
select option {
    color: #AAAAAA;
}

input:focus,
select:focus {
    border-color: var(--input-focus-border-color);
    box-shadow: 0 0 8px rgba(74, 144, 226, 0.5);
    outline: none;
}

input[type="checkbox"],
input[type="radio"] {
    width: auto;
    margin-right: 10px;
}

.form-group .option-group {
    display: flex;
    align-items: center;
}

.form-group .option-group label {
    margin-right: 20px;
    font-weight: 500;
}

.conditional-group {
    display: none;
    padding: 25px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    background-color: #FFFFFF;
    margin-top: 20px;
    transition: all var(--transition-speed) ease;
}

button {
    padding: 16px 18px;
    font-size: 1.1rem;
    font-weight: 600;
    color: #FFFFFF;
    background-color: var(--button-background-color);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color var(--transition-speed), transform 0.2s;
    width: 100%;
}

button:hover {
    background-color: var(--button-hover-background-color);
    transform: translateY(-3px);
}

/* 統一勾選欄位的樣式 */
.form-group input[type="checkbox"],
.form-group input[type="radio"] {
    margin-right: 10px;
}

.form-group .option-group {
    display: flex;
    align-items: center;
}

.form-group .option-group label {
    margin-right: 20px;
    font-weight: 500;
}

/* 響應式設計 */
@media (max-width: 768px) {
    .container {
        padding: 40px 30px;
    }

    h1 {
        font-size: 1.8rem; /* 調整小螢幕下的標題字體大小 */
    }
}

@media (max-width: 480px) {
    .container {
        padding: 30px 20px;
    }

    h1 {
        font-size: 1.6rem; /* 進一步調整標題字體大小 */
    }
}


	-->
    
    </style>
</head>
<body>

  <div class="container">
  <div class="form-container" aria-labelledby="formTitle">
 <form action="agg.php" method="POST" onsubmit="return validateForm()">
            <h1>財團法人台北市失親兒福利基金會</h1>
			
	<div class="form-group">
    <label for="受款人">受款人姓名:</label>
    <input type="text" id="受款人" name="受款人" placeholder="請輸入受款人姓名" required>
	</div>
	<div class="form-group">
    <label for="填表日期">填表日期:</label>
    <input type="date" id="填表日期" name="填表日期" required>
	</div>
	<div class="form-group">
    <label for="付款日期">付款日期:</label>
    <input type="date" id="付款日期" name="付款日期" required>
	</div>

		
		
			
			<div class="form-group">
			<label for="支出項目">請選擇支出項目:</label>
			<select id="支出項目" name="支出項目" onchange="updateConditionalFields()" required>
					<option value="">請選擇</option>
					<option value="活動費用">活動費用</option>
                    <option value="獎學金">獎學金</option>
                    <option value="經濟扶助">經濟扶助</option>
                    <option value="其他">其他</option>
			</select>
			</div>
			
			<!-- 當選擇活動費用時顯示以下欄位 -->
            <div id="活動費用欄位" class="conditional-group">
                <div class="form-group">
                    <label for="專案活動">(專案)活動名稱：</label>
                    <select id="專案活動" name="專案活動">
                        <option value="">請選擇</option>
                        <option value="半日/一日型">半日/一日型：EX:方案活動</option>
                        <option value="過夜型">過夜型：EX:方案活動</option>
                        <option value="企業贊助活動">企業贊助活動:Happy Go</option>
                        <option value="多次型">多次型：EX:成長團體、領袖小組</option>
                        <option value="其他：體驗活動">其他：體驗活動(10萬元預算以上)</option>
                    </select>
					</div>
					
					<div class="form-group">
				<label for="活動名稱">活動名稱:</label>
				<input type="text" id="活動名稱" name="活動名稱">
				</div>
                
                <div class="form-group">
                    <label for="專案日期">日期：</label>
                    <input type="date" id="專案日期" name="專案日期" >
                </div>
            </div>
			
			
			<div id="獎學金欄位" class="conditional-group">
				<div class="form-group">
				<label for="獎學金人數">獎助學金共幾位：</label>
				<input type="text" id="獎學金人數" name="獎學金人數">
				</div>
				<div class="form-group">
				<label for="專案名稱">專案名稱：</label>
				<input type="text" id="專案名稱" name="專案名稱">
				</div>
				<div class="form-group">
				<label for="主題">主題：</label>
				<input type="text" id="主題" name="主題">
				</div>
                <div class="form-group">
                    <label for="獎學金日期">日期：</label>
                    <input type="date" id="獎學金日期" name="獎學金日期" >
                </div>
				</div>
				
				<div id="經濟扶助欄位" class="conditional-group">
                <div class="form-group">
                    <label for="經濟扶助">經濟扶助：</label>
                    <select id="經濟扶助" name="經濟扶助">
                        <option value="">請選擇</option>
                        <option value="急難救助">急難救助</option>
                        <option value="醫療補助">醫療補助</option>
                        <option value="生活扶助">生活扶助</option>
                        <option value="其他專案">其他專案</option>
                    </select>
					</div>
				</div>
				
				
				<div id="其他欄位" class="conditional-group">
                <div class="form-group">
                    <label for="其他項目">其他項目</label>
					
                    <div id="其他項目" name="其他項目">
                        <label><input type="checkbox" name="其他項目[]" value="天使關懷專案"> 天使關懷專案：禮金</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="修繕費"> 修繕費</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="探訪交通差旅"> 探訪交通差旅</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="郵電費"> 郵電費</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="慰問關懷"> 慰問關懷</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="電信費"> 電信費</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="課輔鐘點費"> 課輔鐘點費</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="文具印刷"> 文具印刷</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="心輔諮商"> 心輔諮商</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="電腦用品"> 電腦用品</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="關懷站連結"> 關懷站連結</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="慶典福利"> 慶典福利</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="教育訓練"> 教育訓練</label><br>
                        <label><input type="checkbox" name="其他項目[]" value="雜項購置"> 雜項購置</label><br>
                    </div>
                </div>
            </div>
		
			<div class="form-group">
                <label for="說明">說明：</label>
                <textarea id="notes" class="說明" name="說明" placeholder="輸入您的備註或註解..." required></textarea>
            </div>
		
		 <div class="form-group">
                <label for="支付方式">支付方式</label>
                <select id="支付方式" name="支付方式" onchange="togglePaymentFields()">
                    <option value="">請選擇</option>
                    <option value="現金">現金</option>
                    <option value="轉帳">轉帳</option>
                    <option value="劃撥">劃撥</option>
                    <option value="匯款">匯款</option>
                    <option value="支票">支票</option>
                </select>
            </div>
			
			<div class="form-group">
    <label for="國字金額">金額：</label>
    <input type="number" id="國字金額" name="國字金額" placeholder="請輸入金額" min="0" required aria-required="true" oninput="convertAmountToChinese()">
    <span class="currency-unit" id="元整" name="元整金額">元整</span>
    <!-- 隱藏欄位用來儲存國字金額 -->
    <input type="hidden" id="國字金額_hidden" name="國字金額_hidden">
</div>
		
		<!-- 現金簽收欄 -->
            <div id="現金簽收欄位" class="conditional-group">
                <div class="form-group">
                    <label for="簽收金額">金額：</label>
                    <input type="number" id="簽收金額" name="簽收金額" min="0" >
                </div>
                <div class="form-group">
                    <label for="簽收人">簽收人：</label>
                    <input type="text" id="簽收人" name="簽收人" placeholder="請輸入簽收人">
                </div>
                <div class="form-group">
                    <label for="簽收日">簽收日：</label>
                    <input type="date" id="簽收日" name="簽收日">
                </div>
            </div>

            <!-- 轉帳欄位 -->
            <div id="郵局欄" class="conditional-group" aria-live="polite">
                <div class="form-group">
                    <label for="銀行">銀行(郵局)：</label>
                    <input type="text" id="銀行" name="銀行郵局" placeholder="請輸入銀行名稱">
                </div>
                <div class="form-group">
                    <label for="transferBankBranch">分行：</label>
                    <input type="text" id="transferBankBranch" name="分行" placeholder="請輸入分行名稱">
                </div>
                <div class="form-group">
                    <label for="transferAccountName">戶名：</label>
                    <input type="text" id="transferAccountName" name="戶名" placeholder="請輸入戶名">
                </div>
                <div class="form-group">
                    <label for="transferAccountNumber">帳號：</label>
                    <input type="text" id="transferAccountNumber" name="帳號" placeholder="請輸入帳號">
                </div>
            </div>


            <!-- 支票欄位 -->
            <div id="支票欄位" class="conditional-group" aria-live="polite">
                <div class="form-group">
                    <label for="票號">票號：</label>
                    <input type="text" id="票號" name="票號" placeholder="請輸入票號">
                </div>
                <div class="form-group">
                    <label for="到期日">到期日：</label>
                    <input type="date" id="到期日" name="到期日">
                </div>
                <!-- 新增「請輸入預支金額」欄位 -->
                <div class="form-group">
                    <label for="預支金額">請輸入預支金額：</label>
                    <input type="number" id="預支金額" name="預支金額" min="0">
                </div>
            </div>
		
		
		
		

    <button type="submit">提交表單</button>
    <div id="error-msg" class="alert" style="display:none;">請確保所有必填欄位均已填寫。</div>
</form>
</div>
</div>

<script>






    function validateForm() {
        
        const 填表日期 = document.getElementById('填表日期').value;
        const 受款人 = document.getElementById('受款人').value;
        const 支出項目 = document.getElementById('支出項目').value;
        
		
		
        
        if (!填表日期 || !受款人 || !支出項目) {
            document.getElementById('error-msg').style.display = 'block';
            return false; // 防止表單提交
        }
        document.getElementById('error-msg').style.display = 'none';
        return true; // 允許表單提交
    }
	
	
	
function togglePaymentFields() {
		const selectedItem = document.getElementById('支付方式').value;

    // 隱藏所有條件欄位
    document.getElementById("現金簽收欄位").style.display = 'none';
    document.getElementById("郵局欄").style.display = 'none';
    document.getElementById("支票欄位").style.display = 'none';

    // 根據選擇的支出項目顯示對應的欄位
    if (selectedItem === '現金') {
        document.getElementById("現金簽收欄位").style.display = 'block';
    } else if (selectedItem === '轉帳') {
        document.getElementById("郵局欄").style.display = 'block';
    } else if (selectedItem === '劃撥') {
        document.getElementById("郵局欄").style.display = 'block';
    } else if (selectedItem === '匯款') {
        document.getElementById("郵局欄").style.display = 'block'; 
    } else if (selectedItem === '支票') {
        document.getElementById("支票欄位").style.display = 'block'; 
    }
}
	
function updateConditionalFields() {
		const selectedItem = document.getElementById('支出項目').value;

    // 隱藏所有條件欄位
    document.getElementById("活動費用欄位").style.display = 'none';
    document.getElementById("獎學金欄位").style.display = 'none';
    document.getElementById("經濟扶助欄位").style.display = 'none';
    document.getElementById("其他欄位").style.display = 'none'; // 隱藏「其他」欄位

    // 根據選擇的支出項目顯示對應的欄位
	
	
    if (selectedItem === '活動費用') {
        document.getElementById("活動費用欄位").style.display = 'block';
    } 
	else if (selectedItem === '獎學金') {
        document.getElementById("獎學金欄位").style.display = 'block';
    } 
	else if (selectedItem === '經濟扶助') {
        document.getElementById("經濟扶助欄位").style.display = 'block';
    } 
	else if (selectedItem === '其他') {
        document.getElementById("其他欄位").style.display = 'block'; // 顯示「其他」欄位
    }
}


function convertAmountToChinese() {
    const amount = document.getElementById('國字金額').value;
    const chineseAmount = convertToChinese(amount);
    document.getElementById('元整').textContent = chineseAmount;
    document.getElementById('國字金額_hidden').value = chineseAmount;  // 儲存國字金額到隱藏欄位
}

function convertToChinese(amount) {
    const numMap = ["零", "壹", "貳", "參", "肆", "伍", "陸", "柒", "捌", "玖"];
    const unitMap = ["", "拾", "佰", "仟", "萬", "億"];
    
    let result = "";
    let str = amount.toString().split("").reverse().join("");

    for (let i = 0; i < str.length; i++) {
        const num = parseInt(str[i]);
        const unit = unitMap[i % 4];
        result = numMap[num] + unit + result;
    }

    return result + "元整";
}
	


</script>

</body>
</html>