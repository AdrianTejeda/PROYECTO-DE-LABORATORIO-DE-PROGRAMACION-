<?php /* 
==============================================================
 Archivo: admin/index.php
 Panel de administración de Magic Pizzeria.
==============================================================
*/ ?>
<?php require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['uid']) || $_SESSION['rol'] !== 'admin') { header('Location: ../login.php'); exit; }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PANEL | HORIZONTE CONSTRUCCION</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">PANEL ADMINISTRADOR - HORIZONTE CONSTRUCCION</a>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="productos.php">Productos</a>
      <a class="btn btn-outline-light btn-sm" href="categorias.php">Categorías</a>
      <a class="btn btn-danger btn-sm" href="../logout.php">Salir</a>
    </div>
  </div>
</nav>
<div class="container py-4">
  <h3>Bienvenido, <?php echo esc($_SESSION['usuario']); ?> 👋</h3>
  <p class="text-secondary">Usá el menú para administrar el contenido.</p>
</div>
</body>
</html>
