<?php
session_start();
include 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$userIDQuery = "SELECT UserID FROM User WHERE UserName='$username'";
$userResult = mysqli_query($con, $userIDQuery);
$user = mysqli_fetch_assoc($userResult);
$userID = $user['UserID'];

// 如果购物车 session 不存在，初始化购物车
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}



// Handle adding items to the cart
if (isset($_GET['add'])) {
    $proID = intval($_GET['add']);
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1; // 获取用户选择的数量，默认1
    
    // Fetch product details to get name and price
    $productQuery = "SELECT ProName, Price FROM spepro WHERE ProID = $proID";
    $productResult = mysqli_query($con, $productQuery);
    $product = mysqli_fetch_assoc($productResult);

    if ($product) {
        $proName = $product['ProName'];
        $price = isset($product['Price']) ? $product['Price'] : 0; // Default to 0 if price is missing

        // Check if the product is already in the cart
        $cartQuery = "SELECT * FROM cart WHERE UserID = $userID AND ProID = $proID";
        $cartResult = mysqli_query($con, $cartQuery);

        if (mysqli_num_rows($cartResult) > 0) {
            // Update quantity if product already exists in the cart
            $updateQuery = "UPDATE cart SET Quantity = Quantity + $quantity WHERE UserID = $userID AND ProID = $proID";
            mysqli_query($con, $updateQuery);
        } else {
            // Insert new product into the cart
            $insertQuery = "INSERT INTO cart (UserID, ProID, ProName, UserName, Quantity) 
                            VALUES ($userID, $proID, '$proName', '$username', $quantity)";
            mysqli_query($con, $insertQuery);
        }
    } else {
        echo "Product not found.";
    }
}

// Handle removing items from the cart
if (isset($_GET['remove'])) {
    $proID = intval($_GET['remove']);
    $deleteQuery = "DELETE FROM cart WHERE UserID = $userID AND ProID = $proID";
    mysqli_query($con, $deleteQuery);
}

// Fetch cart items for the user
$cartQuery = "SELECT * FROM cart WHERE UserID = $userID";
$cartItems = mysqli_query($con, $cartQuery);

// Calculate total price
$totalPrice = 0;
$cartData = [];
while ($item = mysqli_fetch_assoc($cartItems)) {
    // Fetch price of each item in the cart
    $productQuery = "SELECT Price FROM spepro WHERE ProID = " . $item['ProID'];
    $productResult = mysqli_query($con, $productQuery);
    $product = mysqli_fetch_assoc($productResult);
    
    // Ensure Price exists before trying to access it
    $price = isset($product['Price']) ? $product['Price'] : 0;
    $totalPrice += $price * $item['Quantity'];  // Calculate total price based on quantity
    
    $item['Price'] = $price; // Add price to item for later display
    $cartData[] = $item;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>购物车</title>
    <style>
        /* Styles for cart */
        body {
            font-family: Arial, sans-serif;
        }
        .cart-item {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
        .cart-item h3 {
            margin: 0;
        }
        .cart-item p {
            margin: 5px 0;
        }
        .cart-actions {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>我的购物车</h1>
    <a href="usercenter.php">返回用户中心</a>
    <?php if (empty($cartData)) { ?>
        <p>您的购物车是空的。</p>
    <?php } else { ?>
        <?php foreach ($cartData as $item) { ?>
    <div class="cart-item">
        <h3><?php echo htmlspecialchars($item['ProName']); ?></h3>
        <p>价格：￥<?php echo htmlspecialchars($item['Price']); ?></p>
        <p>数量：<?php echo htmlspecialchars($item['Quantity']); ?></p> <!-- 显示数量 -->
        <p><a href="cart.php?remove=<?php echo $item['ProID']; ?>">移除</a></p>
    </div>
<?php } ?>
        
        <div class="cart-actions">
            <p>总价：￥<?php echo $totalPrice; ?></p>
            <a href="checkout.php">去结算</a>
        </div>
    <?php } ?>
</body>
</html>
