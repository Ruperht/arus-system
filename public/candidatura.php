<?php
require '../includes/auth.php'; //----------------------------------------------------- Incluye las funciones de autenticación y arranca la sesión para poder usar variables de sesión.
require '../config/db.php'; //--------------------------------------------------------- Incluye la conexión PDO con la base de datos.
require_once '../includes/validaciones.php'; //----------------------------------------------- Incluye funciones de validación de documentos y otros datos.

$titulo_pagina = 'Enviar Candidatura'; //--------------------------------------------- Define el título que utilizará el header en la etiqueta <title>.
$errores = []; //----------------------------------------------------------------------- Crea un array vacío donde se guardarán los mensajes de error.
$valores = [ //-------------------------------------------------------------------------- Guarda los valores del formulario para validarlos y conservarlos tras un error.
    'tipo_identificador' => 'dni', //--------------------------------------------------- Tipo de documento seleccionado por defecto.
    'nif_cif'      => '', //------------------------------------------------------------ Documento identificativo introducido.
    'nombre'       => '', //------------------------------------------------------------ Nombre y apellidos del candidato.
    'direccion'    => '', //------------------------------------------------------------ Dirección postal del candidato.
    'descripcion'  => '', //------------------------------------------------------------ Motivaciones o presentación inicial del candidato.
    'email'        => '', //------------------------------------------------------------ Correo electrónico de contacto.
    'prefijo_telefono' => '+34', //----------------------------------------------------- Prefijo internacional seleccionado por defecto.
    'telefono'     => '', //------------------------------------------------------------ Número de teléfono opcional.
    'telefono_completo' => null, //----------------------------------------------------- Guarda temporalmente el teléfono ya normalizado con prefijo para insertarlo en la base de datos.
];


