<?php
// 引入数据库连接文件
include 'conn.php';

// 获取商品信息
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$proID = isset($_GET['proid']) ? intval($_GET['proid']) : 0;
if ($proID <= 0) {
    echo "无效的商品ID";
    exit();
}

// 获取商品详情
$query = "SELECT * FROM spepro WHERE ProID = $proID";
$product = mysqli_query($con, $query);
if (!$product) {
    echo "商品不存在！";
    exit();
}
$product = mysqli_fetch_assoc($product);

// 获取商品类别列表
$categoryQuery = "SELECT * FROM procat";
$categories = mysqli_query($con, $categoryQuery);
if (!$categories) {
    echo "无法获取商品类别！";
    exit();
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $proName = mysqli_real_escape_string($con, $_POST['proName']);
    $catID = intval($_POST['catID']);
    $proInfo = mysqli_real_escape_string($con, $_POST['proInfo']);
    $origin = mysqli_real_escape_string($con, $_POST['origin']);
    $price = floatval($_POST['price']);
    $storage = intval($_POST['storage']);

    $updateQuery = "UPDATE spepro SET ProName='$proName', CatID=$catID, ProInfo='$proInfo', Origin='$origin', Price=$price, Storage=$storage WHERE ProID = $proID";
    
    if (mysqli_query($con, $updateQuery)) {
        echo "<script>alert('商品信息已更新！'); window.location.href='usercenter.php';</script>";
    } else {
        echo "<script>alert('更新失败，请稍后再试！');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑商品</title>
</head>
<body>
    <h1>编辑商品</h1>
    <form method="POST" action="">
        <label for="proName">商品名称:</label>
        <input type="text" id="proName" name="proName" value="<?php echo htmlspecialchars($product['ProName']); ?>" required>

        <label for="catID">商品类别:</label>
        <select id="catID" name="catID" required>
            <?php while ($category = mysqli_fetch_assoc($categories)) { ?>
                <option value="<?php echo $category['CatID']; ?>" <?php if ($product['CatID'] == $category['CatID']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($category['ProCat']); ?>
                </option>
            <?php } ?>
        </select>

        <label for="proInfo">商品描述:</label>
        <textarea id="proInfo" name="proInfo" required><?php echo htmlspecialchars($product['ProInfo']); ?></textarea>

        <label for="origin">商品产地:</label>
        <input type="text" id="origin" name="origin" value="<?php echo htmlspecialchars($product['Origin']); ?>" required>

        <label for="price">商品价格:</label>
        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['Price']); ?>" required>

        <label for="storage">商品库存:</label>
        <input type="number" id="storage" name="storage" value="<?php echo htmlspecialchars($product['Storage']); ?>" required>

        <button type="submit">更新商品</button>
    </form>
</body>
</html>
