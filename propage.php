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

// 检查用户是否为管理员或该商品发布者
$isAuthorized = ($role === 'Admin' || $userid === intval($product['UserID']));

// 收藏商品功能
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add_to_cart') {
        // 获取用户选择的数量
        $quantity = intval($_POST['quantity']);
        
        if ($quantity < 1) {
            $quantity = 1; // 如果数量小于1，设置为1
        }

        // 检查用户是否存在
        $query = "SELECT UserID FROM User WHERE UserID = '$userid'";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) == 0) {
            echo "<script>alert('用户不存在！');</script>";
            exit();
        }

        // 检查商品是否已在购物车中
        $query = "SELECT * FROM cart WHERE UserID = '$userid' AND ProID = '$productID'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            // 商品已在购物车中，更新数量
            $row = mysqli_fetch_assoc($result);
            $newQuantity = $row['Quantity'] + $quantity;
            $updateQuery = "UPDATE cart SET Quantity = $newQuantity WHERE UserID = '$userid' AND ProID = '$productID'";
            mysqli_query($con, $updateQuery);
            echo "<script>alert('商品数量已更新！');</script>";
        } else {
            // 商品不在购物车中，插入新的记录
            $query = "INSERT INTO cart (UserID, ProID, ProName, UserName, Quantity) 
                      VALUES ('$userid', '$productID', '{$product['ProName']}', '$username', '$quantity')";
            if (mysqli_query($con, $query)) {
                echo "<script>alert('商品已加入购物车！');</script>";
            } else {
                echo "<script>alert('无法加入购物车，请稍后再试！');</script>";
                echo "Error: " . mysqli_error($con);  // 显示错误信息
            }
        }
    } elseif ($action === 'remove_product') {
        // 下架商品逻辑
        if ($isAuthorized) {
            $updateQuery = "UPDATE spepro SET ProStatus = 'Inactive' WHERE ProID = '$productID'";
            if (mysqli_query($con, $updateQuery)) {
                echo "<script>alert('商品已成功下架！'); window.location.href = 'homepage.php';</script>";
                exit();
            } else {
                echo "<script>alert('下架失败，请稍后再试！');</script>";
                echo "Error: " . mysqli_error($con);
            }
        } else {
            echo "<script>alert('您无权下架此商品！');</script>";
        }
    } elseif ($action === 'add_to_likes') {
        // 收藏商品
        $query = "SELECT * FROM likes WHERE UserID = '$userid' AND ProID = '$productID'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) == 0) {
            // 商品不在收藏中，插入新的记录
            $query = "INSERT INTO likes (UserID, ProID) VALUES ('$userid', '$productID')";
            if (mysqli_query($con, $query)) {
                echo "<script>alert('商品已加入收藏！');</script>";
            } else {
                echo "<script>alert('无法加入收藏，请稍后再试！');</script>";
                echo "Error: " . mysqli_error($con);  // 显示错误信息
            }
        } else {
            echo "<script>alert('该商品已经在您的收藏夹中！');</script>";
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
                if (isset($owner['UserName'])) {
                    echo htmlspecialchars($owner['UserName']);
                } else {
                    echo htmlspecialchars('未知');
                }
            ?></p>
            <p>状态：<?php echo htmlspecialchars($product['ProStatus']); ?></p>
        </div>

        <div class="actions">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="add_to_cart">
                <label for="quantity">数量：</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1" required>
                <button type="submit">加入购物车</button>
            </form>

            <?php if ($isAuthorized) { ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="remove_product">
                <button type="submit" style="background-color: red;">下架商品</button>
            </form>
            <?php } ?>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="add_to_likes">
                <button type="submit" style="background-color: yellow;">收藏商品</button>
            </form>
        </div>
    </div>
</body>
</html>
