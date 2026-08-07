<?php
function validarDni(string $dni): bool //---------------------------------------------- Valida que un DNI tenga formato correcto y una letra de control válida.
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
?>