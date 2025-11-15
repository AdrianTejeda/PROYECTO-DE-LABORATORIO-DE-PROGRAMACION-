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
  <title>Productos - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      background-color: #f3f4f6;
    }
    .admin-shell {
      max-width: 1200px;
    }
    .admin-header-card {
      background: linear-gradient(135deg, #0d6efd, #6610f2);
      color: #fff;
      border-radius: 1rem;
    }
    .admin-header-card h3 {
      margin-bottom: .25rem;
    }
    .badge-estado {
      font-size: .8rem;
    }
    .thumb-img {
      height: 50px;
      width: 50px;
      object-fit: cover;
      border-radius: .5rem;
    }
  </style>
</head>
<body>
<div class="container py-4 admin-shell">

  <!-- Header / resumen -->
  <div class="admin-header-card shadow-sm p-3 p-md-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between js-fade-in">
    <div class="mb-3 mb-md-0">
      <h3 class="mb-1"><i class="bi bi-box-seam me-2"></i>Productos</h3>
      <p class="mb-0 opacity-75">
        Administrá los productos que se muestran en la tienda. Podés crear, editar, activar / desactivar y subir imágenes.
      </p>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light btn-sm js-hover" href="index.php">
        <i class="bi bi-speedometer2 me-1"></i> Panel
      </a>
      <a class="btn btn-outline-warning btn-sm js-hover" href="../logout.php">
        <i class="bi bi-box-arrow-right me-1"></i> Salir
      </a>
    </div>
  </div>

  <!-- Alertas -->
  <?php if (!empty($_SESSION['flash_err'])): ?>
    <div class="alert alert-danger shadow-sm js-fade-in">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= esc($_SESSION['flash_err']); ?>
    </div>
    <?php unset($_SESSION['flash_err']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success shadow-sm js-fade-in">
      <i class="bi bi-check-circle-fill me-2"></i><?= esc($_SESSION['flash_ok']); ?>
    </div>
    <?php unset($_SESSION['flash_ok']); ?>
  <?php endif; ?>

  <!-- Formulario en tarjeta -->
  <div class="card border-0 shadow-sm mb-4 js-fade-in">
    <div class="card-header bg-white border-0 pb-0">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">
            <?= isset($edit['id']) ? 'Editar producto' : 'Nuevo producto'; ?>
          </h5>
          <small class="text-muted">
            Completá los campos y guardá para reflejar los cambios en la tienda.
          </small>
        </div>
        <?php if (isset($edit['id'])): ?>
          <span class="badge text-bg-info">ID #<?= (int)$edit['id']; ?></span>
        <?php else: ?>
          <span class="badge text-bg-primary">Creación</span>
        <?php endif; ?>
      </div>
    </div>
    <div class="card-body">

      <form action="productos.php" method="post" enctype="multipart/form-data" class="row g-3">
        <?= function_exists('csrf_input') ? csrf_input() : '' ?>
        <input type="hidden" name="id" value="<?= isset($edit['id']) ? (int)$edit['id'] : 0 ?>">

        <div class="col-md-5">
          <label class="form-label">Nombre</label>
          <input required name="nombre" class="form-control" value="<?= esc($edit['nombre'] ?? '') ?>">
        </div>

        <div class="col-md-4">
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

        <div class="col-md-3">
          <label class="form-label">Unidad</label>
          <input name="unidad" class="form-control" placeholder="unidad, kg, caja, etc."
                 value="<?= esc($edit['unidad'] ?? 'unidad') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Precio</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" name="precio" class="form-control" value="<?= esc($edit['precio'] ?? '') ?>">
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Descuento %</label>
          <input type="number" min="0" max="90" name="descuento" class="form-control" value="<?= esc($edit['descuento'] ?? 0) ?>">
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
                   <?= (!isset($edit['activo']) || (int)$edit['activo'] === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">
              Activo en tienda
            </label>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Imagen (JPG/PNG/WEBP)</label>
          <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png,.webp">
          <?php if (!empty($edit['imagen'])): ?>
            <div class="d-flex align-items-center gap-2 mt-2">
              <small class="text-muted">Actual:</small>
              <img src="../imagenes/<?= esc($edit['imagen']); ?>" class="thumb-img border">
            </div>
          <?php endif; ?>
        </div>

        <div class="col-12">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3"><?= esc($edit['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="col-12 d-flex justify-content-between align-items-center mt-2">
          <div class="text-muted small">
            Los cambios se verán en la página pública inmediatamente después de guardar.
          </div>
          <div class="d-flex gap-2">
            <a href="productos.php" class="btn btn-outline-secondary btn-sm js-hover">Limpiar formulario</a>
            <button type="submit" class="btn btn-primary js-hover">
              <i class="bi bi-save me-1"></i> Guardar
            </button>
          </div>
        </div>
      </form>

    </div>
  </div>

  <!-- Listado -->
  <div class="card border-0 shadow-sm js-fade-in">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Listado de productos</h5>
      <span class="text-muted small">Total: <?= $rows->num_rows ?? 0; ?> registros</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th class="text-end">Precio</th>
              <th class="text-center">Desc%</th>
              <th class="text-center">Estado</th>
              <th>Unidad</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php while($r = $rows->fetch_assoc()): ?>
            <tr>
              <td><?= (int)$r['id']; ?></td>
              <td>
                <?php if(!empty($r['imagen'])): ?>
                  <img src="../imagenes/<?= esc($r['imagen']); ?>" class="thumb-img border">
                <?php else: ?>
                  <span class="text-muted small">Sin imagen</span>
                <?php endif; ?>
              </td>
              <td><?= esc($r['nombre']); ?></td>
              <td><?= esc($r['categoria']); ?></td>
              <td class="text-end">$<?= number_format((float)$r['precio'], 2, ',', '.'); ?></td>
              <td class="text-center"><?= (int)$r['descuento']; ?>%</td>
              <td class="text-center">
                <?php if ((int)$r['activo'] === 1): ?>
                  <span class="badge text-bg-success badge-estado">Activo</span>
                <?php else: ?>
                  <span class="badge text-bg-secondary badge-estado">Oculto</span>
                <?php endif; ?>
              </td>
              <td><?= esc($r['unidad'] ?? ''); ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary js-hover" href="?id=<?= (int)$r['id']; ?>" data-msg="Editando producto #<?= (int)$r['id']; ?>">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <a class="btn btn-sm btn-outline-danger js-hover"
                   href="?del=<?= (int)$r['id']; ?>"
                   onclick="return confirm('¿Eliminar?')"
                   data-msg="Producto eliminado">
                  <i class="bi bi-trash3"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script>
document.addEventListener 'DOMContentLoaded', function () {
  // Inyectar estilos para animaciones sin modificar tus CSS originales
  var style = document.createElement('style');
  style.textContent = `
    .js-fade-in {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity .6s ease, transform .6s ease;
    }
    .js-fade-in.js-visible {
      opacity: 1;
      transform: translateY(0);
    }
    .js-hover {
      transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
    }
    .js-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
    }
  `;
}
  document.head.appendChild(style);

  // Animación de entrada para tarjetas (no hay <section> acá)
  var fadeElements = document.querySelectorAll('.card, .admin-header-card, .alert');
  fadeElements.forEach function (el) {
    el.classList.add('js-fade-in');
  };

  // Hover suave en botones
  var hoverElements = document.querySelectorAll('.btn, a.btn, button');
  hoverElements.forEach function (el) {
    el.classList.add('js-hover');
  };

  // IntersectionObserver para activar la animación al entrar en viewport
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries, obs) {
      entries.forEach function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('js-visible');
          obs.unobserve(entry.target);
        }
      };
    }, { threshold: 0.1 });

    fadeElements.forEach (function) (el) {
      observer.observe(el);
    };
   else {
    // Fallback: mostrar todo directamente
    fadeElements.forEach(function) (el) {
      el.classList.add('js-visible');
    };
  }

  // Mensajes en botones del admin sin tocar la lógica PHP
  var isAdmin = window.location.pathname.indexOf('/admin/') !== -1;
  if (isAdmin) {
    document.body.addEventListener'click', function (e) {
      var btn = e.target.closest('button, a.btn');
      if (!btn) return;
      var msg = btn.getAttribute('data-msg');
      if (!msg || msg.trim() === '') {
        var txt = (btn.textContent || '').trim();
        if (txt) {
          msg = 'Acción: ' + txt + ' realizada ✅';
        } else {
          msg = 'Acción realizada ✅';
        }
      }
      if !btn.hasAttribute('data-no-alert') {
        alert(msg);
      }
    };
  }
};
</script>

</body>
</html>
