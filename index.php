<?php /* 
==============================================================
 Archivo: index.php (Home)
 Muestra carrusel + menú destacado leyendo productos de la BD.
==============================================================
*/ ?>
<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HORIZONTECONSTRUCCIONES</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<header id="inicio" class="position-relative">
  <!-- HERO CAROUSEL -->
  <div id="hero" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#hero" data-bs-slide-to="0" class="active" aria-current="true" aria-label="1"></button>
      <button type="button" data-bs-target="#hero" data-bs-slide-to="1" aria-label="2"></button>
      <button type="button" data-bs-target="#hero" data-bs-slide-to="2" aria-label="3"></button>
    </div>

    <div class="carousel-inner">
      <div class="carousel-item active" data-bs-interval="5000">
        <img class="d-block w-100" src="imagenes/carrusel1.jpg" alt="Slide 1">
      </div>
      <div class="carousel-item" data-bs-interval="5000">
        <img class="d-block w-100" src="imagenes/carrusel2.jpg" alt="Slide 2">
      </div>
      <div class="carousel-item" data-bs-interval="5000">
        <img class="d-block w-100" src="imagenes/carrusel3.jpg" alt="Slide 3">
      </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#hero" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#hero" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Siguiente</span>
    </button>
  </div>
  <!-- /HERO CAROUSEL -->
</header>

<!-- Productos se carga desde la base de datos -->
<section id="productos" class="py-5 bg-light">
  <div class="container">
    <h2 class="mb-3">Menú destacado</h2>
    <div class="row g-4 mt-1">
      <?php
        // Consulta los últimos 8 productos activos y la categoría asociada.
        $q = $conn->query("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 ORDER BY p.id DESC LIMIT 8");
        while($r = $q->fetch_assoc()):
          // Si no hay imagen de producto, usa /imagenes/placeholder.jpg
          $img = $r['imagen'] ? 'imagenes/'.esc($r['imagen']) : 'imagenes/placeholder.jpg';
      ?>
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100 shadow-sm">
            <img class="object-fit-cover w-100" style="height:180px" src="<?php echo $img; ?>" alt="<?php echo esc($r['nombre']); ?>">
            <div class="card-body">
              <span class="badge badge-sale mb-2">-<?php echo (int)$r['descuento']; ?>%</span>
              <h5 class="card-title"><?php echo esc($r['nombre']); ?></h5>
              <p class="card-text text-secondary"><?php echo esc($r['descripcion']); ?></p>
              <div class="d-flex align-items-center justify-content-between">
                <span class="fw-bold">$<?php echo number_format($r['precio'],0,',','.'); ?> / <?php echo esc($r['unidad'] ?? 'unidad'); ?></span>
                <a class="btn btn-danger" href="carrito.php?add=<?php echo (int)$r['id']; ?>"><i class="bi bi-cart-plus"></i> Agregar</a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>


<!-- =================== SERVICIOS =================== -->
<section id="servicios" class="py-5 bg-light">
  <div class="container">
    <h2 class="mb-4">Servicios de construcción</h2>
    <p class="text-secondary mb-5">Obras y soluciones integrales para hogares, comercios y obras civiles.</p>
    <div class="row g-4">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Obra gruesa y estructuras</h5>
            <p class="card-text">Cimientos, losas, mampostería, ampliaciones y refuerzos estructurales.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Remodelaciones y terminaciones</h5>
            <p class="card-text">Reformas de cocinas y baños, revoques, durlock, revestimientos y pisos.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Instalaciones</h5>
            <p class="card-text">Electricidad, plomería y gas con matrícula. Mantenimiento y puestas a punto.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Pintura y revestimientos</h5>
            <p class="card-text">Pintura interior/exterior, impermeabilización y tratamiento de humedades.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Obra liviana</h5>
            <p class="card-text">Cerramientos, decks, pérgolas, techos livianos y trabajos en madera/metal.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Asesoría y dirección de obra</h5>
            <p class="card-text">Cálculo de materiales, presupuestos, cronogramas y conducción técnica.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =================== PRESUPUESTO (FORM WHATSAPP) =================== -->
