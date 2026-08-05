<?php
require '../includes/auth.php'; //----------------------------------------------------- Incluye las funciones de autenticación y arranca la sesión para poder usar variables de sesión.
require '../config/db.php'; //--------------------------------------------------------- Incluye la conexión PDO con la base de datos.

function validarDni(string $dni): bool //--------------------------------------------- Valida que un DNI tenga formato correcto y una letra de control válida.
{
    $dni = strtoupper(trim($dni)); //-------------------------------------------------- Elimina espacios laterales y convierte las letras a mayúsculas.

    if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) { //--------------------------------- Comprueba que el DNI tenga ocho números y una letra final.
        return false; //---------------------------------------------------------------- Devuelve falso si el formato no coincide.
    }

    $letras = 'TRWAGMYFPDXBNJZSQVHLCKE'; //----------------------------------------- Guarda la secuencia oficial de letras usada para calcular la letra del DNI.
    $numero = (int) substr($dni, 0, 8); //-------------------------------------------- Extrae los ocho números y los convierte a entero.

    return $dni[8] === $letras[$numero % 23]; //-------------------------------------- Compara la letra introducida con la calculada mediante el resto de dividir entre 23.
}

function validarNie(string $nie): bool //--------------------------------------------- Valida que un NIE tenga formato correcto y una letra de control válida.
{
    $nie = strtoupper(trim($nie)); //-------------------------------------------------- Elimina espacios laterales y convierte las letras a mayúsculas.

    if (!preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $nie)) { //----------------------------- Comprueba que empiece por X, Y o Z, tenga siete números y una letra final.
        return false; //---------------------------------------------------------------- Devuelve falso si el formato no coincide.
    }

    $prefijos = ['X' => '0', 'Y' => '1', 'Z' => '2']; //----------------------------- Relaciona la primera letra del NIE con el número usado para calcular su letra final.
    $numero = $prefijos[$nie[0]] . substr($nie, 1, 7); //----------------------------- Sustituye el prefijo por su número equivalente y lo une a los siete dígitos.
    $letras = 'TRWAGMYFPDXBNJZSQVHLCKE'; //----------------------------------------- Guarda la secuencia oficial de letras de control.

    return $nie[8] === $letras[((int) $numero) % 23]; //------------------------------ Compara la letra introducida con la letra calculada.
}

function validarCif(string $cif): bool //--------------------------------------------- Valida el formato y el carácter de control de un CIF.
{
    $cif = strtoupper(trim($cif)); //-------------------------------------------------- Elimina espacios laterales y convierte las letras a mayúsculas.

    if (!preg_match('/^[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J]$/', $cif)) { //------------ Comprueba que el CIF tenga una letra inicial válida, siete números y un carácter de control.
        return false; //---------------------------------------------------------------- Devuelve falso si el formato no coincide.
    }

    $sumaPares = 0; //----------------------------------------------------------------- Inicializa la suma de los dígitos situados en posiciones pares.
    $sumaImpares = 0; //--------------------------------------------------------------- Inicializa la suma de los dígitos situados en posiciones impares.

    for ($i = 1; $i <= 7; $i++) { //-------------------------------------------------- Recorre los siete dígitos numéricos del CIF.
        $digito = (int) $cif[$i]; //--------------------------------------------------- Convierte el carácter actual a número entero.

        if ($i % 2 === 0) { //--------------------------------------------------------- Comprueba si la posición recorrida es par.
            $sumaPares += $digito; //-------------------------------------------------- Suma directamente el dígito de una posición par.
        } else {
            $doble = $digito * 2; //--------------------------------------------------- Duplica el dígito de una posición impar.
            $sumaImpares += intdiv($doble, 10) + ($doble % 10); //-------------------- Suma por separado las dos cifras del resultado duplicado.
        }
    }

    $controlNumero = (10 - (($sumaPares + $sumaImpares) % 10)) % 10; //---------------- Calcula el dígito de control numérico del CIF.
    $controlLetra = 'JABCDEFGHI'[$controlNumero]; //---------------------------------- Obtiene la letra de control equivalente al número calculado.
    $controlIntroducido = $cif[8]; //-------------------------------------------------- Extrae el carácter de control introducido por el usuario.

    return $controlIntroducido === (string) $controlNumero || $controlIntroducido === $controlLetra; // Devuelve verdadero si coincide con el control numérico o alfabético.
}

