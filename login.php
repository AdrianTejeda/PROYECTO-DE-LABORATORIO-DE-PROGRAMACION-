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
</body>
</html>
