<style>
  :root {
    /* 色彩定義 */
    --primary-color: #4A90E2; /* 天藍色 */
    --secondary-color: #50E3C2; /* 淺綠色 */
    --accent-color-1: #F5A623; /* 橙色 */
    --accent-color-2: #9013FE; /* 紫色 */
    --background-color: #F0F4F8; /* 淺灰背景 */9
    --card-background-color: rgba(255, 255, 255, 0.95); /* 半透明白色 */
    --input-border-color: #CCCCCC; /* 淺灰邊框 */
    --input-focus-border-color: var(--primary-color); /* 聚焦邊框 */
    --button-background-color: var(--primary-color); /* 按鈕主色 */
    --button-hover-background-color: #357ABD; /* 按鈕懸停色 */
    --label-color: #333333; /* 深灰標籤 */
    --text-color: #333333; /* 深灰文字 */
    --border-color: #DDDDDD; /* 淺灰邊框 */
    --error-color: red; /* 錯誤訊息紅色 */
    --transition-speed: 0.3s;
    --font-family: 'Poppins', sans-serif;
    --shadow-color: rgba(0, 0, 0, 0.1);
    --hover-shadow: rgba(0, 0, 0, 0.2);
    --animation-duration: 0.5s;
  }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: var(--font-family);
    background-color: var(--background-color);
    background-image: url('background-pattern.png'); /* 背景圖案(選填) */
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
    position: relative;
    overflow-y: auto; /* 允許頁面滑動 */
  }

  /* 動態背景動畫(可省略) */
  body::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, var(--accent-color-1), var(--accent-color-2));
    animation: rotateBackground 30s linear infinite;
    opacity: 0.05;
    z-index: -1;
  }
  @keyframes rotateBackground {
    from {transform: rotate(0deg);}
    to   {transform: rotate(360deg);}
  }

  .container {
    width: 100%;
    max-width: 800px;
    padding: 60px;
    background: var(--card-background-color);
    border-radius: 20px;
    box-shadow: 0 15px 30px var(--shadow-color);
    border: 1px solid var(--border-color);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
    transition: transform var(--transition-speed), box-shadow var(--transition-speed);
    animation: fadeIn 1s ease forwards;
    opacity: 0;
    transform: translateY(20px);
  }
  @keyframes fadeIn {
    to { opacity:1; transform:translateY(0);}
  }
  .container:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px var(--hover-shadow);
  }
  .container::before,
  .container::after {
    content:'';
    position:absolute;
    width:150px; height:150px;
    border-radius:50%;
    opacity:0.2;
    animation: float 6s ease-in-out infinite;
  }
  .container::before {
    top:-50px; right:-50px; background:var(--accent-color-1);
  }
  .container::after {
    bottom:-50px; left:-50px; background:var(--accent-color-2);
  }
  @keyframes float {
    0%,100%{transform:translateY(0);}
    50%    {transform:translateY(-20px);}
  }

  h1 {
    text-align:center;
    color:var(--primary-color);
    margin-bottom:40px;
    font-size:2.5rem;
    font-weight:700;
    position:relative;
    animation: fadeInDown var(--animation-duration) ease forwards;
    opacity:0;
  }
  @keyframes fadeInDown {
    from {opacity:0; transform:translateY(-20px);}
    to   {opacity:1; transform:translateY(0);}
  }

  form {
    display:flex;
    flex-direction:column;
    gap:30px;
    animation: slideInUp var(--animation-duration) ease forwards;
    opacity:0;
    transform:translateY(20px);
  }
  @keyframes slideInUp {
    from {opacity:0; transform:translateY(20px);}
    to   {opacity:1; transform:translateY(0);}
  }

  .form-group {
    display:flex;
    flex-direction:column;
    width:100%;
    position:relative;
    transition:transform var(--transition-speed);
  }
  .form-group:hover {
    transform:scale(1.02);
  }
  .form-group label {
    margin-bottom:10px; font-weight:600; color:var(--label-color); font-size:1.1rem; position:relative;
  }
  /* 讓冒號跟 * 可自行調整 */
  .form-group label::after {
    content:'*'; 
    color:var(--error-color); 
    margin-left:5px; 
    opacity:0; 
    transition:opacity var(--transition-speed);
  }
  input:focus + .error-message,
  select:focus + .error-message,
  textarea:focus + .error-message {
    display:block; opacity:1;
  }

  input, select, textarea {
    padding:16px 18px;
    font-size:1rem;
    border-radius:10px;
    border:1px solid var(--input-border-color);
    background-color:#fff;
    transition:border-color var(--transition-speed), box-shadow var(--transition-speed), transform var(--transition-speed);
    width:100%;
  }
  input::placeholder, select option, textarea::placeholder {
    color:#aaa;
  }
  input:focus, select:focus, textarea:focus {
    border-color:var(--input-focus-border-color);
    box-shadow:0 0 8px rgba(74,144,226,0.5);
    outline:none;
    transform: scale(1.01);
  }

  input[type="checkbox"], input[type="radio"] {
    width:auto; margin-right:10px;
  }

  .conditional-group {
    display:none;
    padding:25px;
    border:1px solid var(--border-color);
    border-radius:10px;
    background-color:#fff;
    margin-top:20px;
    transition:all var(--transition-speed) ease;
  }

  button {
    padding:16px 18px;
    font-size:1.1rem; font-weight:600; color:#fff;
    background-color:var(--button-background-color);
    border:none; border-radius:10px;
    cursor:pointer;
    transition: background-color var(--transition-speed), transform 0.2s, box-shadow var(--transition-speed);
    width:100%; position:relative; overflow:hidden;
    box-shadow:0 4px 6px rgba(0,0,0,0.1);
  }
  button::after {
    content:'';
    position:absolute;
    top:50%; left:-50%;
    width:200%; height:200%;
    background:rgba(255,255,255,0.2);
    transform:translate(-50%,-50%) rotate(45deg);
    transition:all 0.5s; opacity:0;
  }
  button:hover::after {
    opacity:1; transform:translate(-50%,-50%) rotate(0deg);
  }
  button:hover {
    background-color:var(--button-hover-background-color);
    transform:translateY(-3px) scale(1.02);
    box-shadow:0 10px 20px rgba(0,0,0,0.2);
  }

  .error-message {
    color:var(--error-color);
    font-size:0.9rem;
    margin-top:5px;
    display:none;
    opacity:0;
    transition:opacity var(--transition-speed);
  }

  @media (max-width:768px){
    .container{padding:40px 30px;}
    h1{font-size:2rem;}
    form{gap:20px;}
    input, select, textarea{padding:14px 16px;}
    button{padding:14px 16px;}
  }
  @media (max-width:480px){
    .container{padding:30px 20px;}
    h1{font-size:1.8rem;}
    form{gap:15px;}
    input, select, textarea{padding:12px 14px;}
    button{padding:12px 14px;}
  }

  /* 模態框樣式 */
  .modal {
    display:none; position:fixed; z-index:1000; left:0; top:0;
    width:100%; height:100%;
    overflow:auto; background-color:rgba(0,0,0,0.5);
    display:flex; align-items:center; justify-content:center;
  }
  .modal-content {
    background-color:#fff;
    padding:20px; border-radius:10px; width:90%; max-width:400px;
    text-align:center; position:relative;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
    animation:scaleIn 0.3s ease forwards;
    transform:scale(0.8); opacity:0;
  }
  @keyframes scaleIn {
    to { transform:scale(1); opacity:1;}
  }
  .modal-content h2 {
    margin-top:0; color:var(--primary-color); font-size:1.5rem;
  }
  .modal-content p {
    color:var(--text-color); margin:20px 0; font-size:1rem;
  }
  .modal-content .modal-buttons {
    display:flex; justify-content:center;
  }
  .modal-content button {
    padding:10px 20px; font-size:1rem;
    border:none; border-radius:5px; cursor:pointer; margin:0 10px;
    transition: background-color var(--transition-speed), transform 0.2s;
  }
  .modal-content .close-btn {
    background-color:var(--button-background-color);
    color:#fff;
  }
  .modal-content .close-btn:hover {
    background-color:var(--button-hover-background-color);
    transform:scale(1.05);
  }

  /* 新增錯誤邊框樣式 */
  .input-error {
    border-color:var(--error-color) !important;
    box-shadow:0 0 8px rgba(255,0,0,0.2);
  }

  /* 顯示國字金額樣式 */
  .chinese-amount-display {
    margin-top:5px; font-size:1rem; color:var(--primary-color);
    animation: fadeInAmount 0.5s ease forwards; opacity:0;
  }
  @keyframes fadeInAmount {
    to {opacity:1;}
  }

  /* 幽浮效果 */
  .form-container {
    position:relative;
    animation: popIn 0.5s ease forwards;
    opacity:0; transform:scale(0.95);
  }
  @keyframes popIn {
    to {opacity:1; transform:scale(1);}
  }
  #填表人 {
    font-weight: bold; font-size:1rem; color:#1A73E8; background-color:transparent;
    border:none; outline:none; text-align:left; padding:0; margin:0;
  }

  /* 讓 Rxx 顯示在姓名前面 */
  #recipientCodeSpan {
    font-weight: bold;
    color: red;
    margin-right: 8px;  /* 與「受款人姓名」之間的間距 */
    user-select: none;  /* 無法選取，防止誤刪 */
  }
  </style>