$titulo_pagina = 'Solicitar servicio'; //--------------------------------------------- Define el título que utilizará el header en la etiqueta <title>.
$errores = []; //----------------------------------------------------------------------- Crea un array vacío donde se guardarán los mensajes de error.
$valores = [ //-------------------------------------------------------------------------- Guarda los valores del formulario para validarlos y conservarlos tras un error.
    'tipo_persona' => 'fisica', //------------------------------------------------------ Valor interno que distingue entre persona física y jurídica.
    'tipo_identificador' => 'dni', //--------------------------------------------------- Tipo de documento seleccionado por defecto.
    'nif_cif'      => '', //------------------------------------------------------------ Documento identificativo introducido.
    'nombre'       => '', //------------------------------------------------------------ Nombre completo o nombre de la empresa.
    'servicio_id'  => '', //------------------------------------------------------------ Identificador del servicio seleccionado.
    'otros_texto'  => '', //------------------------------------------------------------ Texto adicional cuando se elige la opción Otros.
    'descripcion'  => '', //------------------------------------------------------------ Descripción de la necesidad del cliente.
    'email'        => '', //------------------------------------------------------------ Correo electrónico de contacto.
    'prefijo_telefono' => '+34', //----------------------------------------------------- Prefijo internacional seleccionado por defecto.
    'telefono'     => '', //------------------------------------------------------------ Número de teléfono opcional.
];

