<?php

function comprobar_existe_socio($numero, &$error)
{
    $res = pg_query_params("select id
                              from socios
                             where numero = $1", array($numero));
    if (pg_num_rows($res) > 0) {
        $res = pg_query("rollback");
        $error[] = "ya existe un socio con ese número";
        throw new Exception();
    }
}

function insertar_socio($valores)
{
    $columnas = implode(",", array_keys($valores));
    $valores = array_values($valores);
    $comodines = array();
    for ($i = 1; $i <= count($valores); $i++) {
        $comodines[] = "\$$i";
    }
    $comodines = implode(",", $comodines);
    $res = pg_query_params("insert into socios ($columnas)
                            values ($comodines)", $valores);
    return $res;
}

function formulario_insertar($variables)
{
    extract($variables);

    $res = pg_query("select id, nombre
                       from poblaciones
                   order by nombre"); ?>

    <form action="insertar.php" method="post">
        <label for="numero">Número *:</label>
        <input type="text" name="numero" value="<?= $numero ?>" /><br/>
        <label for="dni">DNI:</label>
        <input type="text" name="dni" value="<?= $dni ?>" /><br/>
        <label for="nombre">Nombre *:</label>
        <input type="text" name="nombre" value="<?= $nombre ?>" /><br/>
        <label for="direccion">Dirección:</label>
        <input type="text" name="direccion"
               value="<?= $direccion ?>" /><br/>
        <label for="codpostal">Código postal:</label>
        <input type="text" name="codpostal"
               value="<?= $codpostal ?>" /><br/>
        <label for="poblacion_id">Población *:</label>
        <select name="poblacion_id"><?php
            for ($i = 0; $i < pg_num_rows($res); $i++):
                extract(pg_fetch_assoc($res, $i)); ?>
                <option value="<?= $id ?>"
                    <?= selected($id, $poblacion_id) ?> >
                    <?= $nombre ?>
                </option><?php
            endfor; ?>
        </select><br/>
        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" value="<?= $telefono ?>" /><br/>
        <input type="submit" value="Insertar" />
    </form><?php
}

