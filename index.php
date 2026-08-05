<?php
require 'config/db.php'; //----------------------------------------------------------------- Llamada a la base de datos.
require_once 'includes/auth.php';

$loginError = $_SESSION['login_error'] ?? '';
$loginEmail = $_SESSION['login_email'] ?? '';
$loginRole = $_SESSION['login_role'] ?? '';

unset(
    $_SESSION['login_error'],
    $_SESSION['login_email'],
    $_SESSION['login_role']
);
$titulo_pagina = 'Inicio'; //--------------------------------------------------------------- Título de la página, antes del header para que se pueda usar en el <title> de la etiqueta <head>.
require 'includes/header.php'; //----------------------------------------------------------- Llamada al header.


$servicios = $pdo->query(" 
    SELECT nombre, precio_base, descripcion 
    FROM servicios
    WHERE mostrar_en_home = 1
    ORDER BY id ASC
")->fetchAll(); //-------------------------------------------------------------------------- Solo se muestran los servicios que tienen mostrar_en_home = 1. fetchAll() devuelve todas las columnas de cada fila como un array asociativo.  
?>

<!-- ============ POPUP INICIO DE SESIÓN ============ -->
<div class="login-modal<?= isset($_GET['login']) ? ' visible' : '' ?>"
    id="loginModal"
    aria-hidden="<?= isset($_GET['login']) ? 'false' : 'true' ?>">
    <div class="login-modal-backdrop" data-login-close></div>

    <div class="login-modal-content" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">
        <button type="button" class="login-modal-close" aria-label="Cerrar" data-login-close>&times;</button>

        <h2 id="loginModalTitle">Iniciar sesión</h2>
        <?php if ($loginError): ?>
            <p class="login-error"><?= htmlspecialchars($loginError) ?></p>
        <?php endif; ?>

        <form method="post" action="public/login.php">
            <div class="form-group">
                <label for="loginRol">Rol</label>
                <select id="loginRol" name="rol" required>
                    <option value="">Selecciona tu rol</option>
                    <option value="admin" <?= $loginRole === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    <option value="cliente" <?= $loginRole === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="candidato" <?= $loginRole === 'candidato' ? 'selected' : '' ?>>Candidato</option>
                    <option value="worker" <?= $loginRole === 'worker' ? 'selected' : '' ?>>Trabajador</option>
                </select>
            </div>

            <div class="form-group">
                <label for="loginEmail">Email</label>
                <input id="loginEmail" type="email" name="email" value="<?= htmlspecialchars($loginEmail) ?>" autocomplete="email" required>
            </div>

            <div class="form-group">
                <label for="loginPassword">Contraseña</label>
                <input id="loginPassword" type="password" name="password" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn-primary login-modal-submit">Iniciar sesión</button>
        </form>
    </div>
</div>

<!-- ============ HERO ============ -->
<section class="hero" id="hero">
    <div class="hero-bg" data-speed="0.2"></div> <!----------------------------------------- Efecto parallax en el fondo del hero. Indica la velocidad de movimiento del fondo. -->

    <div class="hero-content">
        <h1>Desarrollamos la tecnología que tu negocio necesita</h1>
        <p>Software a medida, páginas web y automatizaciones — con un equipo que se queda contigo después de entregar el proyecto.</p>
        <div class="hero-actions">
            <a href="public/solicitar-servicio.php" class="btn-primary">Solicitar servicio</a>
            <a href="#servicios" class="btn-secondary">Ver qué hacemos</a>
        </div>
    </div>

    <div class="robot-wrap" id="robotWrap">
        <img src="assets/img/robot.png" alt="Asistente ARUS SYSTEM" draggable="false"> <!--- draggable="false" evita que el usuario pueda arrastrar la imagen del robot. -->
    </div>

    <div class="scroll-hint">Desplázate para descubrir más ↓</div>
</section>

<!-- ============ SERVICIOS ============ -->
<section class="servicios" id="servicios">
    <div class="section-decor" data-speed="-0.05"></div> <!--------------------------------- Efecto parallax en el fondo de la sección. Indica la velocidad de movimiento del fondo. -->
    <h2>Qué hacemos</h2>
    <div class="servicios-grid">
        <?php foreach ($servicios as $servicio): ?> <!-------------------------------------- Recorre el array de servicios y genera una tarjeta para cada uno. -->
            <div class="servicio-card">
                <h3><?= htmlspecialchars($servicio['nombre']) ?></h3>
                <p class="precio-desde">
                    <?= $servicio['precio_base'] !== null
                        ? 'Desde ' . number_format($servicio['precio_base'], 0, ',', '.') . ' €'
                        : 'Presupuesto a medida' ?>
                </p>
                <p class="servicio-desc"><?= htmlspecialchars($servicio['descripcion'] ?? '') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="nota-iva">Precios sin IVA (21%). El importe final se detalla en el presupuesto.</p>
</section>

<!-- ============ PLANES ============ -->
<section class="planes" id="planes">
    <div class="section-decor" data-speed="-0.05"></div> <!---------------------------------- Efecto parallax en el fondo de la sección. Indica la velocidad de movimiento del fondo. -->
    <h2>Planes de mantenimiento</h2>
    <?php
    $planes = $pdo->query("
        SELECT nombre, precio, descripcion, grupo 
        FROM planes 
        ORDER BY grupo DESC, precio ASC
    ")->fetchAll();
    $planes_principales = array_filter($planes, fn($plan) => $plan['grupo'] === 'principal');
    $planes_adicionales = array_filter($planes, fn($plan) => $plan['grupo'] === 'adicional');
    ?>
    <div class="planes-grid">
        <?php foreach ($planes_principales as $plan): ?>
            <div class="plan-card">
                <h3><?= htmlspecialchars($plan['nombre']) ?></h3>
                <p class="plan-precio"><?= number_format($plan['precio'], 2, ',', '.') ?> €/mes</p>
                <ul class="plan-lista">
                    <?php foreach (explode("\n", $plan['descripcion']) as $item): ?>
                        <?php if (trim($item) !== ''): ?>
                            <li><?= htmlspecialchars(trim($item)) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="planes-grid planes-grid-secundaria">
        <?php foreach ($planes_adicionales as $plan): ?>
            <div class="plan-card">
                <h3><?= htmlspecialchars($plan['nombre']) ?></h3>
                <p class="plan-precio"><?= number_format($plan['precio'], 2, ',', '.') ?> €/mes</p>
                <ul class="plan-lista">
                    <?php foreach (explode("\n", $plan['descripcion']) as $item): ?>
                        <?php if (trim($item) !== ''): ?>
                            <li><?= htmlspecialchars(trim($item)) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="nota-iva">Precios sin IVA (21%). El importe final se detalla en el presupuesto.</p>
</section>

<?php require 'includes/footer.php'; ?> <!---------------------------------------------------- Llamada al footer -->