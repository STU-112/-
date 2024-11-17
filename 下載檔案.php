<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];

    // 根據 file 參數設定檔案名稱
    switch ($file) {
        case 'expense_report':
            $filePath = 'files/支出報帳.xlsx';
            break;
        case 'expense_clearance':
            $filePath = 'files/支出核銷.xlsx';
            break;
        case 'advance_request':
            $filePath = 'files/預支請款.xlsx';
            break;
        default:
            die("無效的檔案請求");
    }

    // 檢查檔案是否存在
    if (file_exists($filePath)) {
        // 設定下載 headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // 輸出檔案內容
        readfile($filePath);
        exit;
    } else {
        die("檔案不存在");
    }
} else {
    die("未指定檔案");
}