// Traemos el catálogo completo de servicios para el desplegable.
// Aquí sí mostramos todos, incluido Otros y Mantenimiento, aunque no aparezcan como tarjeta en la home.
$servicios = $pdo->query("SELECT id, nombre FROM servicios ORDER BY id ASC")->fetchAll(); // Consulta todos los servicios y los guarda en un array para generar el desplegable.

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
    $valores['tipo_persona'] = $valores['tipo_identificador'] === 'cif' ? 'juridica' : 'fisica'; // Deduce automáticamente el tipo de persona según el documento.
    $valores['nif_cif']      = trim($_POST['nif_cif'] ?? ''); //----------------------- Recoge el documento y elimina espacios laterales.
    $valores['nombre']       = trim($_POST['nombre'] ?? ''); //------------------------ Recoge el nombre y elimina espacios laterales.
    $valores['servicio_id']  = $_POST['servicio_id'] ?? ''; //------------------------- Recoge el identificador del servicio seleccionado.
    $valores['otros_texto']  = trim($_POST['otros_texto'] ?? ''); //------------------- Recoge el texto de la opción Otros.
    $valores['descripcion']  = trim($_POST['descripcion'] ?? ''); //------------------- Recoge la descripción de la solicitud.
    $valores['email']        = trim($_POST['email'] ?? ''); //------------------------- Recoge el correo electrónico.
    $valores['prefijo_telefono'] = trim($_POST['prefijo_telefono'] ?? '+34'); //------- Recoge el prefijo internacional seleccionado.
    $valores['telefono']     = trim($_POST['telefono'] ?? ''); //---------------------- Recoge el teléfono opcional.

    // --- Validaciones básicas ---
    $valores['nif_cif'] = strtoupper($valores['nif_cif']); //------------------------- Convierte el documento a mayúsculas antes de validarlo.

    if (!in_array($valores['tipo_identificador'], ['dni', 'nie', 'cif'], true)) { //---- Comprueba que el tipo de documento sea uno de los permitidos.
        $errores[] = 'Selecciona un tipo de documento válido.'; //---------------------- Añade un error si el tipo de documento no es válido.
    } elseif ($valores['nif_cif'] === '') { //----------------------------------------- Comprueba que el campo del documento no esté vacío.
        $errores[] = 'El documento identificativo es obligatorio.'; //------------------ Añade un error si no se ha escrito ningún documento.
    } elseif ($valores['tipo_identificador'] === 'dni' && !validarDni($valores['nif_cif'])) { // Valida el formato y la letra del DNI.
        $errores[] = 'El DNI introducido no es válido.'; //----------------------------- Añade un error si el DNI no supera la validación.
    } elseif ($valores['tipo_identificador'] === 'nie' && !validarNie($valores['nif_cif'])) { // Valida el formato y la letra del NIE.
        $errores[] = 'El NIE introducido no es válido.'; //----------------------------- Añade un error si el NIE no supera la validación.
    } elseif ($valores['tipo_identificador'] === 'cif' && !validarCif($valores['nif_cif'])) { // Valida el formato y el control del CIF.
        $errores[] = 'El CIF introducido no es válido.'; //----------------------------- Añade un error si el CIF no supera la validación.
    }

    if ($valores['nombre'] === '') { //------------------------------------------------- Comprueba que el campo nombre sea obligatorio.
        $errores[] = $valores['tipo_persona'] === 'juridica' //------------------------- Elige el mensaje según sea empresa o persona física.
            ? 'El nombre de la empresa es obligatorio.' //-------------------------------- Mensaje mostrado para una persona jurídica.
            : 'El nombre y apellidos son obligatorios.'; //-------------------------------- Mensaje mostrado para una persona física.
    }

    if ($valores['servicio_id'] === '') { //------------------------------------------- Comprueba que se haya seleccionado un servicio.
        $errores[] = 'Selecciona qué servicio necesitas.'; //--------------------------- Añade un error si no se ha elegido ningún servicio.
    }

    if ($valores['descripcion'] === '') { //------------------------------------------- Comprueba que la descripción no esté vacía.
        $errores[] = 'Cuéntanos brevemente qué necesitas.'; //-------------------------- Añade un error si no se explica la necesidad.
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
            $valores['telefono'] = $valores['prefijo_telefono'] . $telefonoLimpio; //---- Une el prefijo y el número antes de guardarlo en la base de datos.
        }
    }

    // --- Comprobación anti-duplicados por NIF/CIF ---
    if (empty($errores)) { //----------------------------------------------------------- Solo consulta la base de datos si no existen errores previos.
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nif_cif = ?"); //----------- Prepara una consulta segura para buscar documentos duplicados.
        $stmt->execute([$valores['nif_cif']]); //--------------------------------------- Sustituye el interrogante por el documento introducido.

        if ($stmt->fetch()) { //--------------------------------------------------------- Comprueba si ya existe un usuario con ese documento.
            $errores[] = 'Ya existe una cuenta con este NIF/CIF. <a href="login.php">Inicia sesión</a> o <a href="recuperar-password.php">recupera tu contraseña</a>.'; // Informa de que ya existe una cuenta y ofrece accesos alternativos.
        }
    }

    // --- Si todo va bien, creamos el usuario y la solicitud ---
    if (empty($errores)) { //----------------------------------------------------------- Solo crea los registros si todas las validaciones son correctas.
        try {
            $pdo->beginTransaction(); //------------------------------------------------ Inicia una transacción para que usuario y solicitud se creen juntos.

            // Prepara la inserción del nuevo usuario.
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (rol, tipo_persona, nif_cif, nombre, email, telefono, estado_perfil, fecha_alta, ultima_actividad)
                VALUES ('cliente', ?, ?, ?, ?, ?, 'pendiente_completar', NOW(), NOW())
            ");
            $stmt->execute([ //---------------------------------------------------------- Ejecuta la inserción sustituyendo los interrogantes por los valores del formulario.
                $valores['tipo_persona'], //-------------------------------------------- Guarda si se trata de persona física o jurídica.
                $valores['nif_cif'], //-------------------------------------------------- Guarda el documento identificativo.
                $valores['nombre'], //--------------------------------------------------- Guarda el nombre o razón social.
                $valores['email'], //---------------------------------------------------- Guarda el correo electrónico.
                $valores['telefono'] ?: null, //---------------------------------------- Guarda el teléfono o null si se dejó vacío.
            ]);

            $usuario_id = $pdo->lastInsertId(); //-------------------------------------- Obtiene el ID del usuario recién creado.

            // Prepara la inserción de la solicitud de servicio.
            $stmt = $pdo->prepare("
                INSERT INTO solicitudes_servicio (usuario_id, servicio_id, otros_texto, descripcion, fecha)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([ //---------------------------------------------------------- Ejecuta la inserción de la solicitud.
                $usuario_id, //---------------------------------------------------------- Relaciona la solicitud con el usuario recién creado.
                $valores['servicio_id'], //-------------------------------------------- Guarda el servicio seleccionado.
                $valores['otros_texto'] ?: null, //------------------------------------ Guarda el texto de Otros o null si no se utilizó.
                $valores['descripcion'], //-------------------------------------------- Guarda la explicación escrita por el cliente.
            ]);

            $pdo->commit(); //---------------------------------------------------------- Confirma la transacción y guarda ambos registros definitivamente.

            // Guardamos temporalmente a quién hay que activar. Todavía no es una sesión de login.
            $_SESSION['activar_usuario_id'] = $usuario_id; //-------------------------- Guarda temporalmente el ID para crear la contraseña del usuario.

            header('Location: crear-password.php'); //-------------------------------- Redirige a la página donde el usuario crea su contraseña.
            exit; //-------------------------------------------------------------------- Finaliza el script después de la redirección.

        } catch (PDOException $e) { //--------------------------------------------------- Captura cualquier error producido durante las operaciones con la base de datos.
            $pdo->rollBack(); //-------------------------------------------------------- Revierte la transacción para no guardar datos incompletos.
            $errores[] = 'No se ha podido procesar la solicitud. Inténtalo de nuevo en unos minutos.'; // Muestra un mensaje genérico sin revelar detalles internos.
        }
    }
}

