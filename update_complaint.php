<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comid = intval($_POST['comid']);
    $review = mysqli_real_escape_string($con, $_POST['review']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    $updateQuery = "
        UPDATE Complaint 
        SET ComStatus = '$status', Review = '$review'
        WHERE Comid = $comid";
    
    if (mysqli_query($con, $updateQuery)) {
        echo "投诉更新成功！";
    } else {
        echo "投诉更新失败：" . mysqli_error($con);
    }
}
?>
