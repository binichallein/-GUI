<?php
include 'conn.php';

// 检查是否传入投诉ID
if (isset($_POST['comid'])) {
    $comid = $_POST['comid'];

    // 删除投诉记录
    $deleteQuery = "DELETE FROM Complaint WHERE Comid = $comid";
    if (mysqli_query($con, $deleteQuery)) {
        echo "投诉已删除成功！";
    } else {
        echo "删除失败，请稍后重试。";
    }
} else {
    echo "未指定投诉ID。";
}

mysqli_close($con);
?>
