<?php
/*
==============================================================
 Archivo: admin/categorias.php
 CRUD de categorías (mismo estilo que productos).
==============================================================
*/
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['uid']) || (($_SESSION['rol'] ?? '') !== 'admin')) { header('Location: ../login.php'); exit; }

// Helpers (fallback)
if (!function_exists('esc')) { function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('csrf_validate')) { function csrf_validate(){ return true; } }
if (!function_exists('csrf_input')) { function csrf_input(){ return ''; } }

// Crear / Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_validate') && csrf_validate() === false) {
    $_SESSION['flash_err'] = 'Token CSRF inválido. Recargá la página e intentá de nuevo.';
    header('Location: categorias.php'); exit;
  }

  $id          = intval($_POST['id'] ?? 0);
  $nombre      = trim($_POST['nombre'] ?? '');
  $descripcion = trim($_POST['descripcion'] ?? '');
  $activo      = isset($_POST['activo']) ? 1 : 0;

  if ($nombre === '') {
    $_SESSION['flash_err'] = 'Completá el nombre de la categoría.';
    header('Location: categorias.php' . ($id>0 ? '?id='.$id : '') ); exit;
  }

  if ($id > 0) {
    $stmt = $conn->prepare("UPDATE categorias SET nombre=?, descripcion=?, activo=? WHERE id=?");
    if (!$stmt) { $_SESSION['flash_err'] = 'Error al preparar UPDATE: '.$conn->error; header('Location: categorias.php'); exit; }
    $stmt->bind_param("ssii", $nombre, $descripcion, $activo, $id);
    if (!$stmt->execute()) { $_SESSION['flash_err'] = 'Error al actualizar: '.$stmt->error; header('Location: categorias.php'); exit; }
    $_SESSION['flash_ok'] = 'Categoría actualizada.';
    header('Location: categorias.php'); exit;
  } else {
    $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, activo) VALUES (?,?,?)");
    if (!$stmt) { $_SESSION['flash_err'] = 'Error al preparar INSERT: '.$conn->error; header('Location: categorias.php'); exit; }
    $stmt->bind_param("ssi", $nombre, $descripcion, $activo);
    if (!$stmt->execute()) { $_SESSION['flash_err'] = 'Error al crear: '.$stmt->error; header('Location: categorias.php'); exit; }
    $_SESSION['flash_ok'] = 'Categoría creada.';
    header('Location: categorias.php'); exit;
  }
}

// Borrar
if (isset($_GET['del'])) {
  $id = intval($_GET['del']);
  $stmt = $conn->prepare('DELETE FROM categorias WHERE id=?');
  if ($stmt) { $stmt->bind_param('i', $id); $stmt->execute(); }
  $_SESSION['flash_ok'] = 'Categoría eliminada.';
  header('Location: categorias.php'); exit;
}

// Cargar datos para edición y listado
$edit = null;
if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $res = $conn->prepare("SELECT * FROM categorias WHERE id=?");
  $res->bind_param('i', $id);
  $res->execute();
  $getres = $res->get_result();
  $edit = $getres ? $getres->fetch_assoc() : null;
}

$rows = $conn->query("SELECT * FROM categorias ORDER BY id DESC");
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Categorías</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Categorías</h3>
    <div>
      <a class="btn btn-secondary" href="index.php">Panel</a>
      <a class="btn btn-danger" href="../logout.php">Salir</a>
    </div>
  </div>

  <?php if (!empty($_SESSION['flash_err'])): ?>
    <div class="alert alert-danger"><?= esc($_SESSION['flash_err']); ?></div>
    <?php unset($_SESSION['flash_err']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success"><?= esc($_SESSION['flash_ok']); ?></div>
    <?php unset($_SESSION['flash_ok']); ?>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <form method="post" class="row g-3" action="categorias.php">
        <?= function_exists('csrf_input') ? csrf_input() : '' ?>
        <input type="hidden" name="id" value="<?= isset($edit['id']) ? (int)$edit['id'] : 0 ?>">

        <div class="col-md-4">
          <label class="form-label">Nombre</label>
          <input required name="nombre" class="form-control" value="<?= esc($edit['nombre'] ?? '') ?>">
        </div>

        <div class="col-md-7">
          <label class="form-label">Descripción</label>
          <input name="descripcion" class="form-control" value="<?= esc($edit['descripcion'] ?? '') ?>">
        </div>

        <div class="col-md-1 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
              <?= (!isset($edit['activo']) || (int)$edit['activo'] === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Activo</th><th></th></tr></thead>
      <tbody>
      <?php if ($rows): while($r_row = $rows->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$r_row['id']; ?></td>
          <td><?= esc($r_row['nombre']); ?></td>
          <td><?= esc($r_row['descripcion']); ?></td>
          <td><?= ((int)$r_row['activo'] === 1) ? 'Sí' : 'No'; ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="?id=<?= (int)$r_row['id']; ?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger" href="?del=<?= (int)$r_row['id']; ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
          </td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
