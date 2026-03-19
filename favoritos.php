<?php
include_once 'config.php';

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$favoritos = obtenerFavoritos($db, $_SESSION['usuario_id']);
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Mis Libros Favoritos</h2>
    
    <?php if(isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
    
    <div class="row mt-4">
        <?php if(!empty($favoritos)): ?>
            <?php foreach($favoritos as $libro_item): ?>
            <div class="col-md-4 mb-4">
                <div class="card book-card">
                    <img src="<?php echo obtenerImagenLibro($libro_item['imagen']); ?>" 
                         class="card-img-top book-cover" 
                         alt="<?php echo htmlspecialchars($libro_item['titulo']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($libro_item['titulo']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($libro_item['autor']); ?></p>
                        <div class="mb-2">
                            <span class="rating-stars">
                                <?php echo mostrarEstrellas($libro_item['puntuacion_promedio'] ?? 0); ?>
                            </span>
                            <span class="ms-1"><?php echo number_format($libro_item['puntuacion_promedio'] ?? 0, 1); ?></span>
                        </div>
                        <span class="badge category-badge"><?php echo htmlspecialchars($libro_item['categoria_nombre']); ?></span>
                        <div class="mt-3">
                            <a href="libro_detalle.php?id=<?php echo $libro_item['id_libro']; ?>" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
                            <a href="agregar_favorito.php?libro_id=<?php echo $libro_item['id_libro']; ?>" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-heart-fill"></i> Quitar
                            </a>
                            
                            <!-- NUEVO: Botón de Wattpad -->
                            <?php if(!empty($libro_item['link'])): ?>
                            <a href="<?php echo htmlspecialchars($libro_item['link']); ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-success mt-1">
                                <i class="bi bi-book"></i> Wattpad
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-heart fs-1"></i>
                    <h4>Aún no tienes libros favoritos</h4>
                    <p>Explora nuestro catálogo y agrega libros a tus favoritos haciendo clic en el ícono ❤️</p>
                    <a href="libros.php" class="btn btn-primary">Explorar Libros</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>