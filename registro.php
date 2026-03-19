<?php
include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$usuario = new Usuario($db);

$mensaje = '';

if($_POST){
    $usuario->nombre = $_POST['nombre'];
    $usuario->correo = $_POST['correo'];
    $usuario->password = $_POST['password'];
    
    if($usuario->correoExiste()){
        $mensaje = '<div class="alert alert-danger">El correo electrónico ya está registrado.</div>';
    } else {
        if($usuario->registrar()){
            $mensaje = '<div class="alert alert-success">Registro exitoso. Ahora puedes iniciar sesión.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error en el registro.</div>';
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Registrarse</h3>
                    
                    <?php echo $mensaje; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>