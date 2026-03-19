<?php
include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$usuario = new Usuario($db);

$mensaje = '';

if($_POST){
    $usuario->correo = $_POST['correo'];
    $usuario->password = $_POST['password'];
    
    if($usuario->login()){
        $_SESSION['usuario_id'] = $usuario->id_usuario;
        $_SESSION['usuario_nombre'] = $usuario->nombre;
        $_SESSION['usuario_correo'] = $usuario->correo;
        $_SESSION['usuario_rol'] = $usuario->rol;
        
        header("Location: index.php");
        exit();
    } else {
        $mensaje = '<div class="alert alert-danger">Correo o contraseña incorrectos.</div>';
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Iniciar Sesión</h3>
                    
                    <?php echo $mensaje; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>