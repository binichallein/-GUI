<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// 检查当前用户是否为管理员
$username = $_SESSION['username'];
$userRoleQuery = "SELECT Role FROM User WHERE UserName='$username'";
$userRoleResult = mysqli_query($con, $userRoleQuery);
$userRole = mysqli_fetch_assoc($userRoleResult)['Role'];

if ($userRole !== 'Admin') {
    echo "<script>alert('您无权访问此页面！'); window.location.href='usercenter.php';</script>";
    exit();
}

// 获取违规用户视图中的信息
$query = "SELECT * FROM UsersWithMultipleViolations";
$result = mysqli_query($con, $query);

// 处理冻结用户请求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'freeze') {
    $freezeProcedure = "CALL FreezeViolatingUsers()";
    if (mysqli_query($con, $freezeProcedure)) {
        echo "<script>alert('违规用户已冻结！');</script>";
    } else { 
        echo "<script>alert('冻结操作失败，请稍后重试！');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查看违规用户</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f4f4f4;
        }
        .actions {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>违规用户管理</h1>
    </div>

    <div class="actions">
        <form method="POST" action="view_violations.php">
            <input type="hidden" name="action" value="freeze">
            <button type="submit">冻结违规用户</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>用户ID</th>
                <th>用户名</th>
                <th>违规次数</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['UserID']); ?></td>
                    <td><?php echo htmlspecialchars($row['UserName']); ?></td>
                    <td><?php echo htmlspecialchars($row['ViolationCount']); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
