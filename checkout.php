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

// Fetch cart items for the user from the database
$cartQuery = "SELECT c.ProID, c.ProName, c.Quantity, p.Price, p.Storage 
              FROM cart c 
              JOIN spepro p ON c.ProID = p.ProID 
              WHERE c.UserID = $userID";
$cartItems = mysqli_query($con, $cartQuery);

// Check if the cart is empty
if (mysqli_num_rows($cartItems) == 0) {
    echo "<p>购物车为空，请先添加商品。</p>";
    exit();
}

// Proceed with checkout logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $totalPrice = 0;
    $allItemsAvailable = true;

    // First, validate stock availability
    while ($item = mysqli_fetch_assoc($cartItems)) {
        if ($item['Quantity'] > $item['Storage']) {
            echo "<p>商品 " . htmlspecialchars($item['ProName']) . " 库存不足，请调整购买数量。</p>";
            $allItemsAvailable = false;
            break;
        }
    }

    // If all items are available, proceed
    if ($allItemsAvailable) {
        mysqli_data_seek($cartItems, 0); // Reset result pointer to the beginning

        while ($item = mysqli_fetch_assoc($cartItems)) {
            $totalPrice += $item['Price'] * $item['Quantity'];

            // Insert each item into the Dingdan table
            $orderTime = date("Y-m-d H:i:s");
            $orderStatus = "未完成"; // Example: Set initial order status to "待付款"
            $insertOrderQuery = "INSERT INTO Dingdan (ProID, UserID, OrderTime, OrderStatus, Quantity) 
                                 VALUES (" . $item['ProID'] . ", $userID, '$orderTime', '$orderStatus', " . $item['Quantity'] . ")";
            mysqli_query($con, $insertOrderQuery);

            // Deduct stock from the spepro table
            $newStorage = $item['Storage'] - $item['Quantity'];
            $updateStockQuery = "UPDATE spepro SET Storage = $newStorage WHERE ProID = " . $item['ProID'];
            mysqli_query($con, $updateStockQuery);
        }

        // Clear the cart after successful order
        $deleteCartQuery = "DELETE FROM cart WHERE UserID = $userID";
        mysqli_query($con, $deleteCartQuery);

        echo "<p>结算成功，感谢购买！</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>结算</title>
</head>
<body>
    <h1>结算</h1>
    <p>请确认您的订单信息并提交结算。</p>

    <ul>
        <?php
        // Reset cart query to fetch items again after posting the order
        $cartItems = mysqli_query($con, $cartQuery);
        $totalPrice = 0;
        while ($item = mysqli_fetch_assoc($cartItems)) {
            $totalPrice += $item['Price'] * $item['Quantity'];
            echo "<li>" . htmlspecialchars($item['ProName']) . " - ￥" . htmlspecialchars($item['Price']) . " x " . htmlspecialchars($item['Quantity']) . "</li>";
        }
        ?>
    </ul>

    <p>总价：￥<?php echo $totalPrice; ?></p>

    <form method="POST" action="checkout.php">
        <button type="submit">确认结算</button>
    </form>
</body>
</html>
