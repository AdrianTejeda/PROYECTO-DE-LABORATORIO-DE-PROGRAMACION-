// =========================
// CARRITO DE COMPRAS + WHATSAPP
// =========================

let carrito = [];

// Detectar los botones "Comprar"
document.addEventListener("DOMContentLoaded", () => {
  const botones = document.querySelectorAll(".boton");

  botones.forEach(boton => {
    boton.addEventListener("click", e => {
      e.preventDefault();

      const producto = boton.parentElement;
      const nombre = producto.querySelector("h3").innerText;
      const precio = producto.querySelector("p")
        ? producto.querySelector("p").innerText
        : "$0";

      carrito.push({ nombre, precio });
      actualizarCarrito();
    });
  });

  document.getElementById("enviar-wsp").addEventListener("click", enviarWhatsApp);
});

// Mostrar el carrito
function actualizarCarrito() {
  const lista = document.getElementById("carrito-lista");
  const total = document.getElementById("carrito-total");

  lista.innerHTML = "";
  let totalNum = 0;

  carrito.forEach((item, i) => {
    const precioNum = parseFloat(item.precio.replace(/[^0-9.]/g, "")) || 0;
    totalNum += precioNum;

    const div = document.createElement("div");
    div.innerHTML = `<p>${i + 1}. ${item.nombre} - ${item.precio}</p>`;
    lista.appendChild(div);
  });

  total.innerText = `Total estimado: $${totalNum}`;
}

// Enviar pedido por WhatsApp
function enviarWhatsApp() {
  if (carrito.length === 0) {
    alert("Tu carrito está vacío 🛒");
    return;
  }

  const numero = "5493865539227"; // Tu número de WhatsApp
  const mensaje = carrito
    .map((item, i) => `${i + 1}. ${item.nombre} - ${item.precio}`)
    .join("%0A");

  const textoFinal = `Hola! Quisiera hacer este pedido:%0A${mensaje}`;
  const url = `https://wa.me/${numero}?text=${textoFinal}`;

  window.open(url, "_blank");
}
