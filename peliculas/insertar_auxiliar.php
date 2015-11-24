<?php

function comprobar_juntos($titulo, $precio, &$error)
{
    if (($titulo == "" && $precio != "") ||
        ($titulo != "" && $precio == "")) {
        $error[] = "deben indicarse título y precio juntos";
    }
}

function comprobar_existe_copia($codigo, &$error)
{
    $res = pg_query_params("select id
                              from copias
                             where codigo = $1", array($codigo));
    if (pg_num_rows($res) > 0) {
        $res = pg_query("rollback");
        $error[] = "ya existe una película con ese código";
        throw new Exception();
    }
}

function insertar_copia($valores)
{
    $columnas = implode(",", array_keys($valores));
    $valores = array_values($valores);
    $comodines = array();
    for ($i = 1; $i <= count($valores); $i++) {
        $comodines[] = "\$$i";
    }
    $comodines = implode(",", $comodines);
    $res = pg_query_params("insert into copias ($columnas)
                            values ($comodines)", $valores);
    return $res;
}

function insertar_pelicula($valores, &$pelicula_id)
{
    $columnas = implode(",", array_keys($valores));
    $valores = array_values($valores);
    $comodines = array();
    for ($i = 1; $i <= count($valores); $i++) {
        $comodines[] = "\$$i";
    }
    $comodines = implode(",", $comodines);
    $res = pg_query_params("insert into peliculas ($columnas)
                            values ($comodines)
                            returning id", $valores);
    if (pg_affected_rows($res) == 1) {
        $fila = pg_fetch_assoc($res, 0);
        $pelicula_id = $fila['id'];
    }
    return $res;
}

function formulario_insertar($variables)
{
    extract($variables);

    $res = pg_query("select pelicula_id as id,
                            titulo || ' (' || precio_alq_format || ')' as opcion
                       from v_copias
                   group by 1, titulo, precio_alq_format
                   order by titulo"); ?>

    <form action="insertar.php" method="post">
        <fieldset>
            <label for="pelicula_id">Película:</label>
            <select name="pelicula_id"><?php
                for ($i = 0; $i < pg_num_rows($res); $i++):
                    extract(pg_fetch_assoc($res, $i)); ?>
                    <option value="<?= $id ?>"
                        <?= selected($id, $pelicula_id) ?> >
                        <?= $opcion ?>
                    </option><?php
                endfor; ?>
            </select><br/>
        </fieldset>
        <fieldset>
            <label for="titulo">Título:</label>
            <input type="text" name="titulo" value="<?= $titulo ?>" /><br/>
            <label for="precio_alq">Precio:</label>
            <input type="text" name="precio_alq" value="<?= $precio_alq ?>" /><br/>
        </fieldset>
        <label for="codigo">Código *:</label>
        <input type="text" name="codigo" value="<?= $codigo ?>" /><br/>
        <input type="submit" value="Insertar" />
    </form><?php
}

