<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Módulo de Licitaciones y Ofertas') ?> - Suplos</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <!-- Vue.js 3 & Axios & SweetAlert2 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navbar Corporativo -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-suplos mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/ofertas">
                <i class="fa-solid fa-layer-group text-info"></i>
                <span>SUPLOS</span>
                <span class="brand-badge ms-1">Ofertas Estándar</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold px-3" href="/ofertas">
                            <i class="fa-solid fa-list-check me-1"></i> Listado de Ofertas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm text-primary fw-bold px-3 shadow-sm" href="/ofertas/crear">
                            <i class="fa-solid fa-plus-circle me-1"></i> Nueva Oferta
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container pb-5">
