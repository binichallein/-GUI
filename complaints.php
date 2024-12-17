<?php
include 'conn.php';
session_start();

// 检查登录状态
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// 检查用户权限
$userRoleQuery = "SELECT Role FROM User WHERE UserName = '$username'";
$userRoleResult = mysqli_query($con, $userRoleQuery);
$userRole = mysqli_fetch_assoc($userRoleResult)['Role'];

if ($userRole !== 'Admin') {
    echo "<script>alert('无权限访问此页面！'); window.location.href = 'usercenter.php';</script>";
    exit();
}

// 获取未处理或待处理的投诉信息
$complaintsQuery = "
    SELECT c.Comid, c.ProID, c.UserID, c.Reason, c.ComStatus, c.Review, c.UserName, c.ProName
    FROM Complaint c
    WHERE c.ComStatus != 'Resolved'
    ORDER BY c.Comid DESC";
$complaints = mysqli_query($con, $complaintsQuery);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投诉管理</title>
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
        .complaints-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .complaints-table th, .complaints-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        .complaints-table th {
            background-color: #f4f4f4;
        }
        button {
            padding: 5px 10px;
            margin: 2px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        textarea {
            width: 100%;
            height: 50px;
            margin-top: 5px;
        }
        .delete-btn {
            background-color: #f44336;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateReview(comid) {
            const review = document.getElementById(`review_${comid}`).value;
            const status = document.getElementById(`status_${comid}`).value;

            $.ajax({
                url: 'update_complaint.php',
                type: 'POST',
                data: { comid: comid, review: review, status: status },
                success: function (response) {
                    alert(response);
                    location.reload();
                },
                error: function () {
                    alert('更新失败，请稍后重试。');
                }
            });
        }

        function rejectComplaint(comid) {
            if (confirm("确定要打回此投诉吗？")) {
                $.ajax({
                    url: 'reject_complaint.php',
                    type: 'POST',
                    data: { comid: comid },
                    success: function(response) {
                        alert(response);
                        location.reload();
                    },
                    error: function() {
                        alert('操作失败，请稍后重试。');
                    }
                });
            }
        }

        function resolveComplaint(comid, userID, userName) {
            if (confirm("确定已解决此投诉并记录违规信息吗？")) {
                $.ajax({
                    url: 'resolve_complaint.php',
                    type: 'POST',
                    data: { comid: comid, userID: userID, userName: userName },
                    success: function(response) {
                        alert(response);
                        location.reload();
                    },
                    error: function() {
                        alert('操作失败，请稍后重试。');
                    }
                });
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>用户投诉管理</h1>
        <p>管理员：<?php echo htmlspecialchars($username); ?></p>
    </div>

    <div class="content">
        <h2>待处理投诉</h2>
        <table class="complaints-table">
            <thead>
                <tr>
                    <th>投诉 ID</th>
                    <th>商品名称</th>
                    <th>投诉用户</th>
                    <th>投诉原因</th>
                    <th>投诉状态</th>
                    <th>处理意见</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($complaint = mysqli_fetch_assoc($complaints)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($complaint['Comid']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['ProName']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['UserName']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['Reason']); ?></td>
                        <td>
                            <select id="status_<?php echo $complaint['Comid']; ?>">
                                <option value="Pending" <?php echo $complaint['ComStatus'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Resolved" <?php echo $complaint['ComStatus'] === 'Resolved' ? 'selected' : ''; ?>>resolved</option>
                                <option value="Reject" <?php echo $complaint['ComStatus'] === 'Reject' ? 'selected' : ''; ?>>reject</option>
                            </select>
                        </td>
                        <td>
                            <textarea id="review_<?php echo $complaint['Comid']; ?>"><?php echo htmlspecialchars($complaint['Review']); ?></textarea>
                        </td>
                        <td>
                            <button onclick="updateReview(<?php echo $complaint['Comid']; ?>)">提交反馈</button>
                            <button class="delete-btn" onclick="rejectComplaint(<?php echo $complaint['Comid']; ?>)">打回投诉</button>
                            <button onclick="resolveComplaint(<?php echo $complaint['Comid']; ?>, <?php echo $complaint['UserID']; ?>, '<?php echo $complaint['UserName']; ?>')">投诉已解决</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
