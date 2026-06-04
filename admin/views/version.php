<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-10 offset-xl-1">
            <div class="card shadow-sm">
                <div class="card-header bg-desert text-white d-flex align-items-center gap-2">
                    <i class="bi bi-code-branch fs-5"></i>
                    <h5 class="mb-0">Historial de Versiones</h5>
                </div>
                <div class="card-body">
                    <h4 class="mb-1">Sistema de Administración — Hotel Atankalama</h4>
                    <p class="text-muted mb-1">Panel centralizado de control de acceso y configuración del sistema.</p>
                    <p class="text-muted small">Desarrollado por Rodrigo Jaque Escobar</p>
                    <hr>

                    <!-- 1.0 -->
                    <div class="row mb-4">
                        <div class="col-sm-12">
                            <h5>Versión 1.0 <small class="text-muted fw-normal"> — Lanzamiento inicial · 28/05/2026</small></h5>
                            <h6 class="text-muted">Perfil: Administradores</h6>
                            <ul>
                                <li><strong>Seguridad — Autenticación OTP centralizada:</strong> Integración con AccesoBootstrap para login de doble factor vía código de 6 dígitos enviado por email. Verificación de permisos en cada request mediante AccesoService.</li>
                                <li><strong>Seguridad — Control de sesiones remoto:</strong> Función "Forzar logout" que invalida la sesión de cualquier usuario en el próximo request. Función "Ver como" para impersonación con alerta visual y salida controlada.</li>
                                <li><strong>Funcionalidad — Gestión de usuarios:</strong> CRUD completo de usuarios del sistema con asignación de apps, roles y secciones. Incluye log de ingresos filtrable por usuario.</li>
                                <li><strong>Funcionalidad — Gestión de aplicaciones:</strong> Registro de apps del hotel con asignación y remoción de usuarios autorizados.</li>
                                <li><strong>Funcionalidad — Roles y secciones:</strong> Creación de roles por app y asignación granular de secciones a cada rol. Control de tipo público/restringido por sección.</li>
                                <li><strong>Funcionalidad — Empresas y Áreas:</strong> CRUD de empresas para el módulo de contratos (doc_) y CRUD de áreas para el módulo de checklists (chk_).</li>
                                <li><strong>UX / Interfaz — Navbar superior:</strong> Menú de navegación con dropdowns, estado activo por ruta, banner de impersonación y dropdown de usuario con cierre de sesión.</li>
                            </ul>
                        </div>
                    </div>
                    <hr class="dropdown-divider">

                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../helpers/cierre.php'; ?>
