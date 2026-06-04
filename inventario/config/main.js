/* ===================================================
   Proyecto: Hotel Atankalama - Sistema de Inventario
   Archivo: main.js
   Descripción: Funciones globales JS, menú móvil y animaciones.
   =================================================== */

document.addEventListener("DOMContentLoaded", function() {
    // ======= Navbar Scroll Effect =======
    const navbar = document.querySelector(".navbar");
    if (navbar) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 20) {
                navbar.classList.add("shadow-sm");
            } else {
                navbar.classList.remove("shadow-sm");
            }
        });
    }

    // ======= Responsive Navbar Toggle =======
    const toggler = document.querySelector(".navbar-toggler");
    const navLinks = document.querySelectorAll(".nav-link");

    if (toggler) {
        navLinks.forEach(link => {
            link.addEventListener("click", () => {
                const navbarCollapse = document.querySelector(".navbar-collapse");
                if (navbarCollapse.classList.contains("show")) {
                    new bootstrap.Collapse(navbarCollapse).toggle();
                }
            });
        });
    }

    // ======= Fade Animations =======
    const fadeElements = document.querySelectorAll(".fade-in");
    fadeElements.forEach(el => {
        el.style.opacity = 0;
        el.style.transition = "opacity 0.6s ease";
        setTimeout(() => { el.style.opacity = 1; }, 100);
    });

    console.log("✅ main.js cargado correctamente");

    /* Compensa altura del navbar fixed-top automáticamente */
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.querySelector('.navbar.fixed-top');
        const main = document.querySelector('main');
        if (navbar && main) {
            const navbarHeight = navbar.offsetHeight;
            main.style.marginTop = (navbarHeight + 10) + 'px'; // 10px extra de respiro
        }
    });


});
