<?php


// 加入搜尋條件
if (!empty($search_serial)) {
    $sql .= " AND `count` LIKE '%$search_serial%'";
}
if (!empty($search_item)) {
    $sql .= " AND `支出項目` = '$search_item'";
}

$result = $db_link_預支->query($sql);

?>