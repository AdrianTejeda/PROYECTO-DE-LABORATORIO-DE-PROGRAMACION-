<?php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['uid']) || (($_SESSION['rol'] ?? '') !== 'admin')) { header('Location: ../login.php'); exit; }

// Helpers (fallback)
if (!function_exists('esc')) { function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('csrf_validate')) { function csrf_validate(){ return true; } }
if (!function_exists('csrf_input')) { function csrf_input(){ return ''; } }

// Subida de imagen a /imagenes/ con nombre único
function save_image($file) {
  if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return null;
  $name = 'p_' . time() . '_' . rand(1000,9999) . '.' . $ext;
  $dest = __DIR__ . '/../imagenes/' . $name;
  if (move_uploaded_file($file['tmp_name'], $dest)) return $name;
  return null;
}

// Crear / Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_validate') && csrf_validate() === false) {
    $_SESSION['flash_err'] = 'Token CSRF inválido. Recargá la página e intentá de nuevo.';
    header('Location: productos.php'); exit;
  }

  $id           = intval($_POST['id'] ?? 0);
  $categoria_id = intval($_POST['categoria_id'] ?? 0);
  $nombre       = trim($_POST['nombre'] ?? '');
  $descripcion  = trim($_POST['descripcion'] ?? '');
  $precio       = floatval($_POST['precio'] ?? 0);
  $descuento    = intval($_POST['descuento'] ?? 0);
  $activo       = isset($_POST['activo']) ? 1 : 0;
  $unidad       = trim($_POST['unidad'] ?? 'unidad');
  $imgNew       = save_image($_FILES['imagen'] ?? null);

  if ($id > 0) {
    // EDITAR
    if ($imgNew !== null) {
      // Traer imagen anterior
      $qOld = $conn->prepare('SELECT imagen FROM productos WHERE id=?');
      $qOld->bind_param('i', $id);
      $qOld->execute();
      $oldRow = $qOld->get_result()->fetch_assoc();
      $oldImg = $oldRow['imagen'] ?? null;

      $stmt = $conn->prepare("UPDATE productos
        SET categoria_id=?, nombre=?, descripcion=?, precio=?, descuento=?, imagen=?, activo=?, unidad=?
        WHERE id=?");
      if (!$stmt) { $_SESSION['flash_err'] = 'Error al preparar UPDATE con imagen: '.$conn->error; header('Location: productos.php'); exit; }
      $stmt->bind_param("issdisisi", $categoria_id, $nombre, $descripcion, $precio, $descuento, $imgNew, $activo, $unidad, $id);
      if (!$stmt->execute()) { $_SESSION['flash_err'] = 'Error al actualizar: '.$stmt->error; header('Location: productos.php'); exit; }

      // Borrar imagen vieja del disco
      if (!empty($oldImg)) {
        $oldPath = __DIR__ . '/../imagenes/' . $oldImg;
        if (is_file($oldPath)) { @unlink($oldPath); }
      }
    } else {
      $stmt = $conn->prepare("UPDATE productos
        SET categoria_id=?, nombre=?, descripcion=?, precio=?, descuento=?, activo=?, unidad=?
        WHERE id=?");
      if (!$stmt) { $_SESSION['flash_err'] = 'Error al preparar UPDATE: '.$conn->error; header('Location: productos.php'); exit; }
      $stmt->bind_param("issdiisi", $categoria_id, $nombre, $descripcion, $precio, $descuento, $activo, $unidad, $id);
      if (!$stmt->execute()) { $_SESSION['flash_err'] = 'Error al actualizar: '.$stmt->error; header('Location: productos.php'); exit; }
    }
    $_SESSION['flash_ok'] = 'Producto actualizado.';
    header('Location: productos.php'); exit;
  } else {
    // CREAR
    $stmt = $conn->prepare("INSERT INTO productos (categoria_id, nombre, descripcion, precio, descuento, imagen, activo, unidad)
      VALUES (?,?,?,?,?,?,?,?)");
    if (!$stmt) { $_SESSION['flash_err'] = 'Error al preparar INSERT: '.$conn->error; header('Location: productos.php'); exit; }
    $stmt->bind_param("issdisis", $categoria_id, $nombre, $descripcion, $precio, $descuento, $imgNew, $activo, $unidad);
    if (!$stmt->execute()) { $_SESSION['flash_err'] = 'Error al crear: '.$stmt->error; header('Location: productos.php'); exit; }
    $_SESSION['flash_ok'] = 'Producto creado.';
    header('Location: productos.php'); exit;
  }
}

// Borrar
if (isset($_GET['del'])) {
  $id = intval($_GET['del']);
  // Imagen del disco
  $qOld = $conn->prepare('SELECT imagen FROM productos WHERE id=?');
  $qOld->bind_param('i', $id);
  $qOld->execute();
  $oldRow = $qOld->get_result()->fetch_assoc();
  $oldImg = $oldRow['imagen'] ?? null;
  if (!empty($oldImg)) {
    $oldPath = __DIR__ . '/../imagenes/' . $oldImg;
    if (is_file($oldPath)) { @unlink($oldPath); }
  }
  $stmt = $conn->prepare('DELETE FROM productos WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $_SESSION['flash_ok'] = 'Producto eliminado.';
  header('Location: productos.php'); exit;
}

// Carga para editar y listados
$edit = null;
if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $res = $conn->prepare("SELECT * FROM productos WHERE id=?");
  $res->bind_param('i', $id);
  $res->execute();
  $edit = $res->get_result()->fetch_assoc();
}

$categorias = $conn->query("SELECT id, nombre FROM categorias WHERE activo=1 ORDER BY nombre ASC");
$rows = $conn->query("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id=p.categoria_id ORDER BY p.id DESC");
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Productos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Productos</h3>
    <div>
      <a class="btn btn-secondary" href="index.php">Panel</a>
      <a class="btn btn-danger" href="../logout.php">Salir</a>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <?php if (!empty($_SESSION['flash_err'])): ?>
        <div class="alert alert-danger"><?= esc($_SESSION['flash_err']); ?></div>
        <?php unset($_SESSION['flash_err']); ?>
      <?php endif; ?>
      <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success"><?= esc($_SESSION['flash_ok']); ?></div>
        <?php unset($_SESSION['flash_ok']); ?>
      <?php endif; ?>

      <form action="productos.php" method="post" enctype="multipart/form-data" class="row g-3">
        <?= function_exists('csrf_input') ? csrf_input() : '' ?>
        <input type="hidden" name="id" value="<?= isset($edit['id']) ? (int)$edit['id'] : 0 ?>">

        <div class="col-md-4">
          <label class="form-label">Nombre</label>
          <input required name="nombre" class="form-control" value="<?= esc($edit['nombre'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Categoría</label>
          <select name="categoria_id" class="form-select" required>
            <option value="">-- Elegir --</option>
            <?php while($c = $categorias->fetch_assoc()): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (isset($edit['categoria_id']) && (int)$edit['categoria_id']===(int)$c['id']) ? 'selected' : '' ?>>
                <?= esc($c['nombre']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Precio</label>
          <input type="number" step="0.01" name="precio" class="form-control" value="<?= esc($edit['precio'] ?? '') ?>">
        </div>

        <div class="col-md-2">
          <label class="form-label">Descuento %</label>
          <input type="number" min="0" max="90" name="descuento" class="form-control" value="<?= esc($edit['descuento'] ?? 0) ?>">
        </div>

        <div class="col-md-1 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
                   <?= (!isset($edit['activo']) || (int)$edit['activo'] === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3"><?= esc($edit['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="col-md-3">
          <label class="form-label">Unidad</label>
          <input name="unidad" class="form-control" placeholder="unidad, kg, caja, etc."
                 value="<?= esc($edit['unidad'] ?? 'unidad') ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Imagen (JPG/PNG/WEBP)</label>
          <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png,.webp">
          <?php if (!empty($edit['imagen'])): ?>
            <small class="text-muted">Actual: <?= esc($edit['imagen']) ?></small>
          <?php endif; ?>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <a href="index.php" class="btn btn-secondary">Volver al panel</a>
        </div>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>ID</th><th>Imagen</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Desc%</th><th>Activo</th><th>Unidad</th><th></th></tr></thead>
      <tbody>
      <?php while($r = $rows->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$r['id']; ?></td>
          <td><?php if(!empty($r['imagen'])): ?><img src="../imagenes/<?= esc($r['imagen']); ?>" style="height:50px"><?php endif; ?></td>
          <td><?= esc($r['nombre']); ?></td>
          <td><?= esc($r['categoria']); ?></td>
          <td>$<?= number_format((float)$r['precio'], 2, ',', '.'); ?></td>
          <td><?= (int)$r['descuento']; ?></td>
          <td><?= ((int)$r['activo'] === 1) ? 'Sí' : 'No'; ?></td>
          <td><?= esc($r['unidad'] ?? ''); ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="?id=<?= (int)$r['id']; ?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger" href="?del=<?= (int)$r['id']; ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
