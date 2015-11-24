<?php

function comprobar_titulo_obligatorio($titulo, &$error)
{
    if ($titulo == "") {
        $error[] = "el título es obligatorio";
    }
}

function comprobar_precio_alq_obligatorio($precio_alq, &$error)
{
    if ($precio_alq == "") {
        $error[] = "el precio es obligatorio";
    }
}

function comprobar_fecha_alta($dia, $mes, $anyo, &$error)
{
    if (ctype_digit($dia) && ctype_digit($mes) && ctype_digit($anyo)) {
        if (checkdate($mes, $dia, $anyo)) {
            return;
        }
    }
    $error[] = "la fecha de alta no es válida";
}

function modificar_pelicula($valores)
{
    $id = $valores['id'];
    unset($valores['id']);
    $pqp = array_values($valores);
    $pqp[] = $id;
    $asignaciones = array();
    $i = 1;
    foreach ($valores as $k => $v) {
        $asignaciones[] = "$k = \$$i";
        $i++;
    }
    $asignaciones = implode(",", $asignaciones);
    $res = pg_query_params("update peliculas
                               set $asignaciones
                             where id = \$$i", $pqp);

    return $res;
}

function modificar_copia($valores)
{
    $id = $valores['id'];
    unset($valores['id']);
    $pqp = array_values($valores);
    $pqp[] = $id;
    $asignaciones = array();
    $i = 1;
    foreach ($valores as $k => $v) {
        $asignaciones[] = "$k = \$$i";
        $i++;
    }
    $asignaciones = implode(",", $asignaciones);
    $res = pg_query_params("update copias
                               set $asignaciones
                             where id = \$$i", $pqp);

    return $res;
}

function comprobar_existe_copia($codigo, $id, &$error)
{
    $res = pg_query_params("select id
                              from copias
                             where codigo = $1 and
                                   id != $2", array($codigo, $id));
    if (pg_num_rows($res) > 0) {
        $res = pg_query("rollback");
        $error[] = "ya existe una película con ese código";
        throw new Exception();
    }
}

function formulario_modificar($variables)
{
    $meses = array(
         1 => 'enero',
         2 => 'febrero',
         3 => 'marzo',
         4 => 'abril',
         5 => 'mayo',
         6 => 'junio',
         7 => 'julio',
         8 => 'agosto',
         9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    );

    extract($variables);

    $patron = "/(\d{4})-(\d{1,2})-(\d{1,2})/";
    $c = array();
    preg_match($patron, $fecha_alta, $c);
    $anyo = (int) $c[1];
    $mes  = (int) $c[2];
    $dia  = (int) $c[3]; ?>

    <form action="modificar.php" method="post">
        <input type="hidden" name="id" value="<?= $id ?>" />
        <input type="hidden" name="pelicula_id" value="<?= $pelicula_id ?>" />
        <label for="titulo">Título *:</label>
        <input type="text" name="titulo" value="<?= $titulo ?>" /><br/>
        <label for="precio_alq">Precio *:</label>
        <input type="text" name="precio_alq" value="<?= $precio_alq ?>" /><br/>
        <label for="codigo">Código *:</label>
        <input type="text" name="codigo" value="<?= $codigo ?>" /><br/>
        <label for="fecha_alta">Fecha *:</label>
        <select name="dia"><?php
            for ($i = 1; $i <= 31; $i++): ?>
                <option value="<?= $i ?>" <?= selected($dia, $i)?> >
                    <?= $i ?>
                </option><?php
            endfor; ?>
        </select>
        <select name="mes"><?php
            foreach ($meses as $k => $v): ?>
                <option value="<?= $k ?>" <?= selected($mes, $k) ?> >
                    <?= $v ?>
                </option><?php
            endforeach; ?>
        </select>
        <select name="anyo"><?php
            $actual = (int) date("Y");
            for ($i = $actual; $i >= $actual - 30; $i--): ?>
                <option value="<?= $i ?>" <?= selected($anyo, $i) ?> >
                    <?= $i ?>
                </option><?php
            endfor; ?>
        </select><br/>
        <input type="submit" value="Modificar" />
    </form><?php
}

