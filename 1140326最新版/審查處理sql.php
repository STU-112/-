<?php
$sql = "
SELECT 
b.*,
s.*,
d.*
	
FROM 
受款人資料檔 AS b
LEFT JOIN 
經辦人交易檔 AS s ON b.受款人代號 = s.受款人代號
LEFT JOIN 
經辦業務檔 AS d ON b.受款人代號 = d.受款人代號
WHERE 
b.受款人代號 = ?";
?>