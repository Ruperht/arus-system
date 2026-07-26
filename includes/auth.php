<?php

session_start(); //---------------------------------------------------------- Inicia o recupera la sesión del usuario.

function iniciarSesion(array $usuario): void //------------------------------ Recibe un array con los datos del usuario que acaba de iniciar sesión. Guarda en la sesión los datos básicos del usuario autenticado.
{
    session_regenerate_id(true); //------------------------------------------ Regenera el ID de sesión para evitar ataques de fijación de sesión, donde alguien intenta reutilizar un identificador anterior. Cambia la cerradura después de entrar en casa.

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['nombre'] = $usuario['nombre'];
}

function estaLogueado(): bool //--------------------------------------------- Comprueba si existe un usuario autenticado. Devuelve true (logueado) o false (no logueado).
{
    return isset($_SESSION['usuario_id']);
}

function requiereLogin(): void //-------------------------------------------- Impide acceder a una página si el usuario no ha iniciado sesión.
{
    if (!estaLogueado()) {
        header('Location: /MY_PROJECTS/ProyectoDAM/public/login.php'); //---- Si no está logueado, redirige a la página de login.
        exit;
    }
}

function requiereRol(string $rolRequerido): void //-------------------------- Comprueba que el usuario autenticado tiene el rol solicitado.
{
    requiereLogin();

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rolRequerido) { //- Si no coincide el rol del usuario con el rol requerido, deniega el acceso.
        http_response_code(403); //------------------------------------------ Este error significa "Acceso prohibido".
        exit('No tienes permiso para acceder a esta página.');
    }
}

function cerrarSesion(): void //--------------------------------------------- Elimina todos los datos de la sesión y cierra la sesión del usuario.
{
    $_SESSION = []; //------------------------------------------------------- Vacía todos los datos guardados en la sesión.

    if (ini_get('session.use_cookies')) { //--------------------------------- Elimina también la cookie de sesión del navegador.
        $parametrosCookie = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametrosCookie['path'],
            $parametrosCookie['domain'],
            $parametrosCookie['secure'],
            $parametrosCookie['httponly']
        );
    }

    session_destroy(); //----------------------------------------------------- Destruye la sesión en el servidor, eliminando todos los datos asociados a ella.
}