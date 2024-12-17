<?php
include 'conn.php';

session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $query = "SELECT * FROM User WHERE UserName='$username' AND Password='$password'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['username'] = $username;
            $_SESSION['userid'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            header('Location: homepage.php');
            exit();
        } else {
            echo "<script>alert('用户名或密码错误！');</script>";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'register') {
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $query = "SELECT * FROM User WHERE UserName='$username'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('用户名已存在，请选择其他用户名！');</script>";
        } else {
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
        #registerModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            width: 400px;
        }
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

            /* 关闭按钮样式 */
    #closeModalBtn {
        padding: 10px;
        background-color: #f44336; /* 红色背景 */
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
    }

    #closeModalBtn:hover {
        background-color: #d32f2f; /* 更深的红色，用于悬停效果 */
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
        <button type="button" id="showRegisterBtn">注册</button>
    </form>

    <!-- 注册弹出框 -->
    <div id="overlay"></div>
    <div id="registerModal">
        <h2>用户注册</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="register">
            <label for="reg_username">用户名:</label>
            <input type="text" id="reg_username" name="username" required>
            <label for="reg_password">密码:</label>
            <input type="password" id="reg_password" name="password" required>
            <label for="reg_email">邮箱:</label>
            <input type="email" id="reg_email" name="email" required>
            <button type="submit">注册</button>
            <button type="button" id="closeModalBtn">关闭</button>
        </form>
    </div>

    <script>
        const registerModal = document.getElementById('registerModal');
        const overlay = document.getElementById('overlay');
        const showRegisterBtn = document.getElementById('showRegisterBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');

        showRegisterBtn.addEventListener('click', () => {
            registerModal.style.display = 'block';
            overlay.style.display = 'block';
        });

        closeModalBtn.addEventListener('click', () => {
            registerModal.style.display = 'none';
            overlay.style.display = 'none';
        });

        overlay.addEventListener('click', () => {
            registerModal.style.display = 'none';
            overlay.style.display = 'none';
        });
    </script>
</body>
</html>
