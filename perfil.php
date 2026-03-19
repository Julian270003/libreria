<?php
include_once 'config.php';

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas reales del usuario
$usuario_id = $_SESSION['usuario_id'];

// Contar reseñas del usuario
$query_resenas = "SELECT COUNT(*) as total_resenas FROM reseñas WHERE id_usuario = ?";
$stmt_resenas = $db->prepare($query_resenas);
$stmt_resenas->execute([$usuario_id]);
$total_resenas = $stmt_resenas->fetch(PDO::FETCH_ASSOC)['total_resenas'];

// Contar favoritos del usuario
$query_favoritos = "SELECT COUNT(*) as total_favoritos FROM favoritos WHERE id_usuario = ?";
$stmt_favoritos = $db->prepare($query_favoritos);
$stmt_favoritos->execute([$usuario_id]);
$total_favoritos = $stmt_favoritos->fetch(PDO::FETCH_ASSOC)['total_favoritos'];

// Contar libros leídos del usuario
$query_leidos = "SELECT COUNT(*) as total_leidos FROM usuario_libros WHERE id_usuario = ? AND estado = 'leido'";
$stmt_leidos = $db->prepare($query_leidos);
$stmt_leidos->execute([$usuario_id]);
$total_leidos = $stmt_leidos->fetch(PDO::FETCH_ASSOC)['total_leidos'];

// Obtener libros recientemente agregados por el usuario
$query_libros_agregados = "SELECT titulo, fecha_agregado FROM libros 
                          WHERE id_usuario_agrego = ? 
                          ORDER BY fecha_agregado DESC 
                          LIMIT 5";
$stmt_libros_agregados = $db->prepare($query_libros_agregados);
$stmt_libros_agregados->execute([$usuario_id]);
$libros_agregados = $stmt_libros_agregados->fetchAll(PDO::FETCH_ASSOC);

// Obtener reseñas recientes del usuario
$query_resenas_recientes = "SELECT r.comentario, r.puntuacion, r.fecha_publicacion, l.titulo as libro_titulo 
                           FROM reseñas r 
                           JOIN libros l ON r.id_libro = l.id_libro 
                           WHERE r.id_usuario = ? 
                           ORDER BY r.fecha_publicacion DESC 
                           LIMIT 3";
$stmt_resenas_recientes = $db->prepare($query_resenas_recientes);
$stmt_resenas_recientes->execute([$usuario_id]);
$resenas_recientes = $stmt_resenas_recientes->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="images/usuarios/default.jpg" 
                         class="rounded-circle mb-3" width="150" height="150" alt="Usuario">
                    <h4><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($_SESSION['usuario_correo']); ?></p>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></span>
                    
                    <!-- Información adicional del usuario -->
                    <div class="mt-3">
                        <small class="text-muted">
                            Miembro desde: <?php 
                            // Obtener fecha de registro (necesitarías agregar este campo a la tabla usuarios)
                            $query_fecha = "SELECT fecha_registro FROM usuarios WHERE id_usuario = ?";
                            $stmt_fecha = $db->prepare($query_fecha);
                            $stmt_fecha->execute([$usuario_id]);
                            $fecha_registro = $stmt_fecha->fetch(PDO::FETCH_ASSOC)['fecha_registro'];
                            echo $fecha_registro ? date('d/m/Y', strtotime($fecha_registro)) : 'Recientemente';
                            ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Libros recientemente agregados -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5>Mis Libros Agregados</h5>
                    <?php if(!empty($libros_agregados)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($libros_agregados as $libro): ?>
                                <div class="list-group-item bg-transparent text-light border-secondary">
                                    <small class="fw-bold"><?php echo htmlspecialchars($libro['titulo']); ?></small>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($libro['fecha_agregado'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">Aún no has agregado libros.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h4>Mi Actividad</h4>
                    <div class="row text-center mt-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5><?php echo $total_resenas; ?></h5>
                                    <p>Reseñas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5><?php echo $total_favoritos; ?></h5>
                                    <p>Favoritos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5><?php echo $total_leidos; ?></h5>
                                    <p>Libros Leídos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reseñas recientes -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5>Mis Reseñas Recientes</h5>
                    <?php if(!empty($resenas_recientes)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($resenas_recientes as $resena): ?>
                                <div class="list-group-item bg-transparent text-light border-secondary">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($resena['libro_titulo']); ?></h6>
                                            <p class="mb-1 small"><?php echo nl2br(htmlspecialchars(substr($resena['comentario'], 0, 100) . '...')); ?></p>
                                            <div class="rating-stars small">
                                                <?php echo mostrarEstrellas($resena['puntuacion']); ?>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($resena['fecha_publicacion'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aún no has escrito reseñas.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Acciones rápidas -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5>Acciones Rápidas</h5>
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="agregar_libro.php" class="btn btn-primary me-2">
                            <i class="bi bi-plus-circle"></i> Agregar Libro
                        </a>
                        <a href="libros.php" class="btn btn-outline-primary me-2">
                            <i class="bi bi-book"></i> Explorar Libros
                        </a>
                        <a href="favoritos.php" class="btn btn-outline-success">
                            <i class="bi bi-heart"></i> Ver Favoritos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>