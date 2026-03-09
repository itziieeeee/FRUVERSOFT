// Seleccionamos los elementos del DOM
const modal = document.getElementById("myModal");
const btn = document.getElementById("openModal");
const closeBtn = document.querySelector(".close-btn");

// 1. Abrir el modal al hacer clic en el botón
btn.onclick = function() {
  modal.style.display = "block";
}

// 2. Cerrar el modal al hacer clic en la (x)
closeBtn.onclick = function() {
  modal.style.display = "none";
}

// 3. Cerrar el modal si el usuario hace clic fuera de la caja blanca
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}