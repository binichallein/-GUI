<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$proID = isset($_GET['proid']) ? intval($_GET['proid']) : 0;
if ($proID > 0) {
    $query = "DELETE FROM spepro WHERE ProID = $proID";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('商品已下架！'); window.location.href='usercenter.php';</script>";
    } else {
        echo "<script>alert('下架失败，请稍后再试！'); window.location.href='usercenter.php';</script>";
    }
} else {
    echo "<script>alert('无效的商品ID！'); window.location.href='usercenter.php';</script>";
}
?>