// Trae todos los prefijos telefónicos activos para generar el desplegable.
$prefijosTelefonicos = $pdo->query("
    SELECT pais, codigo, bandera
    FROM prefijos_telefonicos
    WHERE activo = 1
    ORDER BY orden ASC, pais ASC
")->fetchAll(); // Guarda el resultado en un array para recorrerlo posteriormente.

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //--------------------------------------- Comprueba si el formulario se ha enviado mediante POST.

    // Recogemos y limpiamos los datos del formulario.
    $valores['tipo_identificador'] = $_POST['tipo_identificador'] ?? 'dni'; //--------- Recoge el tipo de documento seleccionado.
    $valores['nif_cif']      = trim($_POST['nif_cif'] ?? ''); //----------------------- Recoge el documento y elimina espacios laterales.
    $valores['nombre']       = trim($_POST['nombre'] ?? ''); //------------------------ Recoge el nombre y elimina espacios laterales.
    $valores['direccion']    = trim($_POST['direccion'] ?? ''); //--------------------- Recoge la dirección y elimina espacios laterales.
    $valores['descripcion']  = trim($_POST['descripcion'] ?? ''); //------------------- Recoge las motivaciones o presentación del candidato.
    $valores['email']        = trim($_POST['email'] ?? ''); //------------------------- Recoge el correo electrónico.
    $valores['prefijo_telefono'] = trim($_POST['prefijo_telefono'] ?? '+34'); //------- Recoge el prefijo internacional seleccionado.
    $valores['telefono']     = trim($_POST['telefono'] ?? ''); //---------------------- Recoge el teléfono opcional.
    $valores['telefono_completo'] = null; //-------------------------------------------- Reinicia el teléfono normalizado en cada envío.

    // --- Validaciones básicas ---
    $valores['nif_cif'] = strtoupper($valores['nif_cif']); //------------------------- Convierte el documento a mayúsculas antes de validarlo.

    if (!in_array($valores['tipo_identificador'], ['dni', 'nie'], true)) { //------------ En una candidatura solo se permiten documentos de persona física.
        $errores[] = 'Selecciona un tipo de documento válido.'; //---------------------- Añade un error si el tipo de documento no es válido.
    } elseif ($valores['nif_cif'] === '') { //----------------------------------------- Comprueba que el campo del documento no esté vacío.
        $errores[] = 'El documento identificativo es obligatorio.'; //------------------ Añade un error si no se ha escrito ningún documento.
    } elseif ($valores['tipo_identificador'] === 'dni' && !validarDni($valores['nif_cif'])) { // Valida el formato y la letra del DNI.
        $errores[] = 'El DNI introducido no es válido.'; //----------------------------- Añade un error si el DNI no supera la validación.
    } elseif ($valores['tipo_identificador'] === 'nie' && !validarNie($valores['nif_cif'])) { // Valida el formato y la letra del NIE.
        $errores[] = 'El NIE introducido no es válido.'; //----------------------------- Añade un error si el NIE no supera la validación.
    }

    if ($valores['nombre'] === '') { //------------------------------------------------- Comprueba que el nombre y apellidos sean obligatorios.
        $errores[] = 'El nombre y apellidos son obligatorios.'; //---------------------- Añade un error si el candidato no introduce su nombre.
    }

    if ($valores['direccion'] === '') { //---------------------------------------------- Comprueba que la dirección sea obligatoria.
        $errores[] = 'La dirección es obligatoria.'; //--------------------------------- Añade un error si el candidato no introduce su dirección.
    }

    if ($valores['descripcion'] === '') { //------------------------------------------- Comprueba que el candidato explique sus motivaciones.
        $errores[] = 'Cuéntanos brevemente cuáles son tus motivaciones.'; //------------ Añade un error si no se explican las motivaciones.
    }

    if ($valores['email'] === '' || !filter_var($valores['email'], FILTER_VALIDATE_EMAIL)) { // Comprueba que el email exista y tenga un formato válido.
        $errores[] = 'Introduce un correo electrónico válido.'; //---------------------- Añade un error si el correo está vacío o mal formado.
    }

    if ($valores['telefono'] !== '') { //------------------------------------------------ Valida el teléfono solo cuando el usuario decide rellenarlo.
        if (!preg_match('/^\+[0-9]{1,4}$/', $valores['prefijo_telefono'])) { //---------- Comprueba que el prefijo empiece por + y tenga entre uno y cuatro números.
            $errores[] = 'Selecciona un prefijo telefónico válido.'; //------------------ Añade un error si el prefijo no tiene un formato correcto.
        }

        $telefonoLimpio = preg_replace('/[\s\-().]/', '', $valores['telefono']); //------ Elimina espacios, guiones, puntos y paréntesis antes de validar.

        if (!preg_match('/^[0-9]{6,15}$/', $telefonoLimpio)) { //------------------------ Comprueba que el número tenga entre seis y quince dígitos.
            $errores[] = 'Introduce un número de teléfono válido.'; //------------------- Añade un error si el teléfono no tiene una longitud válida.
        } else {
            $valores['telefono_completo'] = $valores['prefijo_telefono'] . $telefonoLimpio; // Guarda el teléfono completo sin alterar el valor visible del formulario.
        }
    }

    // --- Comprobación anti-duplicados por DNI/NIE ---
    if (empty($errores)) { //----------------------------------------------------------- Solo consulta la base de datos si no existen errores previos.
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nif_cif = ?"); //----------- Prepara una consulta segura para buscar documentos duplicados.
        $stmt->execute([$valores['nif_cif']]); //--------------------------------------- Sustituye el interrogante por el documento introducido.

        if ($stmt->fetch()) { //--------------------------------------------------------- Comprueba si ya existe un usuario con ese documento.
            $errores[] = 'Ya existe una cuenta con este DNI/NIE. <a href="login.php">Inicia sesión</a> o <a href="recuperar-password.php">recupera tu contraseña</a>.'; // Informa de que ya existe una cuenta y ofrece accesos alternativos.
        }
    }

    // --- Si todo va bien, creamos el usuario ---
    if (empty($errores)) { //----------------------------------------------------------- Solo crea los registros si todas las validaciones son correctas.
        try {
            $pdo->beginTransaction(); //------------------------------------------------ Inicia una transacción para crear conjuntamente el usuario y su candidatura.

            // Prepara la inserción del nuevo candidato.
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (rol, tipo_persona, nif_cif, nombre, email, telefono, estado_perfil, fecha_alta, ultima_actividad)
                VALUES ('candidato', 'fisica', ?, ?, ?, ?, 'pendiente_completar', NOW(), NOW())
            ");
            $stmt->execute([ //---------------------------------------------------------- Ejecuta la inserción del candidato.
                $valores['nif_cif'], //-------------------------------------------------- Guarda el DNI o NIE.
                $valores['nombre'], //--------------------------------------------------- Guarda el nombre y apellidos.
                $valores['email'], //---------------------------------------------------- Guarda el correo electrónico.
                $valores['telefono_completo'], //--------------------------------------- Guarda el teléfono normalizado o null si se dejó vacío.
            ]);

            $usuario_id = $pdo->lastInsertId(); //-------------------------------------- Obtiene el ID del usuario recién creado.

            // Prepara la inserción de la candidatura vinculada al usuario recién creado.
            $stmt = $pdo->prepare("
                INSERT INTO candidaturas (usuario_id, direccion, worker_id, cv_url, presentacion, estado, fecha)
                VALUES (?, ?, NULL, NULL, ?, 'recibida', NOW())
            ");
            $stmt->execute([ //---------------------------------------------------------- Ejecuta la inserción de la candidatura.
                $usuario_id, //---------------------------------------------------------- Relaciona la candidatura con el usuario candidato.
                $valores['direccion'], //------------------------------------------------ Guarda la dirección del candidato.
                $valores['descripcion'], //-------------------------------------------- Guarda las motivaciones o presentación inicial del candidato.
            ]);
            $pdo->commit(); //---------------------------------------------------------- Confirma la transacción y guarda definitivamente el usuario y su candidatura.

            // Guardamos temporalmente a quién hay que activar. Todavía no es una sesión de login.
            $_SESSION['activar_usuario_id'] = $usuario_id; //-------------------------- Guarda temporalmente el ID para crear la contraseña del usuario.

            header('Location: crear-password.php'); //-------------------------------- Redirige a la página donde el usuario crea su contraseña.
            exit; //-------------------------------------------------------------------- Finaliza el script después de la redirección.

        } catch (PDOException $e) { //--------------------------------------------------- Captura cualquier error producido durante las operaciones con la base de datos.
            $pdo->rollBack(); //-------------------------------------------------------- Revierte la transacción para no guardar datos incompletos.
            $errores[] = 'No se ha podido procesar la candidatura. Inténtalo de nuevo en unos minutos.'; // Muestra un mensaje genérico sin revelar detalles internos.
        }
    }
}

require '../includes/header.php'; //--------------------------------------------------- Incluye la cabecera común de la aplicación.
?>

<section class="form-page"> <!---------------------------------------------------------- Contiene toda la página del formulario. -->
    <div class="form-card"> <!--------------------------------------------------------- Crea la tarjeta visual donde se muestra el formulario. -->
        <h1>¿Quieres unirte a nuestro equipo?</h1> <!--------------------------------------------- Muestra el título principal de la página. -->
        <p class="form-intro">Mándanos tu solicitud y revisaremos si encaja en alguna de nuestras oportunidades. Al enviarlo, se creará tu cuenta para que puedas seguir el estado de tu solicitud.</p>

        <?php if (!empty($errores)): ?> <!---------------------------------------------- Comprueba si existen errores de validación. -->
            <div class="form-errores"> <!----------------------------------------------- Muestra visualmente los errores encontrados. -->
                <ul>
                    <?php foreach ($errores as $error): ?> <!---------------------------- Recorre todos los mensajes de error. -->
                        <li><?= $error /* algunos mensajes incluyen enlaces, por eso no usamos htmlspecialchars aquí */ ?></li> <!-- Muestra cada error dentro de una lista. -->
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" id="formCandidatura"> <!-------------------------------------- Formulario de candidatura. -->

            <div class="form-group" id="grupoTipoIdentificador"> <!------------------- Agrupa las opciones de tipo de documento. -->
                <label>Tipo de documento</label>
                <div class="form-radio-group"> <!-------------------------------------- Coloca las opciones DNI y NIE en un mismo grupo. -->
                    <label class="form-radio" id="opcionDni">
                        <input type="radio" name="tipo_identificador" value="dni"
                            <?= $valores['tipo_identificador'] === 'dni' ? 'checked' : '' ?>> <!---- Envía el valor dni cuando esta opción está seleccionada. -->
                        DNI
                    </label>
                    <label class="form-radio" id="opcionNie">
                        <input type="radio" name="tipo_identificador" value="nie"
                            <?= $valores['tipo_identificador'] === 'nie' ? 'checked' : '' ?>> <!---- Envía el valor nie cuando esta opción está seleccionada. -->
                        NIE
                    </label>
                </div>
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa la etiqueta y el campo del documento. -->
                <label for="nif_cif" id="labelIdentificador">DNI</label> <!------------- La etiqueta cambia dinámicamente según el documento elegido. -->
                <input type="text" id="nif_cif" name="nif_cif" maxlength="9"
                    value="<?= htmlspecialchars($valores['nif_cif']) ?>" required> <!---- Limita el documento a un máximo de nueve caracteres. Conserva el valor y evita insertar HTML malicioso. -->
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa la etiqueta y el campo del nombre. -->
                <label for="nombre" id="labelNombre">Nombre y apellidos</label>
                <input type="text" id="nombre" name="nombre"
                    value="<?= htmlspecialchars($valores['nombre']) ?>" required> <!----- Campo donde se escribe el nombre y apellidos del candidato. Conserva el valor y obliga a rellenar el campo. -->
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa la etiqueta y el campo de dirección. -->
                <label for="direccion" id="labelDireccion">Dirección</label> <!------ Dirección postal del candidato. -->
                <input type="text" id="direccion" name="direccion"
                    value="<?= htmlspecialchars($valores['direccion']) ?>" required> <!----- Campo donde se escribe la dirección del candidato. Conserva el valor y obliga a rellenar el campo. -->
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa el área de descripción. -->
                <label for="descripcion">Cuéntanos más sobre tus motivaciones</label>
                <textarea id="descripcion" name="descripcion" rows="4" required><?= htmlspecialchars($valores['descripcion']) ?></textarea> <!-- Campo obligatorio para describir la solicitud. -->
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa el campo de correo electrónico. -->
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email"
                    value="<?= htmlspecialchars($valores['email']) ?>" required> <!------ El navegador realiza una primera comprobación del formato del email. Conserva el correo tras un error y obliga a rellenarlo. -->
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa la etiqueta y el campo del teléfono. -->
                <label for="telefono">Teléfono <span class="form-opcional">(opcional)</span></label> <!-- Indica que el teléfono no es obligatorio. -->

                <div class="telefono-group"> <!----------------------------------------- Agrupa el prefijo internacional y el número en una única línea. -->

                    <select id="prefijo_telefono" name="prefijo_telefono"> <!----------- Permite seleccionar el prefijo internacional del país. -->
                        <?php foreach ($prefijosTelefonicos as $prefijo): ?> <!--------- Recorre todos los prefijos recuperados de la base de datos. -->

                            <option
                                value="<?= htmlspecialchars($prefijo['codigo']) ?>"
                                <?= $valores['prefijo_telefono'] === $prefijo['codigo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prefijo['bandera'] . ' ' . $prefijo['pais'] . ' (' . $prefijo['codigo'] . ')') ?>
                            </option>
                            <!-- Muestra la bandera, el país y el prefijo internacional. -->

                        <?php endforeach; ?>
                    </select>

                    <input
                        type="tel"
                        id="telefono"
                        name="telefono"
                        value="<?= htmlspecialchars($valores['telefono']) ?>"
                        placeholder="Número de teléfono">
                    <!-- Campo donde el usuario introduce únicamente el número de teléfono. -->

                </div>
            </div>
            <p class="form-intro">En la siguiente página podrás crear tu contraseña para acceder a tu cuenta, subir tu CV y seguir el estado de tu solicitud. </p>
            <button type="submit" class="btn-primary form-submit">Enviar candidatura</button> <!-- Envía el formulario. -->
        </form>
    </div>
</section>

<script>
    // Cambia las etiquetas según el tipo de documento seleccionado.
    const labelIdentificador = document.getElementById('labelIdentificador'); //---------------- Busca la etiqueta que muestra DNI, NIE.

    function actualizarDocumento() { //----------------------------------------------------------- Actualiza las etiquetas y ejemplos según el documento elegido.
        const tipoDocumento = document.querySelector('input[name="tipo_identificador"]:checked')?.value || 'dni'; // Obtiene el documento seleccionado o usa DNI por defecto.
        const configuracion = { //---------------------------------------------------------------- Guarda la etiqueta y el ejemplo correspondiente a cada documento.
            dni: { //-------------------------------------------------------------------------------- Configuración visual para el DNI.
                label: 'DNI', //-------------------------------------------------------------------- Texto que se mostrará en la etiqueta.
                placeholder: 'Ej. 12345678Z' //----------------------------------------------------- Ejemplo que se mostrará dentro del campo.
            },
            nie: { //-------------------------------------------------------------------------------- Configuración visual para el NIE.
                label: 'NIE',
                placeholder: 'Ej. X1234567L'
            }
        };

        labelIdentificador.textContent = configuracion[tipoDocumento].label; //---------------------- Cambia el texto de la etiqueta del documento.
        document.getElementById('nif_cif').placeholder = configuracion[tipoDocumento].placeholder; // Cambia el ejemplo mostrado dentro del campo.
    }

    const radiosIdentificador = document.querySelectorAll('input[name="tipo_identificador"]'); //---- Busca las opciones de documento.
    radiosIdentificador.forEach(r => r.addEventListener('change', actualizarDocumento)); //----------- Ejecuta la actualización al cambiar de documento.
    actualizarDocumento(); //-------------------------------------------------------------------------- Ajusta el formulario correctamente al cargar la página.
</script>

<?php require '../includes/footer.php'; ?> <!------------------------------------------ Incluye el pie de página común y carga el JavaScript principal. -->