<?php
include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$libro_obj = new Libro($db);
$stmt = $libro_obj->leer();
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Catálogo de Libros</h2>
        <!-- MODIFICADO: Permitir a todos los usuarios autenticados agregar libros -->
        <?php if(isset($_SESSION['usuario_id'])): ?>
            <a href="agregar_libro.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Agregar Libro
            </a>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if(!empty($libros)): ?>
            <?php foreach($libros as $libro_item): ?>
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
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                    <div class="mt-2">
                        <a href="agregar_libro.php" class="btn btn-primary">¡Agrega el primer libro!</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>