require '../includes/header.php'; //--------------------------------------------------- Incluye la cabecera común de la aplicación.
?>

<section class="form-page"> <!---------------------------------------------------------- Contiene toda la página del formulario. -->
    <div class="form-card"> <!--------------------------------------------------------- Crea la tarjeta visual donde se muestra el formulario. -->
        <h1>¿Qué servicio necesitas?</h1> <!--------------------------------------------- Muestra el título principal de la página. -->
        <p class="form-intro">Cuéntanos qué necesitas y te contactamos con un presupuesto. Al enviarlo, se creará tu cuenta para que puedas seguir el estado de tu solicitud.</p> <!-- Explica al usuario qué ocurrirá al enviar el formulario. -->

        <?php if (!empty($errores)): ?> <!---------------------------------------------- Comprueba si existen errores de validación. -->
            <div class="form-errores"> <!----------------------------------------------- Muestra visualmente los errores encontrados. -->
                <ul>
                    <?php foreach ($errores as $error): ?> <!---------------------------- Recorre todos los mensajes de error. -->
                        <li><?= $error /* algunos mensajes incluyen enlaces, por eso no usamos htmlspecialchars aquí */ ?></li> <!-- Muestra cada error dentro de una lista. -->
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" id="formServicio"> <!-------------------------------------- Envía los datos mediante POST al mismo archivo. -->

            <div class="form-group" id="grupoTipoIdentificador"> <!------------------- Agrupa las opciones de tipo de documento. -->
                <label>Tipo de documento</label>
                <div class="form-radio-group"> <!-------------------------------------- Coloca las opciones DNI, NIE y CIF en un mismo grupo. -->
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
                    <label class="form-radio" id="opcionCif">
                        <input type="radio" name="tipo_identificador" value="cif"
                            <?= $valores['tipo_identificador'] === 'cif' ? 'checked' : '' ?>> <!---- Envía el valor cif cuando esta opción está seleccionada. -->
                        CIF
                    </label>
                </div>
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa la etiqueta y el campo del documento. -->
                <label for="nif_cif" id="labelIdentificador">DNI</label> <!------------- La etiqueta cambia dinámicamente según el documento elegido. -->
                <input type="text" id="nif_cif" name="nif_cif" maxlength="9"
                    value="<?= htmlspecialchars($valores['nif_cif']) ?>" required> <!---- Limita el documento a un máximo de nueve caracteres. Conserva el valor y evita insertar HTML malicioso. -->
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa la etiqueta y el campo del nombre. -->
                <label for="nombre" id="labelNombre">Nombre y apellidos</label> <!------ La etiqueta cambia a Nombre de la empresa cuando se elige CIF. -->
                <input type="text" id="nombre" name="nombre"
                    value="<?= htmlspecialchars($valores['nombre']) ?>" required> <!----- Campo donde se escribe el nombre o la empresa. Conserva el valor y obliga a rellenar el campo. -->
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa el desplegable de servicios. -->
                <label for="servicio_id">Servicio que necesitas</label>
                <select id="servicio_id" name="servicio_id" required> <!--------------- Envía el ID del servicio seleccionado. -->
                    <option value="">Selecciona una opción</option> <!------------------- Opción inicial sin servicio seleccionado. -->
                    <?php foreach ($servicios as $s): ?> <!------------------------------ Recorre los servicios recuperados de la base de datos. -->
                        <option value="<?= $s['id'] ?>" <?= (string)$valores['servicio_id'] === (string)$s['id'] ? 'selected' : '' ?>> <!-- Conserva el servicio seleccionado tras un error. -->
                            <?= htmlspecialchars($s['nombre']) ?> <!-------------------- Muestra el nombre del servicio de forma segura. -->
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="grupoOtrosTexto" style="display:none;"> <!----- Campo oculto que solo aparece al elegir Otros. -->
                <label for="otros_texto">Especifica qué necesitas</label>
                <input type="text" id="otros_texto" name="otros_texto"
                    value="<?= htmlspecialchars($valores['otros_texto']) ?>"> <!--------- Permite explicar una necesidad que no aparece en el catálogo. Conserva el texto tras un error. -->
            </div>

            <div class="form-group"> <!------------------------------------------------ Agrupa el área de descripción. -->
                <label for="descripcion">Cuéntanos brevemente qué necesitas</label>
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
            <button type="submit" class="btn-primary form-submit">Enviar solicitud</button> <!-- Envía el formulario. -->
        </form>
    </div>
