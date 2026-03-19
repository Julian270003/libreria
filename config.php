<?php
session_start();

class Database {
    private $host = "127.0.0.1";
    private $db_name = "reseñas_libros";
    private $username = "root";
    private $password = "root";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

function verificarAdmin() {
    if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
        header("Location: login.php");
        exit();
    }
}

class Libro {
    private $conn;
    private $table_name = "libros";

    public $id_libro;
    public $titulo;
    public $autor;
    public $categoria_id;
    public $anio_publicacion;
    public $resumen;
    public $imagen;
    public $puntuacion_promedio;
    public $link;
    public $id_usuario_agrego;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT l.*, c.nombre as categoria_nombre 
                  FROM " . $this->table_name . " l 
                  LEFT JOIN categorias c ON l.categoria_id = c.id_categoria 
                  ORDER BY l.fecha_agregado DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET titulo=:titulo, autor=:autor, categoria_id=:categoria_id, 
                  anio_publicacion=:anio_publicacion, resumen=:resumen, imagen=:imagen, link=:link, id_usuario_agrego=:id_usuario_agrego";
        
        $stmt = $this->conn->prepare($query);

        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->autor = htmlspecialchars(strip_tags($this->autor));
        $this->resumen = htmlspecialchars(strip_tags($this->resumen));
        $this->link = htmlspecialchars(strip_tags($this->link));
        $this->id_usuario_agrego = $_SESSION['usuario_id'];

        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":autor", $this->autor);
        $stmt->bindParam(":categoria_id", $this->categoria_id);
        $stmt->bindParam(":anio_publicacion", $this->anio_publicacion);
        $stmt->bindParam(":resumen", $this->resumen);
        $stmt->bindParam(":imagen", $this->imagen);
        $stmt->bindParam(":link", $this->link);
        $stmt->bindParam(":id_usuario_agrego", $this->id_usuario_agrego);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function leerUno() {
        $query = "SELECT l.*, c.nombre as categoria_nombre 
                  FROM " . $this->table_name . " l 
                  LEFT JOIN categorias c ON l.categoria_id = c.id_categoria 
                  WHERE l.id_libro = ? 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_libro);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->titulo = $row['titulo'];
            $this->autor = $row['autor'];
            $this->categoria_id = $row['categoria_id'];
            $this->categoria_nombre = $row['categoria_nombre'];
            $this->anio_publicacion = $row['anio_publicacion'];
            $this->resumen = $row['resumen'];
            $this->imagen = $row['imagen'];
            $this->puntuacion_promedio = $row['puntuacion_promedio'];
            $this->link = $row['link'];
            $this->id_usuario_agrego = $row['id_usuario_agrego'];
            return true;
        }
        return false;
    }
}

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id_usuario;
    public $nombre;
    public $correo;
    public $password;
    public $rol;
    public $libros_leidos;
    public $fecha_registro;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre=:nombre, correo=:correo, password=:password, rol='usuario'";
        
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT id_usuario, nombre, correo, password, rol, libros_leidos, fecha_registro 
                  FROM " . $this->table_name . " 
                  WHERE correo = ? 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->correo);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($this->password, $row['password'])) {
                $this->id_usuario = $row['id_usuario'];
                $this->nombre = $row['nombre'];
                $this->correo = $row['correo'];
                $this->rol = $row['rol'];
                $this->libros_leidos = $row['libros_leidos'];
                $this->fecha_registro = $row['fecha_registro'];
                return true;
            }
        }
        return false;
    }

    public function correoExiste() {
        $query = "SELECT id_usuario FROM " . $this->table_name . " WHERE correo = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->correo);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

class Reseña {
    private $conn;
    private $table_name = "reseñas";

    public $id_reseña;
    public $id_libro;
    public $id_usuario;
    public $comentario;
    public $puntuacion;
    public $fecha_publicacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        if(empty($this->id_libro) || empty($this->id_usuario) || empty($this->comentario) || empty($this->puntuacion)) {
            return false;
        }
        
        if($this->puntuacion < 1 || $this->puntuacion > 5) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_libro=:id_libro, id_usuario=:id_usuario, 
                  comentario=:comentario, puntuacion=:puntuacion";
        
        $stmt = $this->conn->prepare($query);

        $this->comentario = htmlspecialchars(strip_tags($this->comentario));
        $this->id_libro = intval($this->id_libro);
        $this->id_usuario = intval($this->id_usuario);
        $this->puntuacion = intval($this->puntuacion);

        $stmt->bindParam(":id_libro", $this->id_libro);
        $stmt->bindParam(":id_usuario", $this->id_usuario);
        $stmt->bindParam(":comentario", $this->comentario);
        $stmt->bindParam(":puntuacion", $this->puntuacion);

        if($stmt->execute()) {
            $this->actualizarPuntuacionPromedio();
            return true;
        }
        return false;
    }

    private function actualizarPuntuacionPromedio() {
        $query = "UPDATE libros 
                  SET puntuacion_promedio = (
                      SELECT AVG(puntuacion) FROM reseñas WHERE id_libro = ?
                  ) 
                  WHERE id_libro = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_libro);
        $stmt->bindParam(2, $this->id_libro);
        $stmt->execute();
    }

    public function leerPorLibro() {
        $query = "SELECT r.*, u.nombre as usuario_nombre 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                  WHERE r.id_libro = ? 
                  ORDER BY r.fecha_publicacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_libro);
        $stmt->execute();
        return $stmt;
    }
}

