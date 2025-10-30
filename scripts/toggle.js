// Obtiene el contenedor con el id 'container' del documento HTML
const container = document.getElementById('container');


const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');


registerBtn.addEventListener('click', () => {
       container.classList.add("active");
});

// Añade un evento al botón de inicio de sesión que se activa cuando se hace clic
loginBtn.addEventListener('click', () => {
    // Al hacer clic en el botón de inicio de sesión, se elimina la clase 'active' del contenedor
    // Esto oculta el formulario de registro y muestra el de inicio de sesión
    container.classList.remove("active");
});