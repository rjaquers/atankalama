<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-code-branch"></i> Historial de Versiones</h3>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4>Sistema de Novedades — Hotel Atankalama</h4>
            <p class="text-muted">Registro de cambios y mejoras del sistema.</p>
            <p class="small">Desarrollado por Rodrigo Jaque Escobar</p>
            <hr>

            <!-- 1.1 -->
            <div class="row">
                <div class="col-sm-12">
                    <h2>Versión 1.1 <small class="text-muted fs-6"> — Actualizaciones realizadas el 01/06/2026</small></h2>
                    <h5>Perfil: Todos</h5>
                    <ul>
                        <li><strong>UX / Interfaz — Menú superior:</strong> El menú lateral izquierdo fue reemplazado por una barra de navegación horizontal sticky en la parte superior. En móvil se colapsa con un botón hamburguesa nativo de Bootstrap. El contenido ahora ocupa el 100% del ancho de pantalla.</li>
                        <li><strong>UX / Interfaz — Organización del menú:</strong> Los ítems se ordenaron en dos grupos: izquierda (Dashboard, Nueva Novedad, Historial, Gestión▾) y derecha (Versiones, Inicio, avatar del usuario▾). El menú Gestión agrupa Empresas, Encargados y Personal en un dropdown.</li>
                        <li><strong>UX / Interfaz — Micro-interacciones:</strong> Se aplicaron principios de diseño de Emil Kowalski: transiciones suaves en todos los links (180ms con curva cubic-bezier), feedback de press en botones (scale 0.97), stagger de entrada en KPI cards (cascada de 65ms), hover lift en tarjetas y dropdown con animación de entrada.</li>
                        <li><strong>UX / Interfaz — Avatar de usuario:</strong> Cuando hay sesión activa, la navbar muestra un avatar circular con la inicial del nombre y un dropdown con el email, acceso al Panel Admin y botón de Cerrar sesión.</li>
                        <li><strong>UX / Interfaz — Accesibilidad:</strong> Todos los efectos hover están protegidos con <code>@media (hover: hover) and (pointer: fine)</code> para evitar estados pegados en iOS. Se agregó soporte a <code>prefers-reduced-motion</code> que desactiva todas las animaciones para usuarios que lo requieran.</li>
                        <li><strong>Corrección — Rutas en página de versiones:</strong> Se corrigieron los <code>include</code> relativos en <code>views/version/index.php</code> usando <code>__DIR__</code> para que funcionen correctamente en producción independiente del directorio de trabajo de PHP.</li>
                        <li><strong>Funcionalidad — Inicio y Salir siempre visibles:</strong> Los botones "Inicio" (hub central) y "Cerrar sesión" ahora se muestran independientemente del estado de autenticación, siguiendo la regla de logout centralizado.</li>
                    </ul>
                </div>
            </div>
            <hr class="dropdown-divider">

            <!-- 1.0 -->
            <div class="row">
                <div class="col-sm-12">
                    <h2>Versión 1.0 <small class="text-muted fs-6"> — Actualizaciones realizadas el 01/06/2026</small></h2>
                    <h5>Perfil: Todos</h5>
                    <ul>
                        <li><strong>UX / Interfaz — Filtros rápidos en Dashboard:</strong> Se agregaron botones de acceso rápido al panel principal para filtrar por "Últimos 7 días", "Últimos 10 días", "Este mes" y "Mes pasado", sin necesidad de ingresar fechas manualmente.</li>
                        <li><strong>Funcionalidad — Desglose por hotel:</strong> El dashboard ahora muestra una tarjeta de resumen por hotel con total de novedades, cantidad de críticas, seguimientos pendientes, promedio de nivel de importancia y top 3 áreas con más registros.</li>
                        <li><strong>Funcionalidad — KPI Colaborador más activo:</strong> Nueva tarjeta en la fila de indicadores que muestra el colaborador con más novedades registradas en el período seleccionado.</li>
                        <li><strong>UX / Interfaz — Gráficos comparativos por hotel:</strong> Los tres gráficos (Departamento involucrado, Top Áreas, Nivel de Criticidad) ahora muestran barras agrupadas para comparar directamente entre Atankalama y Atankalama Inn.</li>
                        <li><strong>Funcionalidad — Ranking de registros:</strong> Nueva tabla con los 5 colaboradores que más novedades ingresaron en el período, con medallas y barras de progreso relativo.</li>
                        <li><strong>Base de datos — Nuevas consultas analíticas:</strong> Se agregaron 6 métodos en DashboardModel para obtener estadísticas desglosadas por hotel: por departamento, por área, distribución de criticidad, top registradores y resumen detallado por hotel.</li>
                    </ul>
                </div>
            </div>
            <hr>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
