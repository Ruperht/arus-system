<?php
require_once '../includes/auth.php'; //------------------------------------------------- Incluye el archivo de autenticación para manejar la sesión y las funciones relacionadas con el login.
require_once '../config/db.php'; //----------------------------------------------------- Incluye el archivo de configuración de la base de datos para poder realizar consultas SQL.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { //---------------------------------------- Si se abre login.php directamente, vuelve al index y solicita abrir el popup.
    header('Location: /MY_PROJECTS/ProyectoDAM/index.php?login=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //----------------------------------------- Comprueba si el formulario ha sido enviado mediante el método POST. Cuando pulsa Iniciar sesión, se envía un POST con los datos del formulario.
    $email = trim($_POST['email'] ?? ''); //-------------------------------------------- Obtiene el email del formulario, eliminando espacios en blanco al inicio y al final. Si no se envía, asigna una cadena vacía.
    $password = $_POST['password'] ?? ''; //-------------------------------------------- Obtiene la contraseña del formulario. Si no se envía, asigna una cadena vacía.
    $rol = $_POST['rol'] ?? ''; //---------------------------------------------------------- Obtiene el rol seleccionado en el popup. Si no se envía, asigna una cadena vacía.

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($rol, ['admin', 'cliente', 'candidato', 'worker'], true)) { //-------------------------------- Validamos el formato del email y el rol.
        $_SESSION['login_error'] = 'Rol, email o contraseña incorrectos.'; //---------------- Guarda un mensaje genérico para no revelar qué credencial es incorrecta.
        $_SESSION['login_email'] = $email; //-------------------------------------------------- Conserva temporalmente el email introducido.
        $_SESSION['login_role'] = $rol; //----------------------------------------------------- Conserva temporalmente el rol seleccionado.
        header('Location: /MY_PROJECTS/ProyectoDAM/index.php?login=1'); //-------------------- Vuelve al index y reabre el popup mostrando el error.
        exit;
    } else {

        $stmt = $pdo->prepare("
            SELECT id, nombre, email, password_hash, rol
            FROM usuarios
            WHERE email = ? AND rol = ?
            LIMIT 1
        "); //-------------------------------------------------------------------------- Prepara una consulta SQL.
        $stmt->execute([$email, $rol]); //--------------------------------------------- Sustituye de forma segura los interrogantes por el email y el rol seleccionados.
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
                $_SESSION['login_error'] = 'Rol, email o contraseña incorrectos.';
                $_SESSION['login_email'] = $email;
                $_SESSION['login_role'] = $rol;
                header('Location: /MY_PROJECTS/ProyectoDAM/index.php?login=1');
                exit;
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
            $_SESSION['login_error'] = 'Rol, email o contraseña incorrectos.';
            $_SESSION['login_email'] = $email;
            $_SESSION['login_role'] = $rol;
            header('Location: /MY_PROJECTS/ProyectoDAM/index.php?login=1');
            exit;
        }
    }
}