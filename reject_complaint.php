<?php
include 'conn.php';

// 检查是否传入投诉ID
if (isset($_POST['comid'])) {
    $comid = $_POST['comid'];

    // 更新投诉状态为 'Reject'
    $updateQuery = "UPDATE Complaint SET ComStatus = 'Reject' WHERE Comid = $comid";
    if (mysqli_query($con, $updateQuery)) {
        echo "投诉已打回！";
    } else {
        echo "打回失败，请稍后重试。";
    }
} else {
    echo "未指定投诉ID。";
}

mysqli_close($con);
?>
