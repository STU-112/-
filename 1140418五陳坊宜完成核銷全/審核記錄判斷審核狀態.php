<?php
// 判斷督導審核狀態
        if ($review_result && $review_result->num_rows > 0) {
            $review_row = $review_result->fetch_assoc();
            $opinion = $review_row["狀態"];

            // 根據督導審核意見判斷狀態
            if ($opinion == "通過") {
                $status = "<span style='color: green;'>主任審核中</span>";
                
                // 查詢主任審核意見
                $sql_director_opinion = "SELECT 狀態 FROM 主任審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                $director_result = $db_link_review->query($sql_director_opinion);
                if ($director_result && $director_result->num_rows > 0) {
                    $director_row = $director_result->fetch_assoc();
                    if ($director_row["狀態"] == "通過") {
                        $status = "<span style='color: green;'>執行長審核中</span>";
                        
                        // 查詢執行長審核意見
                        $sql_executive_opinion = "SELECT 狀態 FROM 執行長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                        $executive_result = $db_link_review->query($sql_executive_opinion);
                        if ($executive_result && $executive_result->num_rows > 0) {
                            $executive_row = $executive_result->fetch_assoc();
                            if ($executive_row["狀態"] == "通過") {
                                $status = "<span style='color: green;'>董事長審核中</span>";
                                
                                // 查詢董事長審核意見
                                $sql_chairman_opinion = "SELECT 狀態 FROM 董事長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                $chairman_result = $db_link_review->query($sql_chairman_opinion);
                                if ($chairman_result && $chairman_result->num_rows > 0) {
                                    $chairman_row = $chairman_result->fetch_assoc();
                                    if ($chairman_row["狀態"] == "通過") {
                                        $status = "<span style='color: green;'>會計審核中</span>";
                                        
                                        // 查詢會計審核意見
                                        $sql_accounting_opinion = "SELECT 狀態 FROM 會計審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                        $accounting_result = $db_link_review->query($sql_accounting_opinion);
                                        if ($accounting_result && $accounting_result->num_rows > 0) {
                                            $accounting_row = $accounting_result->fetch_assoc();
                                            if ($accounting_row["狀態"] == "通過") {
                                                $status = "<span style='color: green;'>出納審核中</span>";
                                                
                                                // 查詢出納審核意見
                                                $sql_cashier_opinion = "SELECT 狀態 FROM 出納審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                                $cashier_result = $db_link_review->query($sql_cashier_opinion);
                                                if ($cashier_result && $cashier_result->num_rows > 0) {
                                                    $cashier_row = $cashier_result->fetch_assoc();
                                                    if ($cashier_row["狀態"] == "通過") {
                                                        $status = "<span style='color: green;'>審核通過</span>";
                                                    } else {
                                                        $status = "<span style='color: red;'>出納不通過</span>";
                                                    }
                                                }
                                            } else {
                                                $status = "<span style='color: red;'>會計不通過</span>";
                                            }
                                        }
                                    } else {
                                        $status = "<span style='color: red;'>董事長不通過</span>";
                                    }
                                }
                            } else {
                                $status = "<span style='color: red;'>執行長不通過</span>";
                            }
                        }
                    } else {
                        $status = "<span style='color: red;'>主任不通過</span>";
                    }
                }
            } else {
                $status = "<span style='color: red;'>督導不通過</span>";
            }
        }
?>