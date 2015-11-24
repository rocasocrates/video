<?php

function usuario_conectado()
{
    return isset($_SESSION['socio_id']);
}

function comprobar_usuario_conectado()
{
    if (!usuario_conectado()):
        header("Location: ../socios/login.php");
        return;
    else:
        $socio_id = $_SESSION['socio_id'];
        $res = pg_query_params("select nombre
                                  from socios
                                 where id = $1", array($socio_id));
        if (pg_num_rows($res) == 0):
            header("Location: ../socios/login.php");
            return;
        else:
            $fila = pg_fetch_assoc($res, 0);
            $nombre = $fila['nombre']; ?>
            <p align="right">
                Usuario: <strong><?= $nombre ?></strong>
                <a href="../socios/logout.php">
                    <input type="button" value="Salir" />
                </a>
            </p>
            <hr/><?php
        endif;
    endif;
}

function alinear($v)
{
    return isset($v['align']) ? "align=\"${v['align']}\"" : '';
}

function selected($value, $col)
{
    return $value == $col ? 'selected="on"' : '';
}

function resaltar($columna, $orden)
{
    return $columna == $orden ? 'style="font-weight: bold"': '';
}

function sentido($columna, $orden, $sentido)
{
    if ($columna == $orden) {
        return $sentido == "asc" ? "desc" : "asc";
    } else {
        return "asc";
    }
}

function conectar()
{
    return pg_pconnect("host=localhost dbname=datos user=usuario
                        password=usuario");
}

function volver()
{ ?>
    <a href="index.php"><input type="button" value="Volver" /></a><?php
}

function comprobar_errores($error)
{
    if ($error) {
        throw new Exception();
    }
}

function comprobar_operacion($res, &$error)
{
    if (!$res || pg_affected_rows($res) != 1) {
        $res = pg_query("rollback");
        $error[] = "no se ha podido llevar a cabo la operación";
        throw new Exception();
    }
}

function filtro($columnas, $columna, $criterio)
{
    if ($criterio != "") {
        $where = "$columna::text = $1";
        $col = $columnas[$columna];
        if (isset($col['post'])) {
            $func = $col['post'];
            $criterio = call_user_func($func, $criterio);
        } elseif (!isset($col['exacto'])) {
            $where = "formato($columna::text) like " .
                     "formato('%' || $1 || '%')";
        }
        $pqp = array($criterio);
    } else {
        $where = "true";
        $pqp = array();
    }

    return array($where, $pqp);
}

function generar_resultado($params)
{
    extract($params);

    if (pg_num_rows($res) > 0): ?>
        <p><table border="1" style="margin:auto">
            <thead><?php
                $href = "index.php?";
                foreach ($columnas as $k => $v):
                    $sufijo = sentido($k, $orden, $sentido);
                    if ($k == $orden) {
                        $flecha = $sentido == "asc" ? " ▴" : " ▾";
                    } else {
                        $flecha = "";
                    } ?>
                    <th>
                        <a href="<?= "${href}orden=$k&sentido=$sufijo" ?>">
                            <input type="button"
                                   value="<?= $v['bonito'] . $flecha ?>"
                                   <?= resaltar($k, $orden) ?> />
                        </a>
                    </th><?php
                endforeach; ?>
                <th colspan="2">Operaciones</th>
            </thead>
            <tbody><?php
                for ($i = 0; $i < pg_num_rows($res); $i++):
                    $fila = pg_fetch_assoc($res, $i); ?>
                    <tr><?php
                        foreach ($columnas as $k => $v): ?>
                            <td <?= alinear($v) ?> >
                                <?= $fila[$k] ?>
                            </td><?php
                        endforeach; ?>
                        <td>
                            <form action="modificar.php" method="get">
                                <input type="hidden" name="id"
                                       value="<?= $fila['id'] ?>" />
                                <input type="submit" value="Modificar" />
                            </form>
                        </td>
                        <td>
                            <form action="borrar.php" method="get">
                                <input type="hidden" name="id"
                                       value="<?= $fila['id'] ?>" />
                                <input type="submit" value="Borrar" />
                            </form>
                        </td>
                    </tr><?php
                endfor; ?>
            </tbody>
        </table></p><?php
    else: ?>
        <h3>La búsqueda no ha devuelto ningún resultado</h3><?php
    endif;
}

function formulario_busqueda($params)
{
    extract($params); ?>

    <p><form action="index.php" method="get">
        <label for="criterio">Buscar:</label>
        <select name="columna"><?php
            foreach ($columnas as $v):
                if (isset($v['criterio'])): ?>
                    <option value="<?= $v['criterio'] ?>"
                        <?= selected($v['criterio'], $columna) ?> >
                        <?= $v['bonito'] ?>
                    </option><?php
                endif;
            endforeach; ?>
        </select>
        <input type="text" name="criterio" value="<?= $criterio ?>" />
        <input type="submit" value="Buscar">
    </form></p><?php
}

function recoger_parametros($columnas)
{
    if (isset($_GET['orden'])) {
        $orden = trim($_GET['orden']);
        if (!isset($columnas[$orden])) {
            header("Location: index.php");
            return array();
        }
        $_SESSION['orden'] = $orden;
    } else {
        if (isset($_SESSION['orden'])) {
            $orden = $_SESSION['orden'];
        } else {
            foreach ($columnas as $k => $v) break;
            $orden = $k;
            $_SESSION['orden'] = $orden;
        }
    }

    if (isset($_GET['sentido'])) {
        $sentido = trim($_GET['sentido']);
        if ($sentido != "asc" && $sentido != "desc") {
            header("Location: index.php");
            return array();
        }
        $_SESSION['sentido'] = $sentido;
    } else {
        if (isset($_SESSION['sentido'])) {
            $sentido = $_SESSION['sentido'];
        } else {
            $sentido = "asc";
            $_SESSION['sentido'] = $sentido;
        }
    }

    if (isset($_GET['columna'])) {
        $columna = trim($_GET['columna']);
        $_SESSION['columna'] = $columna;
    } else {
        if (isset($_SESSION['columna'])) {
            $columna = $_SESSION['columna'];
        } else {
            $columna = "";
            $_SESSION['columna'] = $columna;
        }
    }

    if (isset($_GET['criterio'])) {
        $criterio = trim($_GET['criterio']);
        $_SESSION['criterio'] = $criterio;
    } else {
        if (isset($_SESSION['criterio'])) {
            $criterio = $_SESSION['criterio'];
        } else {
            $criterio = "";
            $_SESSION['criterio'] = $criterio;
        }
    }

    return compact('orden', 'sentido', 'columna', 'criterio');
}

function index($columnas, $vista)
{
    extract(recoger_parametros($columnas));

    $params = compact('columnas', 'columna', 'criterio', 'orden',
                      'sentido');

    formulario_busqueda($params);

    $con = conectar();

    list($where, $pqp) = filtro($columnas, $columna, $criterio);

    $res = pg_query_params($con, "select *
                                    from $vista
                                   where $where
                                order by $orden $sentido", $pqp);

    $params['res'] = $res;
    generar_resultado($params); ?>

    <a href="insertar.php"><input type="button" value="Insertar" /></a><?php
}

