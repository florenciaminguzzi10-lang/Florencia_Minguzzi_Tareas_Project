<?php
require_once __DIR__ . '/conexion.php';

$errores = [];
$mensaje = '';
$nombre = '';
$apellido = '';
$dni = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validaciones
    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($apellido === '') {
        $errores[] = 'El apellido es obligatorio.';
    }
    if ($dni === '' || !preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }

    // Si no hay errores, guardamos en la base de datos
    if (empty($errores)) {
        try {
            $sql = 'INSERT INTO estudiantes (nombre, apellido, dni, email) VALUES (:nombre, :apellido, :dni, :email)';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $dni,
                'email' => $email
            ]);
            $mensaje = 'Estudiante registrado correctamente.';
            
            // Limpiamos las variables
            $nombre = '';
            $apellido = '';
            $dni = '';
            $email = '';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores[] = 'Ese DNI ya está registrado.';
            } else {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
        }
    }
}

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Estudiantes</title>
</head>
<body>
    <h1>Registro de Estudiantes</h1>

    <?php if ($mensaje !== ''): ?>
        <p style="color: green;"><?= escapar($mensaje) ?></p>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <ul style="color: red;">
            <?php foreach ($errores as $error): ?>
                <li><?= escapar($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" value="<?= escapar($nombre) ?>" maxlength="80" required><br><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" value="<?= escapar($apellido) ?>" maxlength="80" required><br><br>

        <label for="dni">DNI:</label><br>
        <input type="text" id="dni" name="dni" value="<?= escapar($dni) ?>" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" required><br><br>

        <label for="email">Correo electrónico:</label><br>
        <input type="email" id="email" name="email" value="<?= escapar($email) ?>" maxlength="254" required><br><br>

        <button type="submit">Registrar estudiante</button>
    </form>
</body>
</html>