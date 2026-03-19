<?php
include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$libro_obj = new Libro($db);
$reseña_obj = new Reseña($db);

$stmt_libros = $libro_obj->leer();
$libros_destacados = $stmt_libros->fetchAll(PDO::FETCH_ASSOC);

$reseñas_recientes = array();
if(!empty($libros_destacados)) {
    $reseña_obj->id_libro = $libros_destacados[0]['id_libro'];
    $stmt_reseñas = $reseña_obj->leerPorLibro();
    $reseñas_recientes = $stmt_reseñas->fetchAll(PDO::FETCH_ASSOC);
}

$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
?>

<?php include 'header.php'; ?>

<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Descubre tu próximo libro favorito</h1>
        <p class="lead mb-4">Comparte tus opiniones, encuentra recomendaciones y conecta con otros amantes de la lectura</p>
        <a href="libros.php" class="btn btn-primary btn-lg">Explorar Libros</a>
    </div>
</section>

<div class="container">
    <section class="mb-5">
        <h2 class="mb-4">Libros Destacados</h2>
        <div class="row">
            <?php if(!empty($libros_destacados)): ?>
                <?php foreach(array_slice($libros_destacados, 0, 3) as $libro_item): ?>
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
                                <?php if($usuario_id): ?>
                                <?php
                                $es_favorito = false;
                                try {
                                    $es_favorito = esFavorito($db, $libro_item['id_libro'], $usuario_id);
                                } catch (Exception $e) {
                                    $es_favorito = false;
                                }
                                ?>
                                <a href="agregar_favorito.php?libro_id=<?php echo $libro_item['id_libro']; ?>" 
                                   class="btn btn-sm <?php echo $es_favorito ? 'btn-outline-danger' : 'btn-outline-secondary'; ?>">
                                    <i class="bi bi-heart<?php echo $es_favorito ? '-fill' : ''; ?>"></i> 
                                    <?php echo $es_favorito ? 'Quitar' : 'Favorito'; ?>
                                </a>
                                <?php endif; ?>
                                
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
                        No hay libros disponibles en este momento.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="libros.php" class="btn btn-primary">Ver Todos los Libros</a>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="mb-4">Reseñas Recientes</h2>
        <div class="row">
            <?php if(!empty($reseñas_recientes)): ?>
                <?php foreach(array_slice($reseñas_recientes, 0, 2) as $reseña_item): ?>
                <div class="col-md-6 mb-4">
                    <div class="card review-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="images/usuarios/default.jpg" 
                                     class="rounded-circle me-3" width="50" height="50" alt="Usuario">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($reseña_item['usuario_nombre'] ?? 'Usuario'); ?></h6>
                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($reseña_item['fecha_publicacion'])); ?></small>
                                </div>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($libros_destacados[0]['titulo']); ?></h5>
                            <div class="mb-2">
                                <span class="rating-stars">
                                    <?php echo mostrarEstrellas($reseña_item['puntuacion']); ?>
                                </span>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($reseña_item['comentario'])); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Aún no hay reseñas. ¡Sé el primero en comentar!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>