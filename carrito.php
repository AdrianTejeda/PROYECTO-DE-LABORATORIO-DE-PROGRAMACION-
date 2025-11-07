<?php /* 
==============================================================
 Archivo: carrito.php
 Maneja el carrito, totales y envío del pedido vía WhatsApp.
==============================================================
*/ ?>
<?php
require_once __DIR__ . '/config.php';

/**
 * Carrito en $_SESSION['cart']:
 *   [ product_id => cantidad, ... ]
 * Editá imágenes en /imagenes; los nombres deben coincidir con productos.imagen
 */

// Asegura la variable de carrito
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Helpers
function cart_count() { return array_sum($_SESSION['cart'] ?? []); }
function cart_add($id, $qty=1) { $id=max(0,intval($id)); $qty=max(1,intval($qty)); if($id>0){ $_SESSION['cart'][$id]=($_SESSION['cart'][$id]??0)+$qty; } }
function cart_set($id, $qty) { $id=max(0,intval($id)); $qty=max(0,intval($qty)); if($id>0){ if($qty==0) unset($_SESSION['cart'][$id]); else $_SESSION['cart'][$id]=$qty; } }
function cart_remove($id) { $id=max(0,intval($id)); unset($_SESSION['cart'][$id]); }

// Acciones por GET
if (isset($_GET['add'])) { cart_add($_GET['add'], isset($_GET['q']) ? $_GET['q'] : 1); header('Location: carrito.php'); exit; }
if (isset($_GET['del'])) { cart_remove($_GET['del']); header('Location: carrito.php'); exit; }
if (isset($_GET['clear'])) { $_SESSION['cart'] = []; header('Location: carrito.php'); exit; }

// Actualizar cantidades por POST
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['qty']) && is_array($_POST['qty'])) {
  foreach($_POST['qty'] as $pid => $qty) { cart_set($pid, $qty); }
  header('Location: carrito.php'); exit;
}

// Obtener productos del carrito desde la BD
$items = []; $total = 0;
if (!empty($_SESSION['cart'])) {
  $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
  $sql = "SELECT id, nombre, precio, descuento, imagen FROM productos WHERE id IN ($ids)";
  $res = $conn->query($sql);
  while($row = $res->fetch_assoc()) {
    $row['qty'] = $_SESSION['cart'][$row['id']] ?? 0;
    $precio = floatval($row['precio']);
    $desc = intval($row['descuento']);
    if ($desc>0) $precio = $precio * (1 - $desc/100);
    $row['precio_final'] = $precio;
    $row['subtotal'] = $row['precio_final'] * $row['qty'];
    $total += $row['subtotal'];
    $items[] = $row;
  }
}

// Preparar mensaje de WhatsApp
$wa_phone = '5493865539227'; // Número WhatsApp 
$wa_lines = [];
$wa_lines[] = "¡Hola! Quiero hacer este pedido:"; // Encabezado del mensaje
foreach($items as $it){
  $wa_lines[] = "- {$it['nombre']} x{$it['qty']} = $" . number_format($it['subtotal'],2,',','.');
}
$wa_lines[] = "Total: $" . number_format($total,2,',','.');
$wa_text = implode("%0A", array_map('rawurlencode', $wa_lines)); // %0A = salto de línea en URL
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Carrito | HORIZONTECONSTRUCCIONES</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Carrito</h3>
    <div>
      <a href="index.php#menu" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
      <?php if(!empty($items)): ?>
        <a href="carrito.php?clear=1" class="btn btn-outline-danger" onclick="return confirm('¿Vaciar carrito?')"><i class="bi bi-trash"></i> Vaciar</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if(empty($items)): ?>
    <div class="alert alert-info">Tu carrito está vacío. Agregá productos desde el <a href="index.php#menu">menú</a>.</div>
  <?php else: ?>
  <form method="post">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th></th><th>Producto</th><th class="text-end">Precio</th>
            <th style="width:140px">Cantidad</th><th class="text-end">Subtotal</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($items as $it): 
          $img = $it['imagen'] ? 'imagenes/'.esc($it['imagen']) : 'imagenes/placeholder.jpg';
        ?>
          <tr>
            <td><img src="<?php echo $img; ?>" style="height:56px"></td>
            <td><?php echo esc($it['nombre']); ?><?php if($it['descuento']>0): ?> <span class="badge badge-sale ms-2">-<?php echo (int)$it['descuento']; ?>%</span><?php endif; ?></td>
            <td class="text-end">$<?php echo number_format($it['precio_final'],2,',','.'); ?></td>
            <td>
              <input type="number" name="qty[<?php echo $it['id']; ?>]" min="0" class="form-control" value="<?php echo $it['qty']; ?>">
            </td>
            <td class="text-end">$<?php echo number_format($it['subtotal'],2,',','.'); ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-danger" href="carrito.php?del=<?php echo $it['id']; ?>"><i class="bi bi-x-lg"></i></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">Total</th>
            <th class="text-end">$<?php echo number_format($total,2,',','.'); ?></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="d-flex justify-content-end gap-2">
      <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-repeat"></i> Actualizar</button>
      <a class="btn btn-success" target="_blank" href="https://wa.me/<?php echo $wa_phone; ?>?text=<?php echo $wa_text; ?>"><i class="bi bi-whatsapp"></i> Enviar por WhatsApp</a>
    </div>
  </form>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
