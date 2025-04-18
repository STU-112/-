
    // 您現有的 JavaScript 代碼（包括上述修改）
    // 以下為完整的 JavaScript 代碼，包含改進的 numberToChinese 函式和金額轉換顯示
    // ...

    // 定義一個通用的特殊符號檢查函式
    function containsSpecialChars(str) {
        // 允許的字符：中文、英文、數字、空格
        const regex = /^[a-zA-Z0-9\u4e00-\u9fa5\s]+$/;
        return !regex.test(str);
    }

    function validateForm() {
        let isValid = true;

        // 清除所有之前的錯誤訊息
        const errorElements = document.querySelectorAll('.error-message');
        errorElements.forEach(elem => {
            elem.textContent = "";
            elem.style.display = 'none';
        });
        const inputElements = document.querySelectorAll('.form-group input, .form-group select, .form-group textarea');
        inputElements.forEach(input => {
            input.classList.remove('input-error');
        });

        // 取得當前本地日期，格式為 YYYY-MM-DD
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const todayString = `${year}-${month}-${day}`;

        // 驗證受款人姓名
        const 受款人 = document.getElementById('受款人');
        const 受款人Error = document.getElementById('受款人-error');
        if (!受款人.value.trim()) {
            受款人Error.textContent = "受款人姓名不能空白。";
            受款人Error.style.display = 'block';
            受款人.classList.add('input-error');
            isValid = false;
        } else if (containsSpecialChars(受款人.value)) {
            受款人Error.textContent = "姓名中不可包含特殊符號。";
            受款人Error.style.display = 'block';
            受款人.classList.add('input-error');
            isValid = false;
        }

        // 驗證填表日期
        const 填表日期 = document.getElementById('填表日期');
        const 填表日期Error = document.getElementById('填表日期-error');
        if (!填表日期.value.trim()) {
            填表日期Error.textContent = "請填寫填表日期。";
            填表日期Error.style.display = 'block';
            填表日期.classList.add('input-error');
            isValid = false;
        } else {
            if (填表日期.value !== todayString) {
                填表日期Error.textContent = "填表日期必須是今天。";
                填表日期Error.style.display = 'block';
                填表日期.classList.add('input-error');
                isValid = false;
            }
        }

        // 驗證付款日期
        const 付款日期 = document.getElementById('付款日期');
        const 付款日期Error = document.getElementById('付款日期-error');
        if (!付款日期.value.trim()) {
            付款日期Error.textContent = "請填寫付款日期。";
            付款日期Error.style.display = 'block';
            付款日期.classList.add('input-error');
            isValid = false;
        }

        // 驗證支出項目
        const 支出項目 = document.getElementById('支出項目');
        const 支出項目Error = document.getElementById('支出項目-error');
        if (!支出項目.value) {
            支出項目Error.textContent = "請選擇支出項目。";
            支出項目Error.style.display = 'block';
            支出項目.classList.add('input-error');
            isValid = false;
        }

        // 驗證說明（不檢查特殊符號）
        const 說明 = document.getElementById('說明');
        const 說明Error = document.getElementById('說明-error');
        if (!說明.value.trim()) {
            說明Error.textContent = "說明不能空白。";
            說明Error.style.display = 'block';
            說明.classList.add('input-error');
            isValid = false;
        }

        // 驗證支付方式
        const 支付方式 = document.getElementById('支付方式');
        const 支付方式Error = document.getElementById('支付方式-error');
        if (!支付方式.value) {
            支付方式Error.textContent = "請選擇支付方式。";
            支付方式Error.style.display = 'block';
            支付方式.classList.add('input-error');
            isValid = false;
        }

        // 驗證金額
        const 金額 = document.getElementById('國字金額');
        const 金額Error = document.getElementById('國字金額-error');
        if (!金額.value || parseFloat(金額.value) <= 0) {
            金額Error.textContent = "金額必須大於 0。";
            金額Error.style.display = 'block';
            金額.classList.add('input-error');
            isValid = false;
        }

        // 根據支出項目進行額外驗證
        if (支出項目.value === '活動費用') {
            const 專案活動 = document.getElementById('專案活動');
            const 專案活動Error = document.getElementById('專案活動-error');
            if (!專案活動.value) {
                專案活動Error.textContent = "請選擇專案活動。";
                專案活動Error.style.display = 'block';
                專案活動.classList.add('input-error');
                isValid = false;
            }

            const 活動名稱 = document.getElementById('活動名稱');
            const 活動名稱Error = document.getElementById('活動名稱-error');
            if (!活動名稱.value.trim()) {
                活動名稱Error.textContent = "活動名稱不能空白。";
                活動名稱Error.style.display = 'block';
                活動名稱.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(活動名稱.value)) {
                活動名稱Error.textContent = "活動名稱中不可包含特殊符號。";
                活動名稱Error.style.display = 'block';
                活動名稱.classList.add('input-error');
                isValid = false;
            }

            const 專案日期 = document.getElementById('專案日期');
            const 專案日期Error = document.getElementById('專案日期-error');
            if (!專案日期.value) {
                專案日期Error.textContent = "請選擇專案日期。";
                專案日期Error.style.display = 'block';
                專案日期.classList.add('input-error');
                isValid = false;
            }
        } else if (支出項目.value === '獎學金') {
            const 獎學金人數 = document.getElementById('獎學金人數');
            const 獎學金人數Error = document.getElementById('獎學金人數-error');
            if (!獎學金人數.value || parseInt(獎學金人數.value) < 1) {
                獎學金人數Error.textContent = "獎助學金人數必須至少為 1。";
                獎學金人數Error.style.display = 'block';
                獎學金人數.classList.add('input-error');
                isValid = false;
            }

            const 專案名稱 = document.getElementById('專案名稱');
            const 專案名稱Error = document.getElementById('專案名稱-error');
            if (!專案名稱.value.trim()) {
                專案名稱Error.textContent = "專案名稱不能空白。";
                專案名稱Error.style.display = 'block';
                專案名稱.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(專案名稱.value)) {
                專案名稱Error.textContent = "專案名稱中不可包含特殊符號。";
                專案名稱Error.style.display = 'block';
                專案名稱.classList.add('input-error');
                isValid = false;
            }

            const 主題 = document.getElementById('主題');
            const 主題Error = document.getElementById('主題-error');
            if (!主題.value.trim()) {
                主題Error.textContent = "主題不能空白。";
                主題Error.style.display = 'block';
                主題.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(主題.value)) {
                主題Error.textContent = "主題中不可包含特殊符號。";
                主題Error.style.display = 'block';
                主題.classList.add('input-error');
                isValid = false;
            }

            const 獎學金日期 = document.getElementById('獎學金日期');
            const 獎學金日期Error = document.getElementById('獎學金日期-error');
            if (!獎學金日期.value) {
                獎學金日期Error.textContent = "請選擇獎學金日期。";
                獎學金日期Error.style.display = 'block';
                獎學金日期.classList.add('input-error');
                isValid = false;
            }
        } else if (支出項目.value === '經濟扶助') {
            const 經濟扶助 = document.getElementById('經濟扶助');
            const 經濟扶助Error = document.getElementById('經濟扶助-error');
            if (!經濟扶助.value) {
                經濟扶助Error.textContent = "請選擇經濟扶助項目。";
                經濟扶助Error.style.display = 'block';
                經濟扶助.classList.add('input-error');
                isValid = false;
            }
        } else if (支出項目.value === '其他') {
            const 其他項目 = document.querySelectorAll('input[name="其他項目[]"]');
            const 其他項目Error = document.getElementById('其他項目-error');
            let checked = false;
            其他項目.forEach(checkbox => {
                if (checkbox.checked) {
                    checked = true;
                }
            });
            if (!checked) {
                其他項目Error.textContent = "請至少選擇一項其他項目。";
                其他項目Error.style.display = 'block';
                isValid = false;
            }
        }

        // 根據支付方式進行額外驗證
        if (支付方式.value === '支票') {
            const 票號 = document.getElementById('票號');
            const 票號Error = document.getElementById('票號-error');
            if (!票號.value.trim()) {
                票號Error.textContent = "請填寫票號。";
                票號Error.style.display = 'block';
                票號.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(票號.value)) {
                票號Error.textContent = "票號中不可包含特殊符號。";
                票號Error.style.display = 'block';
                票號.classList.add('input-error');
                isValid = false;
            }

            const 到期日 = document.getElementById('到期日');
            const 到期日Error = document.getElementById('到期日-error');
            if (!到期日.value) {
                到期日Error.textContent = "請選擇到期日。";
                到期日Error.style.display = 'block';
                到期日.classList.add('input-error');
                isValid = false;
            }

            const 預支金額 = document.getElementById('預支金額');
            const 預支金額Error = document.getElementById('預支金額-error');
            if (!預支金額.value || parseFloat(預支金額.value) <= 0) {
                預支金額Error.textContent = "預支金額必須大於 0。";
                預支金額Error.style.display = 'block';
                預支金額.classList.add('input-error');
                isValid = false;
            }
        } else if (支付方式.value === '現金') {
            const 簽收金額 = document.getElementById('簽收金額');
            const 簽收金額Error = document.getElementById('簽收金額-error');
            if (!簽收金額.value || parseFloat(簽收金額.value) <= 0) {
                簽收金額Error.textContent = "簽收金額必須大於 0。";
                簽收金額Error.style.display = 'block';
                簽收金額.classList.add('input-error');
                isValid = false;
            }

            const 簽收人 = document.getElementById('簽收人');
            const 簽收人Error = document.getElementById('簽收人-error');
            if (!簽收人.value.trim()) {
                簽收人Error.textContent = "請填寫簽收人。";
                簽收人Error.style.display = 'block';
                簽收人.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(簽收人.value)) {
                簽收人Error.textContent = "簽收人中不可包含特殊符號。";
                簽收人Error.style.display = 'block';
                簽收人.classList.add('input-error');
                isValid = false;
            }

            const 簽收日 = document.getElementById('簽收日');
            const 簽收日Error = document.getElementById('簽收日-error');
            if (!簽收日.value) {
                簽收日Error.textContent = "請選擇簽收日。";
                簽收日Error.style.display = 'block';
                簽收日.classList.add('input-error');
                isValid = false;
            }
        } else if (['轉帳', '劃撥', '匯款'].includes(支付方式.value)) {
            const 銀行 = document.getElementById('銀行');
            const 銀行Error = document.getElementById('銀行-error');
            if (!銀行.value.trim()) {
                銀行Error.textContent = "請填寫銀行名稱。";
                銀行Error.style.display = 'block';
                銀行.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(銀行.value)) {
                銀行Error.textContent = "銀行名稱中不可包含特殊符號。";
                銀行Error.style.display = 'block';
                銀行.classList.add('input-error');
                isValid = false;
            }

            const 分行 = document.getElementById('transferBankBranch');
            const 分行Error = document.getElementById('transferBankBranch-error');
            if (!分行.value.trim()) {
                分行Error.textContent = "請填寫分行名稱。";
                分行Error.style.display = 'block';
                分行.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(分行.value)) {
                分行Error.textContent = "分行名稱中不可包含特殊符號。";
                分行Error.style.display = 'block';
                分行.classList.add('input-error');
                isValid = false;
            }

            const 戶名 = document.getElementById('transferAccountName');
            const 戶名Error = document.getElementById('transferAccountName-error');
            if (!戶名.value.trim()) {
                戶名Error.textContent = "請填寫戶名。";
                戶名Error.style.display = 'block';
                戶名.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(戶名.value)) {
                戶名Error.textContent = "戶名中不可包含特殊符號。";
                戶名Error.style.display = 'block';
                戶名.classList.add('input-error');
                isValid = false;
            }

            const 帳號 = document.getElementById('transferAccountNumber');
            const 帳號Error = document.getElementById('transferAccountNumber-error');
            if (!帳號.value.trim()) {
                帳號Error.textContent = "請填寫帳號。";
                帳號Error.style.display = 'block';
                帳號.classList.add('input-error');
                isValid = false;
            } else if (containsSpecialChars(帳號.value)) {
                帳號Error.textContent = "帳號中不可包含特殊符號。";
                帳號Error.style.display = 'block';
                帳號.classList.add('input-error');
                isValid = false;
            }
        }

        // 如果有錯誤，滾動到第一個錯誤訊息
        if (!isValid) {
            const firstErrorElement = document.querySelector('.error-message[style*="block"]');
            if (firstErrorElement) {
                firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }

        return true;
    }

    function togglePaymentFields() {
        const selectedItem = document.getElementById('支付方式').value;

        // 隱藏所有支付方式相關的條件欄位
        const fields = ["現金簽收欄位", "郵局欄", "支票欄位"];
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            field.style.display = 'none';
            Array.from(field.querySelectorAll('input')).forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
                input.removeAttribute('required');
            });
            Array.from(field.querySelectorAll('select')).forEach(select => {
                select.value = '';
                select.removeAttribute('required');
            });
        });

        // 根據選擇的支付方式顯示對應的欄位
        if (selectedItem === '現金') {
            const 現金簽收欄位 = document.getElementById("現金簽收欄位");
            現金簽收欄位.style.display = 'block';
            Array.from(現金簽收欄位.querySelectorAll('input')).forEach(input => {
                input.setAttribute('required', 'required');
            });
        } else if (['轉帳', '劃撥', '匯款'].includes(selectedItem)) {
            const 郵局欄 = document.getElementById("郵局欄");
            郵局欄.style.display = 'block';
            Array.from(郵局欄.querySelectorAll('input')).forEach(input => {
                input.setAttribute('required', 'required');
            });
        } else if (selectedItem === '支票') {
            const 支票欄位 = document.getElementById("支票欄位");
            支票欄位.style.display = 'block';
            Array.from(支票欄位.querySelectorAll('input')).forEach(input => {
                input.setAttribute('required', 'required');
            });
        }
    }

    function updateConditionalFields() {
        const selectedItem = document.getElementById('支出項目').value;

        // 隱藏所有支出項目相關的條件欄位
        const fields = ["活動費用欄位", "獎學金欄位", "經濟扶助欄位", "其他欄位"];
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            field.style.display = 'none';
            Array.from(field.querySelectorAll('input, select, textarea')).forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
                input.removeAttribute('required');
            });
            // 如果是select，移除required
            Array.from(field.querySelectorAll('select')).forEach(select => {
                select.removeAttribute('required');
            });
        });

        // 根據選擇的支出項目顯示對應的欄位
        if (selectedItem === '活動費用') {
            const 活動費用欄位 = document.getElementById("活動費用欄位");
            活動費用欄位.style.display = 'block';
            Array.from(活動費用欄位.querySelectorAll('input, select')).forEach(input => {
                input.setAttribute('required', 'required');
            });
        } else if (selectedItem === '獎學金') {
            const 獎學金欄位 = document.getElementById("獎學金欄位");
            獎學金欄位.style.display = 'block';
            Array.from(獎學金欄位.querySelectorAll('input')).forEach(input => {
                input.setAttribute('required', 'required');
            });
        } else if (selectedItem === '經濟扶助') {
            const 經濟扶助欄位 = document.getElementById("經濟扶助欄位");
            經濟扶助欄位.style.display = 'block';
            Array.from(經濟扶助欄位.querySelectorAll('select')).forEach(select => {
                select.setAttribute('required', 'required');
            });
        } else if (selectedItem === '其他') {
            const 其他欄位 = document.getElementById("其他欄位");
            其他欄位.style.display = 'block';
            // 對於checkboxes，不設定required，但在驗證時檢查至少選擇一項
        }
    }

    function convertAmountToChinese() {
        const amountInput = document.getElementById('國字金額');
        const chineseDisplay = document.getElementById('國字金額_display');
        const hiddenInput = document.getElementById('國字金額_hidden');

        const amount = amountInput.value;

        if (amount === '') {
            chineseDisplay.textContent = '';
            hiddenInput.value = '';
            return;
        }

        const integerAmount = Math.floor(parseFloat(amount));
        const chineseAmount = numberToChinese(integerAmount) + "元整";
        chineseDisplay.textContent = chineseAmount;
        hiddenInput.value = chineseAmount;
    }

    // 改進的數字轉中文函式，處理「兩」的使用
    function numberToChinese(num) {
        if (num === 0) return "零元整";
        const digits = "零一二三四五六七八九";
        const units = ["", "十", "百", "千", "萬", "十萬", "百萬", "千萬", "億"];
        let str = num.toString();
        let result = "";
        let zeroCount = 0;

        for (let i = 0; i < str.length; i++) {
            const n = parseInt(str[i]);
            const unit = units[str.length - i - 1];
            if (n !== 0) {
                if (zeroCount > 0) {
                    result += digits[0];
                    zeroCount = 0;
                }
                // 使用「兩」而不是「二」在萬位以上的位置
                if (n === 2 && (unit === "萬" || unit === "億")) {
                    result += "兩" + unit;
                } else {
                    result += digits[n] + unit;
                }
            } else {
                zeroCount++;
            }
        }

        // 處理結尾的零
        result = result.replace(/零+$/, '');
        // 處理十位數的特殊情況
        result = result.replace(/^一十/, "十");
        return result;
    }

    function showSuccessModal() {
        // 設置提交狀態
        sessionStorage.setItem('formSubmitted', 'true');
        document.getElementById('successModal').style.display = 'flex';
    }

    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
    }

    // 處理表單提交
    document.getElementById('paymentForm').addEventListener('submit', function(event) {
        event.preventDefault(); // 阻止表單的預設提交行為

        if (validateForm()) {
            // 禁用提交按鈕以防止重複提交
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = "提交中...";

            // 準備表單數據
            const formData = new FormData(this);

            // 發送AJAX請求
            fetch(this.action, {
                method: this.method,
                body: formData
            })
            .then(response => response.text()) // 根據您的agg.php返回的內容調整
            .then(data => {
                // 假設agg.php返回成功的訊息
                showSuccessModal();
                // 可選：重置表單
                this.reset();
                // 重置條件欄位顯示
                updateConditionalFields();
                togglePaymentFields();
                // 清空國字金額顯示
                document.getElementById('國字金額_display').textContent = '';
                // 重置提交按鈕狀態
                submitBtn.disabled = false;
                submitBtn.textContent = "提交表單";
            })
            .catch(error => {
                console.error('Error:', error);
                alert('提交失敗，請稍後再試。');
                // 重置提交按鈕狀態
                submitBtn.disabled = false;
                submitBtn.textContent = "提交表單";
            });
        }
    });

    window.onload = function() {
        updateConditionalFields();
        togglePaymentFields();

        document.getElementById('支出項目').addEventListener('change', updateConditionalFields);
        document.getElementById('支付方式').addEventListener('change', togglePaymentFields);
        document.getElementById('國字金額').addEventListener('input', convertAmountToChinese);

        // 檢查是否已提交表單
        if (sessionStorage.getItem('formSubmitted') === 'true') {
            showSuccessModal();
            // 清除提交狀態以防止下次載入時再次顯示
            sessionStorage.removeItem('formSubmitted');
        }

        // 即時驗證部分欄位
        const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                validateField(field);
            });
        });
    };

    // 即時驗證函式
    function validateField(field) {
        const fieldId = field.id;
        const errorElement = document.getElementById(`${fieldId}-error`);
        let errorMessage = "";

        if (field.type === 'text') {
            if (!field.value.trim()) {
                errorMessage = "此欄位不能空白。";
            } else if (fieldId !== '說明' && containsSpecialChars(field.value)) {
                // 「說明」欄位不檢查特殊符號
                if (['受款人', '活動名稱', '專案名稱', '主題', '簽收人', '銀行', 'transferBankBranch', 'transferAccountName', 'transferAccountNumber', '票號'].includes(fieldId)) {
                    errorMessage = "此欄位不可包含特殊符號。";
                }
            }
        } else if (field.type === 'date') {
            if (!field.value) {
                errorMessage = "此欄位不能空白。";
            } else if (fieldId === '填表日期') {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                const todayString = `${year}-${month}-${day}`;
                if (field.value !== todayString) {
                    errorMessage = "填表日期必須是今天。";
                }
            }
        } else if (field.type === 'number') {
            if (!field.value || parseFloat(field.value) <= 0) {
                errorMessage = "此欄位必須大於 0。";
            }
        } else if (field.tagName.toLowerCase() === 'select') {
            if (!field.value) {
                errorMessage = "請選擇一個選項。";
            }
        } else if (field.tagName.toLowerCase() === 'textarea') {
            if (!field.value.trim()) {
                errorMessage = "此欄位不能空白。";
            }
        }

        if (errorMessage) {
            errorElement.textContent = errorMessage;
            errorElement.style.display = 'block';
            field.classList.add('input-error');
        } else {
            errorElement.textContent = "";
            errorElement.style.display = 'none';
            field.classList.remove('input-error');
        }
    }
