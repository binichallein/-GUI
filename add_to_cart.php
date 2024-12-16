<?php
// 引入数据库连接文件
include 'conn.php';

// 开启会话，获取登录用户信息
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'User'; // 默认值为普通用户
$userid = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;

// 获取商品 ID
if (!isset($_GET['proid'])) {
    echo "<script>alert('未指定商品 ID！'); window.location.href = 'homepage.php';</script>";
    exit();
}

$productID = intval($_GET['proid']);

// 获取商品信息
$query = "SELECT * FROM spepro WHERE ProID = '$productID'";
$result = mysqli_query($con, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('商品不存在！'); window.location.href = 'homepage.php';</script>";
    exit();
}
$product = mysqli_fetch_assoc($result);

// 处理用户选择的数量并加入购物车
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_to_cart') {
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1; // 获取用户选择的数量，默认值为 1

        if ($quantity < 1) {
            echo "<script>alert('请输入有效的数量！');</script>";
            exit();
        }

        // 检查用户是否存在
        $query = "SELECT UserID FROM User WHERE UserID = '$userid'";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) == 0) {
            echo "<script>alert('用户不存在！');</script>";
            exit();
        }

        // 插入购物车
        $query = "INSERT INTO cart (UserID, ProID, ProName, UserName, Quantity) 
                  VALUES ('$userid', '$productID', '{$product['ProName']}', '$username', '$quantity')";
        if (mysqli_query($con, $query)) {
            echo "<script>alert('商品已加入购物车！');</script>";
        } else {
            echo "<script>alert('无法加入购物车，请稍后再试！');</script>";
            echo "Error: " . mysqli_error($con); // 输出错误信息
        }
    }
}

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品详情页</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }
        h1, h2 {
            text-align: center;
        }
        .product {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        // 确认弹窗逻辑
        console.log("JavaScript 已加载");
        function confirmAddToCart() {
            const quantityInput = document.getElementById('quantity');
            const quantity = quantityInput.value;

            if (quantity <= 0) {
                alert("请输入有效的数量！");
                return false;
            }

            // 弹窗确认
            const confirmed = confirm(`您确定要购买 ${quantity} 件商品吗？`);
            return confirmed; // 如果用户点击“确定”，返回 true，否则返回 false
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>商品详情</h1>
        <div class="product">
            <h2><?php echo htmlspecialchars($product['ProName']); ?></h2>
            <p>价格：<?php echo htmlspecialchars($product['Price']); ?> 元</p>
            <p>库存：<?php echo htmlspecialchars($product['Storage']); ?></p>
            <p>描述：<?php echo htmlspecialchars($product['ProInfo']); ?></p>
            <p>发布者：<?php
                $query = "SELECT UserName FROM User WHERE UserID = '{$product['UserID']}'";
                $result = mysqli_query($con, $query);
                $owner = $result ? mysqli_fetch_assoc($result) : null;
                echo htmlspecialchars($owner['UserName'] ?? '未知');
            ?></p>
            <p>状态：<?php echo htmlspecialchars($product['ProStatus']); ?></p>
        </div>

        <div class="actions">
            <form method="POST" onsubmit="return confirmAddToCart();" style="display:inline;">
                <input type="hidden" name="action" value="add_to_cart">
                <label for="quantity">数量：</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['Storage']; ?>" required>
                <button type="submit">加入购物车</button>
            </form>
        </div>
    </div>
</body>
</html>