</section>

<script>
    // Cambia las etiquetas según el tipo de documento seleccionado.
    const labelIdentificador = document.getElementById('labelIdentificador'); //---------------- Busca la etiqueta que muestra DNI, NIE o CIF.
    const labelNombre = document.getElementById('labelNombre'); //-------------------------------- Busca la etiqueta del nombre o de la empresa.
    const inputNombre = document.getElementById('nombre'); //------------------------------------ Busca el campo donde se escribe el nombre.

    function actualizarDocumento() { //----------------------------------------------------------- Actualiza las etiquetas y ejemplos según el documento elegido.
        const tipoDocumento = document.querySelector('input[name="tipo_identificador"]:checked')?.value || 'dni'; // Obtiene el documento seleccionado o usa DNI por defecto.
        const esJuridica = tipoDocumento === 'cif'; //------------------------------------------- Comprueba si el documento pertenece a una persona jurídica.
        const configuracion = { //---------------------------------------------------------------- Guarda la etiqueta y el ejemplo correspondiente a cada documento.
            dni: { //-------------------------------------------------------------------------------- Configuración visual para el DNI.
                label: 'DNI', //-------------------------------------------------------------------- Texto que se mostrará en la etiqueta.
                placeholder: 'Ej. 12345678Z' //----------------------------------------------------- Ejemplo que se mostrará dentro del campo.
            },
            nie: { //-------------------------------------------------------------------------------- Configuración visual para el NIE.
                label: 'NIE',
                placeholder: 'Ej. X1234567L'
            },
            cif: { //-------------------------------------------------------------------------------- Configuración visual para el CIF.
                label: 'CIF',
                placeholder: 'Ej. B12345674'
            }
        };

        labelIdentificador.textContent = configuracion[tipoDocumento].label; //---------------------- Cambia el texto de la etiqueta del documento.
        document.getElementById('nif_cif').placeholder = configuracion[tipoDocumento].placeholder; // Cambia el ejemplo mostrado dentro del campo.
        labelNombre.textContent = esJuridica ? 'Nombre de la empresa' : 'Nombre y apellidos'; //----- Cambia la etiqueta según sea empresa o persona física.
        inputNombre.placeholder = esJuridica ? 'Ej. Panadería García S.L.' : 'Ej. María García Pérez'; // Cambia el ejemplo del nombre según el documento.
    }

    const radiosIdentificador = document.querySelectorAll('input[name="tipo_identificador"]'); //---- Busca las tres opciones de documento.
    radiosIdentificador.forEach(r => r.addEventListener('change', actualizarDocumento)); //----------- Ejecuta la actualización al cambiar de documento.
    actualizarDocumento(); //-------------------------------------------------------------------------- Ajusta el formulario correctamente al cargar la página.

    // Muestra el campo de texto libre si el servicio elegido es Otros.
    const selectServicio = document.getElementById('servicio_id'); //---------------------------------- Busca el desplegable de servicios.
    const grupoOtros = document.getElementById('grupoOtrosTexto'); //----------------------------------- Busca el campo adicional de la opción Otros.

    function actualizarOtros() { //--------------------------------------------------------------------- Muestra u oculta el campo adicional según el servicio elegido.
        const textoSeleccionado = selectServicio.options[selectServicio.selectedIndex]?.text || ''; //- Obtiene el texto de la opción seleccionada.
        grupoOtros.style.display = textoSeleccionado.trim() === 'Otros' ? 'block' : 'none'; //---------- Muestra el campo solo cuando se selecciona Otros.
    }

    selectServicio.addEventListener('change', actualizarOtros); //------------------------------------- Ejecuta la función al cambiar el servicio.
    actualizarOtros(); //-------------------------------------------------------------------------------- Ajusta el campo Otros correctamente al cargar la página.
</script>

<?php require '../includes/footer.php'; ?> <!------------------------------------------ Incluye el pie de página común y carga el JavaScript principal. -->