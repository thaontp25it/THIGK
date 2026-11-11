<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kết nối CSDL
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

$id_old = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit = $id_old > 0;

$idgv_val = '';
$fullname = '';
$image = '';
$sumClass = 0;
$error = '';

if ($isEdit) {
    $stmt = $conn->prepare("SELECT * FROM GiangVien WHERE IDGV = ?");
    $stmt->execute([$id_old]);
    $gv = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($gv) {
        $idgv_val = $gv['IDGV'];
        $fullname = $gv['Fullname'];
        $image = $gv['Image'];
        $sumClass = $gv['SumClass'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idgv_input = intval($_POST['IDGV']);
    $fullname = trim($_POST['Fullname']);
    $image = trim($_POST['Image']);
    $sumClass = intval($_POST['SumClass']);

    if (empty($idgv_input) || empty($fullname)) {
        $error = "Vui lòng nhập đầy đủ Mã GV và Họ tên!";
    } else {
        if ($isEdit) {
            // Nếu đổi sang Mã GV mới, kiểm tra xem Mã GV mới đó đã bị trùng với GV khác chưa
            if ($idgv_input != $id_old) {
                $checkStmt = $conn->prepare("SELECT IDGV FROM GiangVien WHERE IDGV = ?");
                $checkStmt->execute([$idgv_input]);
                if ($checkStmt->fetch()) {
                    $error = "Mã GV <strong>#$idgv_input</strong> đã tồn tại cho giảng viên khác! Vui lòng chọn mã khác.";
                }
            }

            if (empty($error)) {
                // Cập nhật cả IDGV mới vào CSDL
                $stmt = $conn->prepare("UPDATE GiangVien SET IDGV = ?, Fullname = ?, Image = ?, SumClass = ? WHERE IDGV = ?");
                $stmt->execute([$idgv_input, $fullname, $image, $sumClass, $id_old]);
                header("Location: index.php");
                exit();
            }
        } else {
            // Thêm mới giảng viên
            $checkStmt = $conn->prepare("SELECT IDGV FROM GiangVien WHERE IDGV = ?");
            $checkStmt->execute([$idgv_input]);
            if ($checkStmt->fetch()) {
                $error = "Mã GV <strong>#$idgv_input</strong> đã tồn tại! Vui lòng nhập mã khác.";
            } else {
                $stmt = $conn->prepare("INSERT INTO GiangVien (IDGV, Fullname, Image, SumClass) VALUES (?, ?, ?, ?)");
                $stmt->execute([$idgv_input, $fullname, $image, $sumClass]);
                header("Location: index.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Sửa Giảng Viên' : 'Thêm Giảng Viên' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .form-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); overflow: hidden; }
        .header-edit { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: #212529; }
        .header-add { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white; }
        .input-group-text { background-color: #f8f9fa; }
        .img-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 3px solid #de2b2b1a; display: block; margin: 0 auto 15px auto; }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width: 520px;">
        <div class="form-card bg-white">
            <div class="p-4 <?= $isEdit ? 'header-edit' : 'header-add' ?>">
                <h4 class="mb-1 fw-bold">
                    <i class="bi <?= $isEdit ? 'bi-pencil-square' : 'bi-person-plus-fill' ?> me-2"></i>
                    <?= $isEdit ? 'Cập Nhật Giảng Viên' : 'Thêm Giảng Viên Mới' ?>
                </h4>
                <p class="mb-0 opacity-75 fs-6">
                    <?= $isEdit ? 'Thay đổi thông tin hoặc Mã GV tùy chỉnh' : 'Nhập thông tin và Mã GV tùy chọn' ?>
                </p>
            </div>

            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 alert-dismissible fade show" role="alert">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Preview Avatar -->
                    <div class="text-center">
                        <img id="avatarPreview" 
                             src="<?= htmlspecialchars($image ?: 'https://via.placeholder.com/80?text=Avatar') ?>" 
                             alt="Preview" class="img-preview shadow-sm"
                             onerror="this.src='https://via.placeholder.com/80?text=Avatar'">
                    </div>

                    <!-- Ô CHO PHÉP NHẬP / CHỈNH SỬA MÃ GV -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã Giảng Viên (IDGV) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash"></i></span>
                            <input type="number" name="IDGV" class="form-control" value="<?= htmlspecialchars($idgv_val) ?>" required placeholder="Ví dụ: 1, 2, 101...">
                        </div>
                    </div>

                    <!-- Họ và tên -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ và Tên <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="Fullname" class="form-control" value="<?= htmlspecialchars($fullname) ?>" required placeholder="Nhập họ và tên...">
                        </div>
                    </div>

                    <!-- Link hình ảnh -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Đường dẫn Hình Ảnh (URL)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-image"></i></span>
                            <input type="text" id="imageInput" name="Image" class="form-control" value="<?= htmlspecialchars($image) ?>" placeholder="https://example.com/image.png">
                        </div>
                    </div>

                    <!-- Tổng số lớp -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tổng Số Lớp</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-book"></i></span>
                            <input type="number" name="SumClass" class="form-control" value="<?= $sumClass ?>" min="0">
                        </div>
                    </div>

                    <!-- Nút thao tác -->
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <a href="index.php" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn <?= $isEdit ? 'btn-warning text-dark fw-bold' : 'btn-success fw-bold' ?> px-4 shadow-sm">
                            <i class="bi <?= $isEdit ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"></i>
                            <?= $isEdit ? 'Lưu Thay Đổi' : 'Thêm Mới' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const imageInput = document.getElementById('imageInput');
        const avatarPreview = document.getElementById('avatarPreview');

        imageInput.addEventListener('input', function() {
            const url = this.value.trim();
            avatarPreview.src = url !== '' ? url : 'https://via.placeholder.com/80?text=Avatar';
        });
    </script>
</body>
</html>