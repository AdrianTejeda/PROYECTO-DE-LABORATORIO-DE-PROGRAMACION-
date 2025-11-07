<?php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "horizonte2025";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
// Si falla la conexión, cortamos la carga de la página con un mensaje.
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// Inicia la sesión solo si no está iniciada (login/carrito).
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Nombre del sitio y teléfono de WhatsApp para el formulario de presupuesto
if (!isset($SITE_NAME)) { $SITE_NAME = 'HORIZONTECONSTRUCCIONES'; }
// Formato: código de país + código de área + número (sin + ni espacios). Ej: 5492920123456
if (!isset($WSP_PHONE)) { $WSP_PHONE = '5493865539227'; } // TODO: reemplazar por el número real

// Escapa texto para salida HTML segura (evita XSS).
function esc($s) { return htmlspecialchars($s ?? "", ENT_QUOTES, "UTF-8"); }

// === CSRF helpers ===
function csrf_token() {
  if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}
function csrf_input() {
  return '<input type="hidden" name="csrf_token" value="'.esc(csrf_token()).'">';
}
function csrf_validate() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tok = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $tok)) {
      http_response_code(403);
      die('CSRF token inválido.');
    }
  }
}

?>