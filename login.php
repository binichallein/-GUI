<?php
// 引入数据库连接文件
include 'conn.php';


session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 判断是登录还是注册
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        // 登录逻辑
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);

        // 查询用户
        $query = "SELECT * FROM User WHERE UserName='$username' AND Password='$password'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) == 1) {
            // 登录成功，跳转到主页
            $user = mysqli_fetch_assoc($result);
            $_SESSION['username'] = $username;
            $_SESSION['userid'] =$user['UserID'];  // 用户ID
            $_SESSION['role'] =  $user['Role'];      // 用户角色
            header('Location: homepage.php');
            exit();
        } else {
            echo "<script>alert('用户名或密码错误！');</script>";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'register') {
        // 注册逻辑
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $email = mysqli_real_escape_string($con, $_POST['email']);

        // 检查用户名是否已存在
        $query = "SELECT * FROM User WHERE UserName='$username'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('用户名已存在，请选择其他用户名！');</script>";
        } else {
            // 插入新用户
            $query = "INSERT INTO User (UserName, Password, Email, Role, Status) VALUES ('$username', '$password', '$email', 'Customer', 'active')";
            if (mysqli_query($con, $query)) {
                echo "<script>alert('注册成功，请登录！');</script>";
            } else {
                echo "<script>alert('注册失败，请稍后再试！');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录与注册</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        form {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h1 {
            text-align: center;
        }
        label, input, button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
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
    <h1>用户登录</h1>
    <form method="POST" action="">
        <input type="hidden" name="action" value="login">
        <label for="username">用户名:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">密码:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">登录</button>
    </form>

    <h1>用户注册</h1>
    <form method="POST" action="">
        <input type="hidden" name="action" value="register">
        <label for="username">用户名:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">密码:</label>
        <input type="password" id="password" name="password" required>
        <label for="email">邮箱:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">注册</button>
    </form>
</body>
</html>
