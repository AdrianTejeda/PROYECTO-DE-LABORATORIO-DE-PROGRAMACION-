<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$err  = '';
$user = '';
$row  = null;

if (!function_exists('esc')) {
  function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_validate')) {
    try { csrf_validate(); } catch(Throwable $e) { $err = 'Token inválido, recargá la página.'; }
  }

  $user = trim($_POST['usuario'] ?? '');
  $pass = trim($_POST['password'] ?? '');

  // CAPTCHA (igual que Magic Pizzería)
  $captcha_input = strtoupper(trim($_POST['captcha'] ?? ''));
  $captcha_ok = isset($_SESSION['captcha']) && $captcha_input === ($_SESSION['captcha'] ?? '');
  unset($_SESSION['captcha']); // invalidar para evitar reuso

  if (!$captcha_ok && empty($err)) {
    $err = 'Código CAPTCHA incorrecto. Intentá de nuevo.';
  }

  if (empty($err)) {
    $stmt = $conn->prepare("SELECT id, usuario, password, rol FROM usuarios WHERE usuario=? AND activo=1 LIMIT 1");
    if (!$stmt) {
      $err = 'Error interno.';
    } else {
      $stmt->bind_param("s", $user);
      $stmt->execute();
      $res = $stmt->get_result();
      $row = $res ? $res->fetch_assoc() : null;

      if ($row) {
        $stored = (string)($row['password'] ?? '');
        $ok = false;

        if (preg_match('/^\$2[aby]\$/', $stored)) {
          // hash bcrypt
          $ok = password_verify($pass, $stored);
        } else {
          // soporte legado: texto plano -> migrar si coincide
          if (hash_equals($stored, (string)$pass)) {
            $ok = true;
            $newHash = password_hash($pass, PASSWORD_DEFAULT);
            if ($newHash) {
              $up = $conn->prepare('UPDATE usuarios SET password=? WHERE id=?');
              if ($up) { $up->bind_param('si', $newHash, $row['id']); $up->execute(); }
            }
          }
        }

        if ($ok) {
          session_regenerate_id(true);
          $_SESSION['uid']     = (int)$row['id'];
          $_SESSION['usuario'] = (string)$row['usuario'];
          $_SESSION['rol']     = $row['rol'] ?? 'user';
          header('Location: admin/'); exit;
        } else {
          $err = 'Usuario o clave inválidos';
        }
      } else {
        $err = 'Usuario o clave inválidos';
      }
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | HORIZONTECONSTRUCCIONES</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-sm-10 col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h3 class="mb-3">Login administrador</h3>
            <?php if(!empty($err)): ?>
              <div class="alert alert-danger"><?= esc($err) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off" novalidate>
              <?= function_exists('csrf_input') ? csrf_input() : '' ?>
              <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="usuario" class="form-control" required value="<?= esc($user) ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Comprobá que sos humano</label>
                <div class="d-flex align-items-center gap-2 mb-2">
                  <img src="captcha.php?rand=<?= time() ?>" alt="CAPTCHA" height="40">
                  <a href="#" onclick="this.previousElementSibling.src='captcha.php?rand='+Date.now();return false;" class="small">Recargar</a>
                </div>
                <input type="text" name="captcha" class="form-control" placeholder="Ingresá el código de la imagen" required>
              </div>
              <button class="btn btn-dark w-100">Ingresar</button>
            </form>
            <a class="d-block text-center mt-3" href="index.php">← Volver al sitio</a>
          </div>
        </div>
      </div>
    </div>
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