<section id="presupuesto" class="py-5 bg-light">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xl-8 col-lg-9">
        <div class="card shadow-lg border-0">
          <div class="card-body p-4 p-md-5">

            <div class="d-flex align-items-center justify-content-between mb-4">
              <div>
                <h2 class="mb-1">Pedí tu presupuesto</h2>
                <p class="text-secondary mb-0">
                  Completá el formulario y te contactamos por WhatsApp para coordinar tu obra.
                </p>
   
            <form id="formPresupuesto" class="row g-3 needs-validation" novalidate>
              <div class="col-md-6">
                <label class="form-label">Nombre y apellido</label>
                <input type="text" name="nombre" class="form-control" required>
                <div class="invalid-feedback">Ingresá tu nombre.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Teléfono (WhatsApp)</label>
                <input type="tel" name="telefono" class="form-control" required>
                <div class="invalid-feedback">Ingresá tu teléfono.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
              </div>

              <div class="col-md-6">
                <label class="form-label">Tipo de trabajo</label>
                <select name="tipo" class="form-select" required>
                  <option value="" selected disabled>Elegí una opción</option>
                  <option>Obra nueva</option>
                  <option>Remodelación</option>
                  <option>Instalaciones (luz/agua/gas)</option>
                  <option>Pintura</option>
                  <option>Otro</option>
                </select>
                <div class="invalid-feedback">Seleccioná un tipo de trabajo.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Descripción del trabajo</label>
                <textarea name="detalle" class="form-control" rows="4"
                          placeholder="Medidas, materiales, plazos, dirección, etc." required></textarea>
                <div class="invalid-feedback">Contanos un poco más del trabajo.</div>
              </div>

              <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-success btn-lg px-4" type="submit">
                  <i class="bi bi-whatsapp me-2"></i> Enviar por WhatsApp
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Bootstrap validation + WhatsApp redirect
(function() {
  const form = document.getElementById('formPresupuesto');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    // Validación
    if (!form.checkValidity()) { form.classList.add('was-validated'); return; }
    const fd = new FormData(form);
    const data = Object.fromEntries(fd.entries());
    const linea = [
      "*Nuevo pedido de presupuesto*",
      "Nombre: " + (data.nombre||""),
      "Tel: " + (data.telefono||""),
      "Email: " + (data.email||""),
      "Tipo: " + (data.tipo||""),
      "Detalle: " + (data.detalle||"")
    ].join("\n");
    const phone = (window.WSP_PHONE || "%WSP_PHONE%");
    const url = "https://wa.me/" + phone + "?text=" + encodeURIComponent(linea);
    window.open(url, "_blank");
  });
  // Exponer número desde PHP
  window.WSP_PHONE = "<?php echo isset($WSP_PHONE) ? $WSP_PHONE : ''; ?>";
})();
</script>

<!-- 👇 Se eliminó la sección #contacto, ahora el contacto está en el footer -->

<footer id="footer" class="py-4 bg-dark text-white-50 mt-5">
  <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center">
    <div>© <?php echo date('Y'); ?> HORIZONTECONSTRUCCIONES</div>
    <div><a href="#presupuesto" class="link-light text-decoration-none">Pedí tu presupuesto</a></div>
  </div>

  <!-- Redes sociales en el footer -->
  <div class="container mt-4">
    <div class="row g-3">
      <div class="col-md-4">
        <a class="btn btn-wsp w-100 d-flex justify-content-between align-items-center"
           href="https://wa.me/5493865539227" target="_blank" rel="noopener">
          <span><i class="bi bi-whatsapp me-2"></i> WhatsApp</span>
          <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <div class="col-md-4">
        <a class="btn btn-insta w-100 d-flex justify-content-between align-items-center"
           href="https://www.instagram.com" target="_blank" rel="noopener">
          <span><i class="bi bi-instagram me-2"></i> Instagram</span>
          <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <div class="col-md-4">
        <a class="btn btn-fb w-100 d-flex justify-content-between align-items-center"
           href="https://www.facebook.com" target="_blank" rel="noopener">
          <span><i class="bi bi-facebook me-2"></i> Facebook</span>
          <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const el = document.querySelector('#hero');
    if (el) {
      new bootstrap.Carousel(el, {
        interval: 5000,   // 5s por slide
        ride: 'carousel', // arranca solo
        pause: false,     // no se pausa al pasar el mouse
        touch: true,
        wrap: true
      });
    }
  });
</script>

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
      var msg = btn.getAttribute('data-msg');
      if (!msg || msg.trim() === '') {
        var txt = (btn.textContent || '').trim();
        if (txt) {
          msg = 'Acción: ' + txt + ' realizada ✅';
        } else {
          msg = 'Acción realizada ✅';
        }
      }
      if (!btn.hasAttribute('data-no-alert')) {
        alert(msg);
      }
    });
  }
});
</script>

<!-- 🔥 Script específico para SERVICIOS (colores + emojis + hover extra) -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  // Estilos específicos solo para las cards de SERVICIOS
  var st = document.createElement("style");
  st.textContent = `
    #servicios .card {
      transition: .25s;
      border-radius: 1rem;
    }
    #servicios .card:hover {
      transform: translateY(-6px) scale(1.02);
      box-shadow: 0 1rem 2rem rgba(0,0,0,.18);
    }
  `;
  document.head.appendChild(st);

  // Colores + Emojis automáticos para cada servicio (en orden)
  const data = [
    {emoji:"🏗️", color:"#fb8c00"}, // Obra gruesa
    {emoji:"🛠️", color:"#43a047"}, // Remodelaciones
    {emoji:"💡", color:"#1e88e5"}, // Instalaciones
    {emoji:"🎨", color:"#8e24aa"}, // Pintura
    {emoji:"🧱", color:"#ffa000"}, // Obra liviana
    {emoji:"📐", color:"#00897b"}  // Asesoría
  ];

  document.querySelectorAll("#servicios .card .card-title").forEach((titulo, i) => {
    if (!data[i]) return;

    // Agrego emoji solo si no lo tiene ya
    if (!titulo.textContent.includes(data[i].emoji)) {
      titulo.textContent = data[i].emoji + " " + titulo.textContent.trim();
    }

    // Colorear la card
    let card = titulo.closest(".card");
    card.style.borderTop = "5px solid " + data[i].color;
    card.style.background = data[i].color + "1A"; // 1A = 10% transparencia
  });
});
</script>

</body>
</html>
