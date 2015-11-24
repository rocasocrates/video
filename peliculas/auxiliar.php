<?php

define('PATRON', '/^(-?\d*)(,\d{2})?(\s*€)?$/');

function comprobar_titulo($titulo, &$error)
{
    if (strlen($titulo) > 50) {
        $error[] = "el título no puede tener más de 50 caracteres";
    }
}

function comprobar_precio_alq(&$precio_alq, &$error)
{
    if ($precio_alq != "") {
        $c = array();
        preg_match(PATRON, $precio_alq, $c);
        if (empty($c)) {
            $error[] = "el precio no es correcto";
        } else {
            $precio = normalizar_precio($precio_alq);
            $patron = '/^(-?\d*),(\d*)\s€$/';
            $c = array();
            preg_match($patron, $precio, $c);
            $precio = "${c[1]}.${c[2]}";
            $valor = (float) $precio;
            if ($valor < 0 || $valor >= 100) {
                $error[] = "el precio debe estar entre 0 y 99,99 €";
            } else {
                $precio_alq = $precio;
            }
        }
    }
}

function comprobar_pelicula_id($pelicula_id, &$error)
{
    if ($pelicula_id == "") {
        $error[] = "la película es obligatoria";
    }
}

function comprobar_codigo($codigo, &$error)
{
    if ($codigo == "") {
        $error[] = "el código es obligatorio";
    } else {
        if (!ctype_digit($codigo)) {
            $error[] = "el código sólo puede tener dígitos";
        }
        if (strlen($codigo) > 13) {
            $error[] = "el código no puede tener más de 13 dígitos";
        }
    }
}

function bloquear_tabla_peliculas()
{
    $res = pg_query("lock table peliculas in share mode");
}

function bloquear_tabla_copias()
{
    $res = pg_query("lock table copias in share mode");
}

function comparar_precios($precio_usuario, $precio_tabla)
{
    if ($precio_usuario == $precio_tabla) {
        return TRUE;
    }

    $patron = '/^(\d){1,2}(,\d{2})?(\s*€)?$/';

    $c = array();
    preg_match($patron, $precio_usuario, $c);

    if (empty($c)) {
        return FALSE;
    }

    if ($c[2] == "") {
        $precio_usuario = preg_replace($patron, '\1,00\3', $precio_usuario);
    }

    $precio_usuario = preg_replace($patron, '\1\2', $precio_usuario);
    $precio_tabla = preg_replace($patron, '\1\2', $precio_tabla);

    return $precio_usuario == $precio_tabla;
}

function normalizar_precio($precio)
{
    $c = array();
    preg_match(PATRON, $precio, $c);

    if (empty($c)) {
        return $precio;
    }

    if (!isset($c[2]) || $c[2] == "") {
        $precio = preg_replace(PATRON, '\1,00\3', $precio);
    }

    $precio = preg_replace(PATRON, '\1\2 €', $precio);

    return $precio;
}

function normalizar_fecha($fecha)
{
    $patron = '/^(\d{1,2})-(\d{1,2})-(\d{2}(\d{2})?)$/';

    $c = array();
    preg_match($patron, $fecha, $c);

    if (empty($c)) {
        return $fecha;
    }

    if (strlen($c[1]) == 1) {
        $fecha = preg_replace($patron, '0\1-\2-\3', $fecha);
    }

    if (strlen($c[2]) == 1) {
        $fecha = preg_replace($patron, '\1-0\2-\3', $fecha);
    }

    if (strlen($c[3]) == 2) {
        $cent = (int) ($c[3]) >= 70 ? "19" : "20";
        $fecha = preg_replace($patron, "\\1-\\2-$cent\\3", $fecha);
    }

    return $fecha;
}

