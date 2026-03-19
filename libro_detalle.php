<?php
include_once 'config.php';

if(!isset($_GET['id'])) {
    header("Location: libros.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$libro_detalle = new Libro($db);
$libro_detalle->id_libro = $_GET['id'];

if(!$libro_detalle->leerUno()) {
    header("Location: libros.php");
    exit();
}

$reseña = new Reseña($db);
$reseña->id_libro = $_GET['id'];
$stmt_reseñas = $reseña->leerPorLibro();
$reseñas_libro = $stmt_reseñas->fetchAll(PDO::FETCH_ASSOC);

$mensaje_reseña = '';
if(isset($_POST['agregar_reseña']) && isset($_SESSION['usuario_id'])) {
    if(isset($_POST['id_libro']) && isset($_POST['comentario']) && isset($_POST['puntuacion'])) {
        $id_libro = intval($_POST['id_libro']);
        $comentario = trim($_POST['comentario']);
        $puntuacion = intval($_POST['puntuacion']);
        
        if($id_libro > 0 && $puntuacion >= 1 && $puntuacion <= 5 && !empty($comentario)) {
            $nueva_reseña = new Reseña($db);
            $nueva_reseña->id_libro = $id_libro;
            $nueva_reseña->id_usuario = $_SESSION['usuario_id'];
            $nueva_reseña->comentario = $comentario;
            $nueva_reseña->puntuacion = $puntuacion;
            
            if($nueva_reseña->crear()) {
                $mensaje_reseña = '<div class="alert alert-success">Reseña agregada correctamente.</div>';
                $stmt_reseñas = $reseña->leerPorLibro();
                $reseñas_libro = $stmt_reseñas->fetchAll(PDO::FETCH_ASSOC);
                $_POST = array();
            } else {
                $mensaje_reseña = '<div class="alert alert-danger">Error al agregar la reseña.</div>';
            }
        } else {
            $mensaje_reseña = '<div class="alert alert-danger">Por favor completa todos los campos correctamente.</div>';
        }
    } else {
        $mensaje_reseña = '<div class="alert alert-danger">Faltan campos obligatorios.</div>';
    }
}

$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$es_favorito = false;
if($usuario_id) {
    try {
        $es_favorito = esFavorito($db, $libro_detalle->id_libro, $usuario_id);
    } catch (Exception $e) {
        $es_favorito = false;
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <img src="<?php echo obtenerImagenLibro($libro_detalle->imagen); ?>" 
                 class="img-fluid rounded book-cover" 
                 alt="<?php echo htmlspecialchars($libro_detalle->titulo); ?>">
        </div>
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($libro_detalle->titulo); ?></h1>
            <p class="lead">por <?php echo htmlspecialchars($libro_detalle->autor); ?></p>
            
            <div class="mb-3">
                <span class="rating-stars fs-4">
                    <?php echo mostrarEstrellas($libro_detalle->puntuacion_promedio ?? 0); ?>
                </span>
                <span class="ms-2 fs-5"><?php echo number_format($libro_detalle->puntuacion_promedio ?? 0, 1); ?></span>
            </div>
            
            <span class="badge category-badge fs-6"><?php echo htmlspecialchars($libro_detalle->categoria_nombre); ?></span>
            
            <div class="mt-4">
                <h4>Resumen</h4>
                <p><?php echo nl2br(htmlspecialchars($libro_detalle->resumen ?: 'No hay resumen disponible.')); ?></p>
            </div>
            
            <div class="mt-3">
                <strong>Año de publicación:</strong> <?php echo htmlspecialchars($libro_detalle->anio_publicacion); ?>
            </div>
            
            <div class="mt-3">
                <?php if($usuario_id): ?>
                <a href="agregar_favorito.php?libro_id=<?php echo $libro_detalle->id_libro; ?>" 
                   class="btn <?php echo $es_favorito ? 'btn-outline-danger' : 'btn-outline-secondary'; ?>">
                    <i class="bi bi-heart<?php echo $es_favorito ? '-fill' : ''; ?>"></i> 
                    <?php echo $es_favorito ? 'Quitar de Favoritos' : 'Agregar a Favoritos'; ?>
                </a>
                <?php endif; ?>
                
                <!-- NUEVO: Botón de Wattpad -->
                <?php if(!empty($libro_detalle->wattpad_link)): ?>
                <a href="<?php echo htmlspecialchars($libro_detalle->wattpad_link); ?>" 
                   target="_blank" 
                   class="btn btn-success">
                    <i class="bi bi-book"></i> Leer en Wattpad
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <h3>Reseñas</h3>
            
            <?php if(isset($_SESSION['usuario_id'])): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Agregar Reseña</h5>
                    <?php echo $mensaje_reseña; ?>
                    <form method="post">
                        <input type="hidden" name="agregar_reseña" value="1">
                        <input type="hidden" name="id_libro" value="<?php echo $libro_detalle->id_libro; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Puntuación</label>
                            <div>
                                <select name="puntuacion" class="form-select" required>
                                    <option value="">Seleccionar puntuación</option>
                                    <option value="1" <?php echo (isset($_POST['puntuacion']) && $_POST['puntuacion'] == 1) ? 'selected' : ''; ?>>1 Estrella</option>
                                    <option value="2" <?php echo (isset($_POST['puntuacion']) && $_POST['puntuacion'] == 2) ? 'selected' : ''; ?>>2 Estrellas</option>
                                    <option value="3" <?php echo (isset($_POST['puntuacion']) && $_POST['puntuacion'] == 3) ? 'selected' : ''; ?>>3 Estrellas</option>
                                    <option value="4" <?php echo (isset($_POST['puntuacion']) && $_POST['puntuacion'] == 4) ? 'selected' : ''; ?>>4 Estrellas</option>
                                    <option value="5" <?php echo (isset($_POST['puntuacion']) && $_POST['puntuacion'] == 5) ? 'selected' : ''; ?>>5 Estrellas</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Comentario</label>
                            <textarea name="comentario" class="form-control" rows="4" required placeholder="Escribe tu reseña aquí..."><?php echo isset($_POST['comentario']) ? htmlspecialchars($_POST['comentario']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Enviar Reseña</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <a href="login.php" class="alert-link">Inicia sesión</a> para agregar una reseña.
            </div>
            <?php endif; ?>
            
            <?php if(!empty($reseñas_libro)): ?>
                <?php foreach($reseñas_libro as $reseña_item): ?>
                <div class="card mb-3 review-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="images/usuarios/default.jpg" 
                                 class="rounded-circle me-3" width="50" height="50" alt="Usuario">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($reseña_item['usuario_nombre']); ?></h6>
                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($reseña_item['fecha_publicacion'])); ?></small>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <span class="rating-stars">
                                <?php echo mostrarEstrellas($reseña_item['puntuacion']); ?>
                            </span>
                        </div>
                        
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($reseña_item['comentario'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Aún no hay reseñas para este libro. ¡Sé el primero en comentar!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>