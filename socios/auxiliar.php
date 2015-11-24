<?php

function comprobar_numero($numero, &$error)
{
    if ($numero == "") {
        $error[] = "el número es obligatorio";
    } else {
        if (!ctype_digit($numero)) {
            $error[] = "el número sólo puede contener dígitos";
        }

        if (strlen($numero) > 13) {
            $error[] = "el número debe tener, como máximo, 13 dígitos";
        }
    }
}

function comprobar_dni(&$dni, &$error)
{
    if ($dni == "") {
        $dni = NULL;
    } elseif (strlen($dni) > 9) {
        $error[] = "el DNI no puede tener más de nueve caracteres";
    }
}

function comprobar_nombre($nombre, &$error)
{
    if ($nombre == "") {
        $error[] = "el nombre es obligatorio";
    }
}

function comprobar_direccion(&$direccion, &$error)
{
    if ($direccion == "") {
        $direccion = NULL;
    } elseif (strlen($direccion) > 150) {
        $error[] = "la dirección no puede tener más de 150 caracteres";
    }
}

function comprobar_codpostal(&$codpostal, &$error)
{
    if ($codpostal == "") {
        $codpostal = NULL;
    } else {
        if (!ctype_digit($codpostal)) {
            $error[] = "el código postal sólo puede contener dígitos";
        }

        if (strlen($codpostal) != 5) {
            $error[] = "el código postal debe tener cinco dígitos exactamente";
        }
    }
}

function comprobar_poblacion_id($poblacion_id, &$error)
{
    if ($poblacion_id == "") {
        $error[] = "la población es obligatoria";
    }
}

function comprobar_telefono($telefono, &$error)
{
    if (strlen($telefono) > 15) {
        $error[] = "el teléfono no puede tener más de 15 caracteres";
    }
}

function bloquear_tabla_socios()
{
    $res = pg_query("lock table socios in share mode");
}