class Categoria {
    private $conn;
    private $table_name = "categorias";

    public $id_categoria;
    public $nombre;
    public $descripcion;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function leer() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}

class Favorito {
    private $conn;
    private $table_name = "favoritos";

    public $id_favorito;
    public $id_libro;
    public $id_usuario;
    public $fecha_guardado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function agregar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_libro=:id_libro, id_usuario=:id_usuario";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_libro", $this->id_libro);
        $stmt->bindParam(":id_usuario", $this->id_usuario);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id_libro = :id_libro AND id_usuario = :id_usuario";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_libro", $this->id_libro);
        $stmt->bindParam(":id_usuario", $this->id_usuario);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

function mostrarEstrellas($puntuacion) {
    $html = '';
    $puntuacion = floatval($puntuacion);
    
    for($i = 1; $i <= 5; $i++) {
        if($i <= floor($puntuacion)) {
            $html .= '<i class="bi bi-star-fill"></i>';
        } elseif($i == ceil($puntuacion) && $puntuacion - floor($puntuacion) >= 0.5) {
            $html .= '<i class="bi bi-star-half"></i>';
        } else {
            $html .= '<i class="bi bi-star"></i>';
        }
    }
    return $html;
}

function obtenerImagenLibro($imagen) {
    if (!empty($imagen)) {
        $rutas_posibles = [
            $imagen,
            'images/libros/' . $imagen,
            'uploads/' . $imagen,
            'assets/images/libros/' . $imagen,
            'img/' . $imagen
        ];
        
        foreach ($rutas_posibles as $ruta) {
            if (file_exists($ruta)) {
                return $ruta;
            }
        }
    }
    
    return 'images/libros/default.jpg';
}

function esFavorito($db, $id_libro, $id_usuario) {
    if(!$id_usuario) return false;
    
    $query = "SELECT id_favorito FROM favoritos WHERE id_libro = ? AND id_usuario = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id_libro, $id_usuario]);
    return $stmt->rowCount() > 0;
}

function obtenerFavoritos($db, $id_usuario) {
    if(!$id_usuario) return [];
    
    $query = "SELECT l.*, c.nombre as categoria_nombre 
              FROM favoritos f 
              JOIN libros l ON f.id_libro = l.id_libro 
              LEFT JOIN categorias c ON l.categoria_id = c.id_categoria 
              WHERE f.id_usuario = ? 
              ORDER BY f.fecha_guardado DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$id_usuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerReseñasRecientes($db, $limite = 5) {
    $query = "SELECT r.*, u.nombre as usuario_nombre, l.titulo as libro_titulo 
              FROM reseñas r 
              JOIN usuarios u ON r.id_usuario = u.id_usuario 
              JOIN libros l ON r.id_libro = l.id_libro 
              ORDER BY r.fecha_publicacion DESC 
              LIMIT ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerEstadisticasUsuario($db, $usuario_id) {
    $estadisticas = [
        'total_resenas' => 0,
        'total_favoritos' => 0,
        'total_leidos' => 0,
        'total_agregados' => 0
    ];
    
    try {
        // Contar reseñas
        $query = "SELECT COUNT(*) as total FROM reseñas WHERE id_usuario = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$usuario_id]);
        $estadisticas['total_resenas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Contar favoritos
        $query = "SELECT COUNT(*) as total FROM favoritos WHERE id_usuario = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$usuario_id]);
        $estadisticas['total_favoritos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Contar libros leídos
        $query = "SELECT COUNT(*) as total FROM usuario_libros WHERE id_usuario = ? AND estado = 'leido'";
        $stmt = $db->prepare($query);
        $stmt->execute([$usuario_id]);
        $estadisticas['total_leidos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Contar libros agregados
        $query = "SELECT COUNT(*) as total FROM libros WHERE id_usuario_agrego = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$usuario_id]);
        $estadisticas['total_agregados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
    } catch (PDOException $e) {
        // En caso de error, mantener los valores por defecto
    }
    
    return $estadisticas;
}

function limpiarDatos($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function esAdministrador() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'admin';
}

function mostrarMensaje() {
    if(isset($_SESSION['mensaje'])) {
        echo '<div class="alert alert-info alert-dismissible fade show" role="alert">';
        echo $_SESSION['mensaje'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['mensaje']);
    }
}
?>