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

<script>
document.addEventListener('DOMContentLoaded', function () {
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
  document.head.appendChild(style);

  // Animación de entrada para secciones y tarjetas
  var fadeElements = document.querySelectorAll('section, .card, .card.h-100');
  fadeElements.forEach(function (el) {
    el.classList.add('js-fade-in');
  });

  // Hover suave en botones y links del navbar
  var hoverElements = document.querySelectorAll('.btn, .navbar .nav-link, a.btn, button');
  hoverElements.forEach(function (el) {
    el.classList.add('js-hover');
  });

  // IntersectionObserver para activar la animación al entrar en viewport
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('js-visible');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    fadeElements.forEach(function (el) {
      observer.observe(el);
    });
  } else {
    // Fallback: mostrar todo directamente
    fadeElements.forEach(function (el) {
      el.classList.add('js-visible');
    });
  }

  // Mensajes en botones del admin sin tocar la lógica PHP
  var isAdmin = window.location.pathname.indexOf('/admin/') !== -1;
  if (isAdmin) {
    document.body.addEventListener('click', function (e) {
      var btn = e.target.closest('button, a.btn');
      if (!btn) return;
      // Si el botón ya tiene un onclick, lo respetamos; solo mostramos mensaje aparte
      var msg = btn.getAttribute('data-msg');
      if (!msg || msg.trim() === '') {
        var txt = (btn.textContent || '').trim();
        if (txt) {
          msg = 'Acción: ' + txt + ' realizada ✅';
        } else {
          msg = 'Acción realizada ✅';
        }
      }
      // Evitamos estorbar confirm() propios del botón
      if (!btn.hasAttribute('data-no-alert')) {
        alert(msg);
      }
    });
  }
});
</script>

</body>
</html>
