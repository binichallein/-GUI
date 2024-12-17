<?php
// 引入数据库连接文件
include 'conn.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$userid = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;

// 获取用户收藏的商品
$query = "SELECT spepro.ProID, spepro.ProName, spepro.Price, spepro.Storage 
          FROM likes 
          JOIN spepro ON likes.ProID = spepro.ProID 
          WHERE likes.UserID = '$userid'";
$results = mysqli_query($con, $query);

// 检查查询是否成功
if (!$results) {
    die("数据库查询失败：" . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的收藏</title>
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
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .product-item {
            border: 1px solid #ccc;
            padding: 20px;
            width: 200px;
            border-radius: 5px;
            text-align: center;
        }
        .product-item h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>我的收藏</h1>
        <div class="product-list">
            <?php 
            // 检查结果是否为空
            if (mysqli_num_rows($results) > 0) {
                while ($product = mysqli_fetch_assoc($results)) { ?>
                    <div class="product-item">
                        <h3>
                            <a href="propage.php?proid=<?php echo $product['ProID']; ?>">
                                <?php echo htmlspecialchars($product['ProName']); ?>
                            </a>
                        </h3>
                        <p>价格：<?php echo htmlspecialchars($product['Price']); ?> 元</p>
                        <p>库存：<?php echo htmlspecialchars($product['Storage']); ?></p>
                    </div>
                <?php }
            } else { ?>
                <p>暂无收藏的商品。</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
