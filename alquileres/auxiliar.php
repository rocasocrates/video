<?php

function no_vacio($valor)
{
    if ($valor == "") {
        throw new Exception("");
    }
}

function obtener_socio($numero)
{
    $res = pg_query_params("select *
                              from socios
                             where numero = $1", array($numero));
    if (pg_num_rows($res) == 0) {
        throw new Exception("el socio indicado no existe");
    }

    return pg_fetch_assoc($res, 0);
}

function obtener_copia($codigo)
{
    $res = pg_query_params("select *
                              from v_copias
                             where codigo = $1", array($codigo));
    if (pg_num_rows($res) == 0) {
        throw new Exception("la película indicada no existe");
    }

    return pg_fetch_assoc($res, 0);
}

function copia_disponible($copia_id)
{
    $res = pg_query_params("select numero, nombre
                              from pendientes p join socios s
                                on p.socio_id = s.id
                             where copia_id = $1", array($copia_id));
    if (pg_num_rows($res) > 0) {
        $fila = pg_fetch_assoc($res, 0);
        $numero = $fila['numero'];
        $nombre = $fila['nombre'];
        throw new Exception("Película ya alquilada");
    }
}

function mostrar_socio($fila)
{
    $id = $fila['id'];
    $nombre = $fila['nombre'];
    $telefono = $fila['telefono']; ?>
    <p><fieldset>
        <p>
            <strong>Nombre:</strong>
            <a href="../socios/modificar.php?id=<?= $id ?>">
                <?= $nombre ?>
            </a>
        </p>
        <p><strong>Teléfono:</strong> <?= $telefono ?></p>
    </fieldset></p><?php
}

function mostrar_copia($fila)
{
    $id = $fila['id'];
    $titulo = $fila['titulo'];
    $precio_alq_format = $fila['precio_alq_format']; ?>
    <p><fieldset>
        <p>
            <strong>Título:</strong>
            <a href="../peliculas/modificar.php?id=<?= $id ?>">
                <?= $titulo ?>
            </a>
        </p>
        <p><strong>Precio:</strong> <?= $precio_alq_format ?></p>
    </fieldset></p><?php
}

function pendientes_de_socio($socio_id, $numero)
{
    $res = pg_query_params("select *
                              from pendientes
                             where socio_id = $1", array($socio_id));
    if (pg_num_rows($res) > 0): ?>
        <table border="1" style="margin:auto">
            <thead>
                <th>Código</th>
                <th>Título</th>
                <th>Precio alq.</th>
                <th>Devolver</th>
            </thead>
            <tbody><?php
                for ($i = 0; $i < pg_num_rows($res); $i++):
                    $fila = pg_fetch_assoc($res, $i);
                    extract($fila); ?>
                    <tr>
                        <td align="center">
                            <a href="../peliculas/modificar.php?id=<?= $copia_id ?>">
                                <?= $codigo ?>
                            </a>
                        </td>
                        <td>
                            <a href="../peliculas/modificar.php?id=<?= $copia_id ?>">
                                <?= $titulo ?>
                            </a>
                        </td>
                        <td align="center"><?= $precio_alq_format ?></td>
                        <td>
                            <form action="index.php?numero=<?= $numero ?>" method="post">
                                <input type="hidden" name="alquiler_id"
                                       value="<?= $id ?>" />
                                <input type="submit" value="Devolver" />
                            </form>
                        </td>
                    </tr><?php
                endfor; ?>
            </tbody>
        </table><?php
    endif;
}

function alquilar($socio_id, $copia_id)
{
    try {
        $res = pg_query("begin");
        $res = pg_query("lock table alquileres in share mode");
        copia_disponible($copia_id);
        $res = pg_query_params("insert into alquileres (socio_id,
                                                        copia_id)
                                values ($1, $2)",
                                array($socio_id, $copia_id));
        $res = pg_query("commit");
    } catch (Exception $e) {
        $res = pg_query("rollback");
        throw new Exception("la película ya ha sido alquilada");
    }
}

function devolver($alquiler_id)
{
    $res = pg_query_params("update alquileres
                               set fecha_dev = current_date
                             where id = $1", array($alquiler_id));

}

function pedir_numero_socio($numero)
{ ?>
    <form action="index.php" method="get">
        <label for="numero">Nº socio:</label>
        <input type="text" name="numero" value="<?= $numero ?>" /><br/>
        <input type="submit" value="Aceptar" />
    </form><?php
}

function pedir_codigo_copia($codigo)
{ ?>
    <form action="index.php" method="get">
        <label for="codigo">Código:</label>
        <input type="text" name="codigo" value="<?= $codigo ?>" /><br/>
        <input type="submit" value="Aceptar" />
    </form><?php
}

function boton_alquilar($socio_id, $copia_id)
{ ?>
    <form action="index.php" method="post">
        <input type="hidden" name="socio_id" value="<?= $socio_id ?>" />
        <input type="hidden" name="copia_id" value="<?= $copia_id ?>" />
        <input type="submit" value="Alquilar" />
    </form><?php
}

