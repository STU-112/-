<?php
echo"<form method='get' style='text-align: center; margin-bottom: 20px;'>
    <label>單號: <input type='text' name='search_serial' value='$search_serial'></label>
    <label>支出項目:
        <select name='search_item'>
            <option value=''>-- 全部 --</option>
            <option value='活動費用'" . ($search_item == '活動費用' ? " selected" : "") . ">活動費用</option>
            <option value='獎學金'" . ($search_item == '獎學金' ? " selected" : "") . ">獎學金</option>
            <option value='經濟扶助'" . ($search_item == '經濟扶助' ? " selected" : "") . ">經濟扶助</option>
            <option value='其他'" . ($search_item == '其他' ? " selected" : "") . ">其他</option>
        </select>
    </label>
    <button type='submit'>搜尋</button>
</form>";
?>