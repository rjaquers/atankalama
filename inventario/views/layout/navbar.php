<nav class='navbar navbar-expand-lg navbar-dark bg-primary fixed-top'>
    <div class='container'>
        <a class='navbar-brand' href='index.php?page=dashboard'>
            <i class='fas fa-boxes me-2'></i>Inventario Atankalama
        </a>
        <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#menuPrincipal'
                aria-controls='menuPrincipal' aria-expanded='false' aria-label='Toggle navigation'>
            <span class='navbar-toggler-icon'></span>
        </button>

        <div class='collapse navbar-collapse' id='menuPrincipal'>
            <ul class='navbar-nav ms-auto mb-2 mb-lg-0'>

                <!-- DASHBOARD -->
                <li class='nav-item'>
                    <a class='nav-link' href='index.php?page=dashboard'>
                        <i class='fas fa-home me-1'></i> Home
                    </a>
                </li>

                <!-- INVENTARIO -->
                <li class='nav-item dropdown'>
                    <a class='nav-link dropdown-toggle' href='#' id='inventarioDropdown'
                       role='button' data-bs-toggle='dropdown'>
                        <i class='fas fa-boxes me-1'></i> Inventario
                    </a>
                    <ul class='dropdown-menu'>
                        <li>
                            <a class='dropdown-item' href='index.php?page=products'>
                                <i class='fas fa-box me-2'></i> Productos
                            </a>
                        </li>

                        <li>
                        <a class='dropdown-item' href='index.php?page=consumption&action=create'>
                            <i class='fas fa-arrow-down me-2'></i>    Registrar Consumo Simple
                        </a>
                        </li>

                        <li class='nav-item'>
                            <a class='dropdown-item' href='index.php?page=consumption&action=batch'>
                                <i class='fas fa-layer-group me-1'></i> Registrar Consumo Masivo
                            </a>
                        </li>
                        <li>
                            <a class='dropdown-item' href='index.php?page=stock_entry&action=create'>
                                <i class='fas fa-arrow-up me-2'></i> Ingresar Inventario
                            </a>
                        </li>
                        <li>
                            <a class='dropdown-item' href='index.php?page=stock_entry&action=batch'>
                                <i class='fas fa-layer-group me-2'></i> Ingreso en Lote
                            </a>
                        </li>
                        <li>
                            <a class='dropdown-item' href='index.php?page=voice_stock'>
                                <i class='fas fa-microphone me-2'></i> Operación por Voz
                            </a>
                        </li>
                        <li>
                            <a class='dropdown-item' href='index.php?page=chatbot'>
                                <i class='fas fa-robot me-2'></i> Asistente IA
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CONFIGURACIÓN -->
                <li class='nav-item dropdown'>
                    <a class='nav-link dropdown-toggle' href='#' id='configDropdown'
                       role='button' data-bs-toggle='dropdown'>
                        <i class='fas fa-cogs me-1'></i> Configuración
                    </a>
                    <ul class='dropdown-menu'>
                        <li>
                            <a class='dropdown-item' href='index.php?page=categories'>
                                Categorías
                            </a>
                        </li>
                        <li>
                            <a class='dropdown-item' href='index.php?page=locations'>
                                Ubicaciones
                            </a>
                        </li>
                        <li>
                            <a class='dropdown-item' href='index.php?page=users'>
                                Usuarios
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ADMIN -->
                <li class='nav-item'>
                    <a class='nav-link' href='index.php?page=logs'>
                        <i class='fas fa-clipboard-list me-1'></i> Registros
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-warning" href="#" onclick="hardReload()">
                        <i class="fas fa-sync-alt me-1"></i> Forzar Recarga
                    </a>
                </li>

            </ul>
        </div>

    </div>
</nav>