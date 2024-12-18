<?php
include 'conn.php';
session_start();

// 检查登录状态
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// 获取用户ID
$userQuery = "SELECT UserID, Role FROM User WHERE UserName = '$username'";
$userResult = mysqli_query($con, $userQuery);
$user = mysqli_fetch_assoc($userResult);
$userID = $user['UserID'];
$userRole = $user['Role'];

// 如果是管理员，则可以查看所有违规记录
if ($userRole === 'Admin') {
    $violationsQuery = "SELECT * FROM Violation ORDER BY RecordTime DESC";
} else {
    $violationsQuery = "SELECT * FROM Violation WHERE UserID = $userID ORDER BY RecordTime DESC";
}

$violations = mysqli_query($con, $violationsQuery);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>违规记录</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
        }
        .header h1 {
            margin: 0;
        }
        .violation-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .violation-table th, .violation-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        .violation-table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>违规记录</h1>
        <a href="usercenter.php">返回用户中心</a>
        <p>用户：<?php echo htmlspecialchars($username); ?></p>
    </div>

    <div class="content">
        <h2>违规记录</h2>
        <table class="violation-table">
            <thead>
                <tr>
                    <th>违规ID</th>
                    <th>违规用户</th>
                    <th>违规原因</th>
                    <th>违规时间</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($violation = mysqli_fetch_assoc($violations)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($violation['RecordID']); ?></td>
                        <td><?php echo htmlspecialchars($violation['UserName']); ?></td>
                        <td><?php echo htmlspecialchars($violation['VioReason']); ?></td>
                        <td><?php echo htmlspecialchars($violation['RecordTime']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
