<?php
include 'conn.php';

// 检查是否传入投诉ID和用户信息
if (isset($_POST['comid']) && isset($_POST['userID']) && isset($_POST['userName'])) {
    $comid = $_POST['comid'];
    $userID = $_POST['userID'];
    $userName = $_POST['userName'];

    // 获取该投诉关联的产品发布者信息
    $complaintQuery = "SELECT ProID FROM Complaint WHERE Comid = $comid";
    $complaintResult = mysqli_query($con, $complaintQuery);
    if ($complaintResult && mysqli_num_rows($complaintResult) > 0) {
        $complaint = mysqli_fetch_assoc($complaintResult);
        $proID = $complaint['ProID'];

        // 获取发布该产品的用户ID
        $productQuery = "SELECT UserID FROM spepro WHERE ProID = $proID";
        $productResult = mysqli_query($con, $productQuery);
        if ($productResult && mysqli_num_rows($productResult) > 0) {
            $product = mysqli_fetch_assoc($productResult);
            $productUserID = $product['UserID'];

            // 获取发布该商品的用户的用户名
            $userQuery = "SELECT UserName FROM User WHERE UserID = $productUserID";
            $userResult = mysqli_query($con, $userQuery);
            if ($userResult && mysqli_num_rows($userResult) > 0) {
                $user = mysqli_fetch_assoc($userResult);
                $productUserName = $user['UserName'];

                // 更新投诉状态为 'Resolved'
                $updateQuery = "UPDATE Complaint SET ComStatus = 'Resolved' WHERE Comid = $comid";
                if (mysqli_query($con, $updateQuery)) {
                    // 插入违规记录
                    $violationQuery = "INSERT INTO Violation (UserID, UserName, VioReason, RecordTime) 
                                       VALUES ($productUserID, '$productUserName', '投诉被管理员解决，用户违规', NOW())";
                    if (mysqli_query($con, $violationQuery)) {
                        echo "投诉已解决并记录违规信息。";
                    } else {
                        echo "记录违规信息失败，请稍后重试。";
                    }
                } else {
                    echo "解决投诉失败，请稍后重试。";
                }
            } else {
                echo "未找到发布该商品的用户名。";
            }
        } else {
            echo "未找到该产品的发布者信息。";
        }
    } else {
        echo "未找到该投诉对应的产品。";
    }
} else {
    echo "未指定投诉ID或用户信息。";
}

mysqli_close($con);
?>
