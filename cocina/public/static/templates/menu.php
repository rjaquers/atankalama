<nav class='navbar navbar-expand-lg navbar-dark bg-primary'>

    <div class='container-fluid'>

        <!-- Logo -->
        <a class='navbar-brand d-flex align-items-center' href='index.php'>
            <img src='<?= BASE_URL ?>public/static/img/logoAtankalama.png' width='150' alt='Atankalama'
                title='Atankalama'>
        </a>

        <!-- Botón hamburguesa -->
        <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarMenu'
            aria-controls='navbarMenu' aria-expanded='false' aria-label='Toggle navigation'>
            <span class='navbar-toggler-icon'></span>
        </button>

        <!-- Menú colapsable -->
        <div class='collapse navbar-collapse' id='navbarMenu'>
            
            <!-- Menú Izquierdo -->
            <ul class='navbar-nav me-auto mb-2 mb-lg-0'>
                <li class='nav-item'>
                    <a class="nav-link <?= ($_GET['page'] ?? '') === 'cocina/index' ? 'active' : '' ?>"
                        href='index.php?page=cocina/index' target='_blank'>
                        <i class="bi bi-fire me-1"></i> Cocina
                    </a>
                </li>
            </ul>

            <!-- Menú Derecho -->
            <ul class='navbar-nav ms-auto mb-2 mb-lg-0'>

                <!-- Reloj -->
                <li class='nav-item d-flex align-items-center me-3 pe-3 border-end border-light border-opacity-25'>
                    <span class="fw-bold text-white fs-6 d-flex align-items-center">
                        <i class="bi bi-clock me-2 opacity-75"></i> <span id="relojActual"><?= date('d-m-Y H:i:s') ?></span>
                    </span>
                </li>

                <!-- Ordenes (Solo Particulares) -->
                <li class='nav-item'>
                    <?php 
                    $isOrdenesActive = in_array($_GET['page'] ?? '', ['recepcion/index', 'recepcion/particular', 'recepcion/imprimir']);
                    ?>
                    <a class="nav-link <?= $isOrdenesActive ? 'active' : '' ?>"
                        href='index.php?page=recepcion/particular'>
                        Orden Particular
                    </a>
                </li>

                <!-- Dropdown Comandas -->
                <li class='nav-item dropdown'>
                    <a class="nav-link dropdown-toggle <?= str_starts_with($_GET['page'] ?? '', 'comanda/') ? 'active' : '' ?>"
                        href='#' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        Comandas
                    </a>
                    <ul class='dropdown-menu dropdown-menu-end'>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'comanda/cena' ? 'active' : '' ?>"
                                href='index.php?page=comanda/cena'>
                                <i class='bi bi-moon-stars-fill me-2 text-primary'></i>Cena / Colación
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'comanda/especial' ? 'active' : '' ?>"
                                href='index.php?page=comanda/especial'>
                                <i class='bi bi-star-fill me-2 text-warning'></i>Colación Especial
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'comanda/desayuno' ? 'active' : '' ?>"
                                href='index.php?page=comanda/desayuno'>
                                <i class='bi bi-sun-fill me-2 text-info'></i>Desayuno
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'desayuno/tablero' ? 'active' : '' ?>"
                                href='index.php?page=desayuno/tablero'>
                                <i class='bi bi-table me-2 text-warning'></i>Desayuno Masivo
                            </a>
                        </li>
                        <li><hr class='dropdown-divider'></li>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'comanda/listado' ? 'active' : '' ?>"
                                href='index.php?page=comanda/listado'>
                                <i class='bi bi-journal-text me-2'></i>Ver Listado
                            </a>
                        </li>
                        <li><hr class='dropdown-divider'></li>
                        <li>
                            <a class="dropdown-item <?= str_starts_with($_GET['page'] ?? '', 'voucher/') ? 'active' : '' ?>"
                                href='index.php?page=voucher/kiosko' target='_blank'>
                                <i class='bi bi-display me-2 text-info'></i>Kiosko Vouchers
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Dropdown Sistema / Gestión -->
                <li class='nav-item dropdown'>
                    <?php
                    $gestionPages = ['recepcion/listado', 'producto/index', 'estadistica/index', 'Reporte/ver', 'cocina/log', 'empresa/index'];
                    $isGestionActive = in_array($_GET['page'] ?? '', $gestionPages)
                        || str_starts_with($_GET['page'] ?? '', 'empresa/');
                    ?>
                    <a class="nav-link dropdown-toggle <?= $isGestionActive ? 'active' : '' ?>"
                        href='#' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        Gestión
                    </a>
                    <ul class='dropdown-menu dropdown-menu-end'>
                        <li> 
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'recepcion/listado' ? 'active' : '' ?>"
                                href='index.php?page=recepcion/listado'>
                                <i class="bi bi-list-ul me-2"></i> Listado
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'producto/index' ? 'active' : '' ?>"
                                href='index.php?page=producto/index'>
                                <i class="bi bi-box-seam me-2"></i> Productos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'estadistica/index' ? 'active' : '' ?>"
                                href='index.php?page=estadistica/index'>
                                <i class="bi bi-bar-chart me-2"></i> Estadística
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'Reporte/ver' ? 'active' : '' ?>"
                                href='index.php?page=Reporte/ver'>
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Excel
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= str_starts_with($_GET['page'] ?? '', 'empresa/') ? 'active' : '' ?>"
                                href='index.php?page=empresa/index'>
                                <i class="bi bi-building me-2"></i> Empresas
                            </a>
                        </li>
                        <li><hr class='dropdown-divider'></li>
                        <li>
                            <a class="dropdown-item <?= ($_GET['page'] ?? '') === 'cocina/log' ? 'active' : '' ?>"
                                href='index.php?page=cocina/log'>
                                <i class="bi bi-terminal me-2"></i> Log
                            </a>
                        </li>
                    </ul>
                </li>

                <li class='nav-item'>
                    <a href='index.php?page=voucher/kiosko' target='_blank'
                       class='nav-link px-2' title='Kiosko Vouchers'>
                        <i class='bi bi-display fs-5'></i>
                    </a>
                </li>

                <li class='nav-item'>
                    <a href='https://www.atankalama.com/login/index.php?route=dashboard' class='nav-link text-white-50'>
                        <i class='bi bi-house-door me-1'></i> Inicio
                    </a>
                </li>

                <li class='nav-item'>
                    <a href='index.php?page=logout' class='nav-link text-danger'>
                        <i class='bi bi-power me-1'></i> Salir
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>