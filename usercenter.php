<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$userIDQuery = "SELECT UserID FROM User WHERE UserName='$username'";
$userResult = mysqli_query($con, $userIDQuery);
$user = mysqli_fetch_assoc($userResult);
$userID = $user['UserID'];

// 处理发布商品
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'publish') {
    $proName = mysqli_real_escape_string($con, $_POST['proName']);
    $catID = intval($_POST['catID']);
    $proInfo = mysqli_real_escape_string($con, $_POST['proInfo']);
    $origin = mysqli_real_escape_string($con, $_POST['origin']);
    $price = floatval($_POST['price']);
    $storage = intval($_POST['storage']);
    $proStatus = 'active'; // 默认商品状态

    $query = "INSERT INTO spepro (ProName, CatID, UserID, ProInfo, Origin, Price, Storage, ProStatus) 
              VALUES ('$proName', $catID, $userID, '$proInfo', '$origin', $price, $storage, '$proStatus')";
    
    if (mysqli_query($con, $query)) {
        echo "<script>alert('商品发布成功！');</script>";
    } else {
        echo "<script>alert('发布商品失败，请稍后再试！');</script>";
    }
}

// 获取用户发布的商品
$query = "SELECT * FROM spepro WHERE UserID = $userID";
$products = mysqli_query($con, $query);

// 获取所有类别
$categories = mysqli_query($con, "SELECT * FROM procat");
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户中心</title>
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
        .user-actions {
            padding: 20px;
        }
        .user-actions form {
            margin-bottom: 20px;
        }
        .user-actions form input, .user-actions form select {
            margin: 10px 0;
            padding: 5px;
        }
        .product-list {
            padding: 20px;
        }
        .product-list .product {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>欢迎来到用户中心</h1>
        <p>用户：<?php echo htmlspecialchars($username); ?></p>
    </div>

    <div class="user-actions">
        <h2>发布新商品</h2>
        <form method="POST" action="usercenter.php">
            <input type="hidden" name="action" value="publish">
            <label for="proName">商品名称:</label>
            <input type="text" id="proName" name="proName" required>

            <label for="catID">商品类别:</label>
            <select id="catID" name="catID" required>
                <?php while ($row = mysqli_fetch_assoc($categories)) { ?>
                    <option value="<?php echo $row['CatID']; ?>"><?php echo $row['ProCat']; ?></option>
                <?php } ?>
            </select>

            <label for="proInfo">商品描述:</label>
            <textarea id="proInfo" name="proInfo" required></textarea>

            <label for="origin">商品产地:</label>
            <input type="text" id="origin" name="origin" required>

            <label for="price">商品价格:</label>
            <input type="number" id="price" name="price" required>

            <label for="storage">商品库存:</label>
            <input type="number" id="storage" name="storage" required>

            <button type="submit">发布商品</button>
        </form>

        <h2><a href="cart.php">查看购物车</a></h2>
        <h2><a href="order.php">查看我的订单</a></h2>  <!-- Added order page link -->
    </div>

    <div class="product-list">
        <h2>我的商品</h2>
        <?php while ($product = mysqli_fetch_assoc($products)) { ?>
            <div class="product">
                <h3><?php echo htmlspecialchars($product['ProName']); ?></h3>
                <p>价格：<?php echo htmlspecialchars($product['Price']); ?></p>
                <p>库存：<?php echo htmlspecialchars($product['Storage']); ?></p>
                <a href="editproduct.php?proid=<?php echo $product['ProID']; ?>">编辑商品</a>
                <a href="deleteproduct.php?proid=<?php echo $product['ProID']; ?>">下架商品</a>
            </div>
        <?php } ?>
    </div>
</body>
</html>
