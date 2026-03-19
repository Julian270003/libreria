<?php
include_once 'config.php';

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['libro_id'])) {
    header("Location: libros.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$libro_id = intval($_GET['libro_id']);

$libro = new Libro($db);
$libro->id_libro = $libro_id;

if(!$libro->leerUno()) {
    header("Location: libros.php");
    exit();
}

$query = "SELECT id_favorito FROM favoritos WHERE id_libro = ? AND id_usuario = ?";
$stmt = $db->prepare($query);
$stmt->execute([$libro_id, $_SESSION['usuario_id']]);

if($stmt->rowCount() > 0) {
    $query = "DELETE FROM favoritos WHERE id_libro = ? AND id_usuario = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$libro_id, $_SESSION['usuario_id']]);
    $_SESSION['mensaje'] = "Libro removido de favoritos";
} else {
    $query = "INSERT INTO favoritos (id_libro, id_usuario) VALUES (?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$libro_id, $_SESSION['usuario_id']]);
    $_SESSION['mensaje'] = "Libro agregado a favoritos";
}

if(isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: libros.php");
}
exit();
?>