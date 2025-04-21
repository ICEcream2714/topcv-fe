<?php

// Hiển thị lỗi trong quá trình phát triển
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


try {
  $pdo = new PDO('mysql:dbname=job_db;host=mysql', 'user', '123456', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  echo 'Kết nối thành công!<br>';
} catch (PDOException $e) {
    echo 'Kết nối thất bại: ' . $e->getMessage();
    exit;
}

$query = $pdo->query('SHOW VARIABLES like "version"');
$row = $query->fetch();
echo 'MySQL version:' . $row['Value'];

echo '<h2>Kiểm tra các bảng trong database:</h2>';
$tables = $pdo->query('SHOW TABLES');
$tableCount = 0;

echo '<ul>';
while ($table = $tables->fetch(PDO::FETCH_NUM)) {
    echo '<li>' . $table[0] . '</li>';
    $tableCount++;
}
echo '</ul>';

if ($tableCount == 0) {
    echo '<p>Chưa có bảng nào được tạo. Schema chưa được thiết lập.</p>';
} else {
    echo '<p>Đã tìm thấy ' . $tableCount . ' bảng. Schema đã được thiết lập.</p>';
}