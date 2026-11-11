<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Kết nối Cơ sở dữ liệu MySQL
$host = 'localhost';
$dbname = 'QLHocPhan';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// 2. Xử lý Xóa giảng viên (Yêu cầu 4)[cite: 1]
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM GiangVien WHERE IDGV = ?");
    $stmt->execute([$id]);
    header("Location: index.php");
    exit();
}

// 3. Lấy danh sách giảng viên (Yêu cầu 2)[cite: 1]
$stmt = $conn->query("SELECT * FROM GiangVien ORDER BY IDGV ASC");
$giangViens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Giảng Viên</title>
    <!-- Bootstrap 5 CDN & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .header-bg {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 20px 25px;
        }
        .avatar-img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e9ecef;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-action {
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width: 950px;">
        <div class="main-card bg-white">
            <!-- Header Trang -->
            <div class="header-bg d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1 fw-bold"><i class="bi bi-person-badge-fill me-2"></i>Danh Sách Giảng Viên</h3>
                    <p class="mb-0 text-white-50 opacity-75 fs-6">Hệ thống quản lý học phần - Bảng Giảng Viên</p>
                </div>
                <a href="form.php" class="btn btn-light text-primary fw-semibold px-3 py-2 shadow-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> Thêm Giảng Viên
                </a>
            </div>

            <!-- Bảng Dữ Liệu -->
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Tổng số: <strong><?= count($giangViens) ?></strong> giảng viên</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border-top">
                        <thead class="table-light">
                            <tr class="text-secondary text-uppercase fs-7">
                                <th style="width: 110px;">Mã GV</th>
                                <th style="width: 90px;">Hình ảnh</th>
                                <th>Họ và Tên</th>
                                <th style="width: 140px;" class="text-center">Tổng số lớp</th>
                                <th style="width: 160px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($giangViens) > 0): ?>
                                <?php foreach ($giangViens as $gv): ?>
                                    <tr>
                                        <td>
                                            <!-- Mã GV với tiền tố GV -->
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 fs-6">
                                                GV<?= sprintf("%03d", $gv['IDGV']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <img src="<?= htmlspecialchars($gv['Image'] ?: 'https://via.placeholder.com/60?text=GV') ?>" 
                                                 alt="Avatar" class="avatar-img">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($gv['Fullname']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                                                <?= $gv['SumClass'] ?> lớp
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="form.php?id=<?= $gv['IDGV'] ?>" class="btn btn-outline-warning btn-action me-1">
                                                <i class="bi bi-pencil-square"></i> Sửa
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-action" 
                                                    onclick="confirmDelete(<?= $gv['IDGV'] ?>, '<?= htmlspecialchars($gv['Fullname'], ENT_QUOTES) ?>')">
                                                <i class="bi bi-trash"></i> Xóa
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Chưa có dữ liệu giảng viên.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL XÁC NHẬN XÓA -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Xác nhận xóa?</h5>
                    <p class="text-muted mb-4" id="deleteMessage">Bạn có chắc chắn muốn xóa giảng viên này?</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 fw-semibold" data-bs-dismiss="modal">Hủy bỏ</button>
                        <a id="btnConfirmDelete" href="#" class="btn btn-danger px-4 fw-semibold">Xóa ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id, name) {
            document.getElementById('deleteMessage').innerHTML = 'Bạn có chắc chắn muốn xóa giảng viên <strong>' + name + '</strong> không?';
            document.getElementById('btnConfirmDelete').href = 'index.php?action=delete&id=' + id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>