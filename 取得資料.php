// 取得表單數據
    $受款人 = mysqli_real_escape_string($連接, $_POST['受款人']);
    $填表日期 = mysqli_real_escape_string($連接, $_POST['填表日期']);
    $付款日期 = !empty($_POST['付款日期']) ? "'" . mysqli_real_escape_string($連接, $_POST['付款日期']) . "'" : "NULL";
    $支出項目 = mysqli_real_escape_string($連接, $_POST['支出項目']);
    $活動名稱 = !empty($_POST['活動名稱']) ? "'" . mysqli_real_escape_string($連接, $_POST['活動名稱']) . "'" : "NULL";
    $專案日期 = !empty($_POST['專案日期']) ? "'" . mysqli_real_escape_string($連接, $_POST['專案日期']) . "'" : "NULL";
    $獎學金人數 = !empty($_POST['獎學金人數']) ? intval($_POST['獎學金人數']) : "NULL";
    $專案名稱 = !empty($_POST['專案名稱']) ? "'" . mysqli_real_escape_string($連接, $_POST['專案名稱']) . "'" : "NULL";
    $主題 = !empty($_POST['主題']) ? "'" . mysqli_real_escape_string($連接, $_POST['主題']) . "'" : "NULL";
    $獎學金日期 = !empty($_POST['獎學金日期']) ? "'" . mysqli_real_escape_string($連接, $_POST['獎學金日期']) . "'" : "NULL";
	$經濟扶助 = !empty($_POST['經濟扶助']) ? "'" . mysqli_real_escape_string($連接, $_POST['經濟扶助']) . "'" : "NULL";
	$其他項目 = isset($_POST['其他項目']) ? implode(", ", $_POST['其他項目']) : "NULL"; // 將選中的項目轉為字串
	$說明 = mysqli_real_escape_string($連接, $_POST['說明']);
	$支付方式 = mysqli_real_escape_string($連接, $_POST['支付方式']);
	
	$國字金額 = isset($_POST['國字金額_hidden']) ? $_POST['國字金額_hidden'] : '';
	
	$金額 = !empty($_POST['金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['金額']) . "'" : "NULL";
	$簽收金額 = !empty($_POST['簽收金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收金額']) . "'" : "NULL";
	$簽收人 = !empty($_POST['簽收人']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收人']) . "'" : "NULL";
	$簽收日 = !empty($_POST['簽收日']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收日']) . "'" : "NULL";
	$銀行郵局 = !empty($_POST['銀行郵局']) ? "'" . mysqli_real_escape_string($連接, $_POST['銀行郵局']) . "'" : "NULL";
	$分行 = !empty($_POST['分行']) ? "'" . mysqli_real_escape_string($連接, $_POST['分行']) . "'" : "NULL";
	$戶名 = !empty($_POST['戶名']) ? "'" . mysqli_real_escape_string($連接, $_POST['戶名']) . "'" : "NULL";
	$帳戶 = !empty($_POST['帳戶']) ? "'" . mysqli_real_escape_string($連接, $_POST['帳戶']) . "'" : "NULL";
	$票號 = !empty($_POST['票號']) ? "'" . mysqli_real_escape_string($連接, $_POST['票號']) . "'" : "NULL";
	$到期日 = !empty($_POST['到期日']) ? "'" . mysqli_real_escape_string($連接, $_POST['到期日']) . "'" : "NULL";
	$預收金額 = !empty($_POST['預收金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['預收金額']) . "'" : "NULL";
	