<?php
// Bắt đầu session
session_start();

// Kiểm tra trạng thái đăng nhập, nếu chưa đăng nhập thì chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Lấy thông tin người dùng từ session
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Hiển thị lỗi trong quá trình phát triển
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối CSDL
try {
  $conn = new PDO('mysql:dbname=test_db;host=localhost:3307', 'root', 'root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

  // Lấy danh sách người dùng
  $stmt = $conn->prepare("SELECT id, email, created_at FROM users ORDER BY id");
  $stmt->execute();
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorMsg = 'Kết nối thất bại: ' . $e->getMessage();
}

// Xử lý đăng xuất
if (isset($_POST['logout'])) {
  // Xóa tất cả dữ liệu session
  session_unset();
  session_destroy();

  // Chuyển hướng về trang đăng nhập
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trang chủ</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
    }

    .dashboard-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #f9f9f9;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .welcome {
      font-size: 18px;
    }

    .logout-btn {
      background-color: #dc3545;
      color: white;
      padding: 8px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .logout-btn:hover {
      background-color: #c82333;
    }

    .content {
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      background-color: white;
    }

    /* CSS cho bảng hiển thị danh sách người dùng */
    .users-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    .users-table th,
    .users-table td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    .users-table th {
      background-color: #f2f2f2;
      color: #333;
      font-weight: bold;
    }

    .users-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .users-table tr:hover {
      background-color: #f1f1f1;
    }

    .user-count {
      margin-bottom: 10px;
      font-weight: bold;
    }

    .current-user {
      background-color: #e6f7ff !important;
    }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <div class="header">
      <div class="welcome">Xin chào, <?php echo htmlspecialchars($user_email); ?>!</div>
      <form method="post">
        <button type="submit" name="logout" class="logout-btn">Đăng xuất</button>
      </form>
    </div>
    <div class="content">
      <h2>Bảng điều khiển</h2>
      <p>Bạn đã đăng nhập thành công.</p>

      <!-- Hiển thị danh sách người dùng -->
      <h3>Danh sách người dùng</h3>

      <?php if (isset($errorMsg)): ?>
        <div style="color: red;"><?php echo $errorMsg; ?></div>
      <?php else: ?>
        <div class="user-count">Tổng số người dùng: <?php echo count($users); ?></div>
        <table class="users-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Ngày đăng ký</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>

<?php
// Đóng kết nối
if (isset($conn)) {
  $conn = null;
}
?>