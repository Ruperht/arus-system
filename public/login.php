<?php
require_once '../includes/auth.php'; //------------------------------------------------- Incluye el archivo de autenticación para manejar la sesión y las funciones relacionadas con el login.
require_once '../config/db.php'; //----------------------------------------------------- Incluye el archivo de configuración de la base de datos para poder realizar consultas SQL.

$error = ''; //------------------------------------------------------------------------- Crea una variable vacía donde se guardará cualquier mensaje de error.

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //----------------------------------------- Comprueba si el formulario ha sido enviado mediante el método POST. Cuando pulsa Iniciar sesión, se envía un POST con los datos del formulario.
    $email = trim($_POST['email'] ?? ''); //-------------------------------------------- Obtiene el email del formulario, eliminando espacios en blanco al inicio y al final. Si no se envía, asigna una cadena vacía.
    $password = $_POST['password'] ?? ''; //-------------------------------------------- Obtiene la contraseña del formulario. Si no se envía, asigna una cadena vacía.

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //-------------------------------- Validamos el formato del email.
        $error = 'Email o contraseña incorrectos.';
    } else {

        $stmt = $pdo->prepare("
            SELECT id, email, password_hash, rol
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        "); //-------------------------------------------------------------------------- Prepara una consulta SQL.
        $stmt->execute([$email]); //---------------------------------------------------- Sustituye de forma segura el ? por el valor de $email.
        $usuario = $stmt->fetch(); //--------------------------------------------------- Obtiene el primer resultado de la consulta, que será un array asociativo con los datos del usuario.

        if ($usuario && password_verify($password, $usuario['password_hash'])) { //----- Comprueba si el usuario y la contraseña escrita coinciden con el hash guardado en la base de datos.

            $rutas = [
                'admin' => '/MY_PROJECTS/ProyectoDAM/admin/dashboard.php',
                'cliente' => '/MY_PROJECTS/ProyectoDAM/cliente/dashboard.php',
                'candidato' => '/MY_PROJECTS/ProyectoDAM/candidato/dashboard.php',
                'worker' => '/MY_PROJECTS/ProyectoDAM/worker/dashboard.php'
            ];  //---------------------------------------------------------------------- Definimos las rutas permitidas según el rol

            // Comprobamos que el rol tenga una ruta permitida
            if (!isset($rutas[$usuario['rol']])) { //----------------------------------- Busca si el rol del usuario existe como clave dentro de $rutas.
                $error = 'El rol del usuario no es válido.';
            } else {
                iniciarSesion($usuario); //--------------------------------------------- Llama a la función iniciarSesion() para guardar los datos del usuario en la sesión y regenerar el ID de sesión.

                $pdo->prepare("
                    UPDATE usuarios
                    SET ultima_actividad = NOW()
                    WHERE id = ?
                ")->execute([$usuario['id']]);  //-------------------------------------- Actualizamos la última actividad con consulta SQL y ejecutamos la consulta sustituyendo el ? por el ID del usuario.

                header('Location: ' . $rutas[$usuario['rol']]); //---------------------- Redirige al usuario a la ruta correspondiente según su rol.
                exit;
            }
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión — ARUS SYSTEM</title>
</head>

<body>
    <h2>Iniciar sesión</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="email">Email:</label>
        <input
            id="email"
            type="email"
            name="email"
            value="<?= htmlspecialchars($email ?? '') ?>"
            autocomplete="email"
            required>

        <label for="password">Contraseña:</label>
        <input
            id="password"
            type="password"
            name="password"
            autocomplete="current-password"
            required>

        <button type="submit">Iniciar sesión</button>
    </form>
</body>

</html>