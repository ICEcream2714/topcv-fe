<?php

// Hiển thị lỗi trong quá trình phát triển
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi tạo các biến thông báo
$errorMsg = '';
$successMsg = '';
$email = '';

try {
  $conn = new PDO('mysql:dbname=test_db;host=localhost:3307', 'root', 'root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  // echo 'Kết nối thành công!<br>';

  // Xử lý form đăng ký
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errorMsg = "Email không hợp lệ!";
    } else {
      // Kiểm tra email đã tồn tại trong database chưa
      $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
      $stmt->bindParam(':email', $email);
      $stmt->execute();
      $count = $stmt->fetchColumn();

      if ($count > 0) {
        $errorMsg = "Email đã được sử dụng, vui lòng chọn email khác!";
      } 
      // Kiểm tra mật khẩu và xác nhận mật khẩu
      elseif ($password !== $confirmPassword) {
        $errorMsg = "Mật khẩu và xác nhận mật khẩu không khớp!";
      }
      // Kiểm tra độ dài mật khẩu
      elseif (strlen($password) < 6) {
        $errorMsg = "Mật khẩu phải có ít nhất 6 ký tự!";
      } else {
        // Mã hóa mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
          // Thêm người dùng vào database
          $stmt = $conn->prepare("INSERT INTO users (email, password, created_at) VALUES (:email, :password, NOW())");
          $stmt->bindParam(':email', $email);
          $stmt->bindParam(':password', $hashedPassword);
          $stmt->execute();
          
          $successMsg = "Đăng ký thành công!";
          $email = ''; // Xóa email sau khi đăng ký thành công
        } catch (PDOException $e) {
          $errorMsg = "Lỗi khi đăng ký: " . $e->getMessage();
        }
      }
    }
  }
} catch (PDOException $e) {
    $errorMsg = 'Kết nối thất bại: ' . $e->getMessage();
    // exit;
}

// Kiểm tra nếu bảng users chưa tồn tại thì tạo mới
try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
} catch (PDOException $e) {
    $errorMsg = 'Lỗi tạo bảng users: ' . $e->getMessage();
}

// echo "Dong ket noi Database";
// $conn = null;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Đăng ký tài khoản</h2>
        
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-error"><?php echo $errorMsg; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success"><?php echo $successMsg; ?></div>
        <?php endif; ?>
        
        <form id="registerForm" method="post" action="" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <div id="emailError" class="error"></div>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
                <div id="passwordError" class="error"></div>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Xác nhận mật khẩu:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
                <div id="confirmPasswordError" class="error"></div>
            </div>
            <div class="form-group">
                <button type="submit" class="submit-btn">Đăng ký</button>
            </div>
        </form>
    </div>

    <script>
        function validateForm() {
            let isValid = true;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Reset error messages
            document.getElementById('emailError').textContent = '';
            document.getElementById('passwordError').textContent = '';
            document.getElementById('confirmPasswordError').textContent = '';
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Email không hợp lệ';
                isValid = false;
            }
            
            // Validate password
            if (password.length < 6) {
                document.getElementById('passwordError').textContent = 'Mật khẩu phải có ít nhất 6 ký tự';
                isValid = false;
            }
            
            // Validate confirm password
            if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Mật khẩu và xác nhận mật khẩu không khớp';
                isValid = false;
            }
            
            return isValid;
        }
    </script>
</body>
</html>

<?php 
// Đóng kết nối
$conn = null;
?>