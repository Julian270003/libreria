<?php
include_once 'config.php';

// Permitir tanto a administradores como a usuarios normales agregar libros
if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$categoria = new Categoria($db);
$stmt_categorias = $categoria->leer();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';

if($_POST && isset($_POST['titulo'])) {
    $libro = new Libro($db);
    $libro->titulo = trim($_POST['titulo']);
    $libro->autor = trim($_POST['autor']);
    $libro->categoria_id = intval($_POST['categoria_id']);
    $libro->anio_publicacion = intval($_POST['anio_publicacion']);
    $libro->resumen = trim($_POST['resumen']);
    $libro->imagen = trim($_POST['imagen']);
    $libro->link = trim($_POST['link']);

    // Validaciones
    if(empty($libro->titulo) || empty($libro->autor) || empty($libro->categoria_id)) {
        $mensaje = '<div class="alert alert-danger">Por favor completa todos los campos obligatorios.</div>';
    } elseif($libro->anio_publicacion < 1000 || $libro->anio_publicacion > date('Y')) {
        $mensaje = '<div class="alert alert-danger">El año de publicación no es válido.</div>';
    } else {
        if($libro->crear()){
            // Guardar quién agregó el libro
            $id_libro_creado = $db->lastInsertId();
            $query_update = "UPDATE libros SET id_usuario_agrego = ? WHERE id_libro = ?";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->execute([$_SESSION['usuario_id'], $id_libro_creado]);
            
            $mensaje = '<div class="alert alert-success">Libro agregado correctamente.</div>';
            // Limpiar el formulario
            $_POST = array();
        } else {
            $mensaje = '<div class="alert alert-danger">Error al agregar el libro. Verifica los datos.</div>';
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">
                        <i class="bi bi-book"></i> Agregar Nuevo Libro
                    </h3>
                    
                    <?php echo $mensaje; ?>
                    
                    <form method="post" id="formLibro">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título del Libro *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" 
                                           required maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="autor" class="form-label">Autor *</label>
                                    <input type="text" class="form-control" id="autor" name="autor" 
                                           value="<?php echo isset($_POST['autor']) ? htmlspecialchars($_POST['autor']) : ''; ?>" 
                                           required maxlength="255">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoria_id" class="form-label">Categoría *</label>
                                    <select class="form-select" id="categoria_id" name="categoria_id" required>
                                        <option value="">Seleccionar categoría</option>
                                        <?php foreach($categorias as $cat): ?>
                                        <option value="<?php echo $cat['id_categoria']; ?>" 
                                                <?php echo (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="anio_publicacion" class="form-label">Año de Publicación *</label>
                                    <input type="number" class="form-control" id="anio_publicacion" name="anio_publicacion" 
                                           value="<?php echo isset($_POST['anio_publicacion']) ? htmlspecialchars($_POST['anio_publicacion']) : ''; ?>" 
                                           required min="1000" max="<?php echo date('Y'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- NUEVO CAMPO: Enlace de Wattpad -->
                        <div class="mb-3">
                            <label for="link" class="form-label">Enlace de Wattpad</label>
                            <input type="url" class="form-control" id="link" name="link" 
                                   value="<?php echo isset($_POST['link']) ? htmlspecialchars($_POST['link']) : ''; ?>" 
                                   placeholder="https://www.wattpad.com/story/123456-titulo-del-libro">
                            <div class="form-text">
                                Opcional: Agrega el enlace de Wattpad para que los usuarios puedan leer el libro directamente.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Nombre de la Imagen *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="imagen" name="imagen" 
                                       value="<?php echo isset($_POST['imagen']) ? htmlspecialchars($_POST['imagen']) : ''; ?>" 
                                       placeholder="ejemplo: mi-libro.jpg" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="mostrarImagenes()">
                                    <i class="bi bi-image"></i> Ver Imágenes
                                </button>
                            </div>
                            <div class="form-text">
                                Solo el nombre del archivo (ej: "mi-libro.jpg"). La imagen debe estar en la carpeta images/libros/
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="resumen" class="form-label">Resumen *</label>
                            <textarea class="form-control" id="resumen" name="resumen" rows="5" 
                                      required placeholder="Escribe un resumen del libro..."><?php echo isset($_POST['resumen']) ? htmlspecialchars($_POST['resumen']) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle"></i> Agregar Libro
                            </button>
                            <a href="libros.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al Catálogo
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Panel de imágenes disponibles -->
            <div class="card mt-4" id="panelImagenes" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-images"></i> Imágenes Disponibles
                        <button type="button" class="btn-close float-end" onclick="ocultarImagenes()"></button>
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $carpeta_imagenes = 'images/libros/';
                    if (is_dir($carpeta_imagenes)) {
                        $imagenes = scandir($carpeta_imagenes);
                        $imagenes_validas = array_filter($imagenes, function($img) {
                            return $img != '.' && $img != '..' && preg_match('/\.(jpg|jpeg|png|gif)$/i', $img);
                        });
                        
                        if(count($imagenes_validas) > 0) {
                            echo '<div class="row">';
                            foreach($imagenes_validas as $imagen) {
                                echo '<div class="col-md-3 mb-3">';
                                echo '<div class="card h-100">';
                                echo '<img src="' . $carpeta_imagenes . $imagen . '" class="card-img-top" style="height: 120px; object-fit: cover;" alt="' . $imagen . '">';
                                echo '<div class="card-body text-center">';
                                echo '<small class="text-muted d-block">' . $imagen . '</small>';
                                echo '<button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="seleccionarImagen(\'' . $imagen . '\')">';
                                echo '<i class="bi bi-check-lg"></i> Seleccionar';
                                echo '</button>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-warning text-center">No hay imágenes en la carpeta.</div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">La carpeta images/libros/ no existe.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarImagenes() {
    document.getElementById('panelImagenes').style.display = 'block';
}

function ocultarImagenes() {
    document.getElementById('panelImagenes').style.display = 'none';
}

function seleccionarImagen(nombreImagen) {
    document.getElementById('imagen').value = nombreImagen;
    ocultarImagenes();
}

// Validación del formulario
document.getElementById('formLibro').addEventListener('submit', function(e) {
    const año = document.getElementById('anio_publicacion').value;
    const añoActual = new Date().getFullYear();
    
    if (año < 1000 || año > añoActual) {
        e.preventDefault();
        alert('El año de publicación debe estar entre 1000 y ' + añoActual);
        return false;
    }
});
</script>

<?php include 'footer.php'; ?>