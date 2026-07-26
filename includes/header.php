<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) . ' — ARUS SYSTEM' : 'ARUS SYSTEM' ?></title>
    <link rel="stylesheet" href="/MY_PROJECTS/ProyectoDAM/assets/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/MY_PROJECTS/ProyectoDAM/assets/css/style.css') ?>"> <!-- Cache busting: agrega un parámetro de versión basado en la fecha de modificación del archivo CSS para forzar la recarga del navegador cuando el archivo cambie. -->
</head>
<body>

    <header class="site-header" id="siteHeader">
        <div class="header-inner">
            <!------------------------------------------------------------------------------- Si pulsas el logo, vuelve al inicio.-->
            <a href="/MY_PROJECTS/ProyectoDAM/index.php" class="logo"> 
                <img src="/MY_PROJECTS/ProyectoDAM/assets/img/logo.png" alt="ARUS SYSTEM">
            </a>

            <nav class="main-nav">
                <a href="/MY_PROJECTS/ProyectoDAM/index.php#servicios">Servicios</a>
                <a href="/MY_PROJECTS/ProyectoDAM/index.php#planes">Planes</a>
                <a href="/MY_PROJECTS/ProyectoDAM/public/candidatura.php">Trabaja con nosotros</a>
                <a href="/MY_PROJECTS/ProyectoDAM/public/login.php" class="btn-nav">Iniciar sesión</a>
            </nav>
        </div>
    </header>
    