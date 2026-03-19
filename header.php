<?php
include_once 'config.php';

$database = new Database();
$db = $database->getConnection();
$libro_obj = new Libro($db);
$stmt_libros = $libro_obj->leer();
$libros = $stmt_libros->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookReviews - Tu plataforma de reseñas de libros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #D2691E;
            --accent-color: #F4A460;
            --dark-bg: #1a1a1a;
            --card-bg: #2d2d2d;
            --text-light: #f8f9fa;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            padding-top: 76px;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Merriweather', serif;
        }
        
        .navbar-brand {
            font-family: 'Merriweather', serif;
            font-weight: 700;
            color: var(--accent-color) !important;
        }
        
        .card {
            background-color: var(--card-bg);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        .book-cover {
            height: 250px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .rating-stars {
            color: #FFD700;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('images/background.jpg');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            margin-bottom: 40px;
        }
        
        .category-badge {
            background-color: var(--accent-color);
            color: #000;
        }
        
        .footer {
            background-color: #111;
            padding: 40px 0;
            margin-top: 50px;
        }
        
        .book-card {
            height: 100%;
        }
        
        .review-card {
            border-left: 4px solid var(--accent-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #795c5cff;">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book-half"></i> BookReviews
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="libros.php">Libros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="favoritos.php">Favoritos</a>
                    </li>
                    <!-- NUEVO: Enlace para agregar libros para usuarios autenticados -->
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="agregar_libro.php">Agregar Libro</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex">
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="favoritos.php">Favoritos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light me-2">Iniciar Sesión</a>
                        <a href="registro.php" class="btn btn-primary">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>