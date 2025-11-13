<?php /* 
==============================================================
 Archivo: partials/navbar.php
 Contiene el menú superior del sitio.
==============================================================
*/ ?>
<?php require_once __DIR__ . '/../config.php'; ?>
<nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm">
  <div class="container">

  <a class="navbar-brand d-flex align-items-center" href="index.php">
  <img src="imagenes/logo.png" alt="HORIZONTECONSTRUCCIONES" height="40" class="me-2">
  <span class="fw-bold text-dark" >HORIZONTE CONSTRUCCIONES</span>




   </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php#inicio">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#productos">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#servicios">Servicios</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#presupuesto">Presupuesto</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#contacto">Contacto</a></li>
        <li class="nav-item"><a class="nav-link" href="carrito.php"><i class="bi bi-cart"></i> Carrito <?php $cnt = 0; if (!empty($_SESSION['cart'])) { $cnt = array_sum($_SESSION['cart']); } echo "<span class=\"badge text-bg-danger ms-1\">".$cnt."</span>"; ?></a></li>
        <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-person"></i> Login</a></li>
      </ul>
    </div>
  </div>
</nav>
