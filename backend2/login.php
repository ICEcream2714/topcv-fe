<?php
// Bắt đầu session
session_start();

// Hiển thị lỗi trong quá trình phát triển
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi tạo các biến thông báo
$errorMsg = '';
$successMsg = '';
$email = '';

// Nếu người dùng đã đăng nhập, chuyển hướng đến trang dashboard
if (isset($_SESSION['user_id'])) {
  // Bạn có thể thay đổi URL này thành trang dashboard
  header('Location: dashboard.php');
  exit;
}

try {
  $conn = new PDO('mysql:dbname=test_db;host=localhost:3307', 'root', 'root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

  // Xử lý form đăng nhập
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Kiểm tra email và mật khẩu có trống không
    if (empty($email) || empty($password)) {
      $errorMsg = "Vui lòng nhập đầy đủ email và mật khẩu!";
    } else {
      // Kiểm tra email có hợp lệ không
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Email không hợp lệ!";
      } else {
        // Kiểm tra thông tin đăng nhập
        $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Nếu tìm thấy người dùng và mật khẩu khớp
        if ($user && password_verify($password, $user['password'])) {
          // Đăng nhập thành công, lưu thông tin vào session
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['user_email'] = $user['email'];

          // Chuyển hướng đến trang dashboard
          $successMsg = "Đăng nhập thành công! Đang chuyển hướng...";
          echo '<meta http-equiv="refresh" content="0;url=dashboard.php">';
        } else {
          // Đăng nhập thất bại
          $errorMsg = "Email hoặc mật khẩu không chính xác!";
        }
      }
    }
  }
} catch (PDOException $e) {
  $errorMsg = 'Kết nối thất bại: ' . $e->getMessage();
}

// Kiểm tra bảng users đã tồn tại chưa
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

?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập</title>
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
      width: 100%;
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

    .links {
      margin-top: 15px;
      text-align: center;
    }

    .links a {
      color: #007bff;
      text-decoration: none;
    }

    .links a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="form-container">
    <h2>Đăng nhập</h2>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-error"><?php echo $errorMsg; ?></div>
    <?php endif; ?>

    <?php if (!empty($successMsg)): ?>
      <div class="alert alert-success"><?php echo $successMsg; ?></div>
    <?php endif; ?>

    <form id="loginForm" method="post" action="" onsubmit="return validateForm()">
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
        <button type="submit" class="submit-btn">Đăng nhập</button>
      </div>
    </form>
    <div class="links">
      <p>Chưa có tài khoản? <a href="index.php">Đăng ký ngay</a></p>
    </div>
  </div>

  <script>
    function validateForm() {
      let isValid = true;
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;

      // Reset error messages
      document.getElementById('emailError').textContent = '';
      document.getElementById('passwordError').textContent = '';

      // Validate email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        document.getElementById('emailError').textContent = 'Email không hợp lệ';
        isValid = false;
      }

      // Validate password
      if (password.length < 1) {
        document.getElementById('passwordError').textContent = 'Vui lòng nhập mật khẩu';
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