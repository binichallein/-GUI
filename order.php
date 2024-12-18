<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$userIDQuery = "SELECT UserID FROM User WHERE UserName='$username'";
$userResult = mysqli_query($con, $userIDQuery);
$user = mysqli_fetch_assoc($userResult);
$userID = $user['UserID'];

// Fetch orders for the user from the Dingdan table
$orderQuery = "SELECT d.OrderID, d.ProID, d.Quantity, d.OrderTime, d.OrderStatus, p.ProName, p.Price 
               FROM Dingdan d 
               JOIN spepro p ON d.ProID = p.ProID 
               WHERE d.UserID = $userID";
$orderItems = mysqli_query($con, $orderQuery);
if (!$orderItems) {
    die("Error executing query: " . mysqli_error($con));  // 输出错误信息
}

// Handle "received" button click (update order status to "已收到货")
if (isset($_POST['received_order_id'])) {
    $orderID = intval($_POST['received_order_id']);
    $updateQuery = "UPDATE Dingdan SET OrderStatus = '已收到货' WHERE OrderID = $orderID AND UserID = $userID";
    mysqli_query($con, $updateQuery);
    header("Location: order.php"); // Reload page after update
    exit();
}

// Handle "complaint" button click (insert complaint into Complaint table)
if (isset($_POST['complain_order_id'])) {
    $orderID = intval($_POST['complain_order_id']);
    $reason = mysqli_real_escape_string($con, $_POST['complaint_reason']);
    $proIDQuery = "SELECT p.ProID, p.ProName FROM Dingdan d 
               JOIN spepro p ON d.ProID = p.ProID 
               WHERE d.OrderID = $orderID";
    $proResult = mysqli_query($con, $proIDQuery);
    if (!$proResult) {
        die("Error executing query: " . mysqli_error($con));  // 输出错误信息
    }
    $product = mysqli_fetch_assoc($proResult);
    
    $insertComplaintQuery = "INSERT INTO Complaint (UserName, ProName, Reason, ComStatus, UserID, ProID, Review) 
                             VALUES ('$username', '" . $product['ProName'] . "', '$reason', '待处理', $userID, '" . $product['ProID'] . "', '')";
   if (mysqli_query($con, $insertComplaintQuery)) {
    // If the query is successful
    header("Location: order.php"); // Reload page after complaint submission
    exit();
} else {
    // If there is an error, show it
    echo "Error: " . mysqli_error($con);
}
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的订单</title>
</head>
<body>
    <h1>我的订单</h1>
    <a href="usercenter.php">返回用户中心</a>
    <?php if (mysqli_num_rows($orderItems) == 0) { ?>
        <p>您还没有订单。</p>
    <?php } else { ?>
        <table border="1">
            <thead>
                <tr>
                    <th>订单编号</th>
                    <th>商品名称</th>
                    <th>数量</th>
                    <th>价格</th>
                    <th>订单时间</th>
                    <th>订单状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($order = mysqli_fetch_assoc($orderItems)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($order['OrderID']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['ProName']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['Quantity']) . "</td>";
                    echo "<td>￥" . htmlspecialchars($order['Price']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['OrderTime']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['OrderStatus']) . "</td>";
                    echo "<td>";

                    // "已收到货" button
                    if ($order['OrderStatus'] !== '已收到货') {
                        echo "<form method='POST' style='display:inline;'>
                                <button type='submit' name='received_order_id' value='" . $order['OrderID'] . "'>已收到货</button>
                              </form>";
                    }

                    // "投诉" button
                    echo "<form method='POST' action='' style='display:inline;'>
                            <button type='submit' name='complain_order_id' value='" . $order['OrderID'] . "'>投诉</button>
                            <input type='text' name='complaint_reason' placeholder='请输入投诉理由' required />
                          </form>";

                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php } ?>
</body>
</html>
