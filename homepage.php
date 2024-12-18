<?php
// 引入数据库连接文件
include 'conn.php';
?>
<?php
// 获取用户信息
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// 获取商品类别
$query = "SELECT * FROM procat";
$categories = mysqli_query($con, $query);
if (!$categories) {
    echo "Error fetching categories: " . mysqli_error($con);
    exit();
}

// 获取第一个类别下的商品
$selectedCategory = isset($_GET['cat']) ? intval($_GET['cat']) : 1;
$query = "SELECT * FROM spepro WHERE CatID = '$selectedCategory' AND ProStatus not in ('Frozen','Inactive')
";
$products = mysqli_query($con, $query);
if (!$products) {
    echo "Error fetching products: " . mysqli_error($con);
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品主页</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
        }
        .categories {
            display: flex;
            justify-content: center;
            padding: 10px;
            background-color: #f4f4f4;
        }
        .categories a {
            margin: 0 10px;
            text-decoration: none;
            color: #333;
        }
        .categories a:hover {
            text-decoration: underline;
        }
        .products {
            display: flex;
            flex-wrap: wrap;
            padding: 20px;
            gap: 20px;
        }
        .product {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            width: 200px;
            text-align: center;
        }
        .product a {
            text-decoration: none;
            color: #4CAF50;
        }
        .product a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>特色时令商品集市平台</h1>
    <a href="login.php">返回登录界面</a>
    <div>
        <p>欢迎，<a href="usercenter.php"><?php echo htmlspecialchars($username); ?></a>!</p>
    </div>
</div>

    <div class="categories">
        <?php while ($row = mysqli_fetch_assoc($categories)) { ?>
            <a href="homepage.php?cat=<?php echo $row['CatID']; ?>">
                <?php echo htmlspecialchars($row['ProCat']); ?>
            </a>
        <?php } ?>
    </div>

    <div class="products">
        <?php while ($row = mysqli_fetch_assoc($products)) { ?>
            <div class="product">
                <h2><?php echo htmlspecialchars($row['ProName']); ?></h2>
                <p>价格：<?php echo htmlspecialchars($row['Price']); ?></p>
                <a href="propage.php?proid=<?php echo $row['ProID']; ?>">查看详情</a>
            </div>
        <?php } ?>
    </div>
</body>
</html>
