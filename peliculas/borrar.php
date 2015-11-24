<!DOCTYPE html>
<html>
    <head>
        <title>Borrar</title>
        <meta charset="utf-8" />
    </head>
    <body><?php
        require 'auxiliar.php';
        require '../comunes/auxiliar.php';

        if (isset($_GET['id'])):
            $id = trim($_GET['id']);
            conectar();
            $res = pg_query_params("select codigo, titulo
                                      from v_copias
                                     where id = $1", array($id));
            if (pg_num_rows($res) != 1): ?>
                <h3>La película indicada no existe</h3><?php
                volver();
            else:
                $fila = pg_fetch_assoc($res, 0);
                extract($fila); ?>
                <h4><?= $codigo ?> : <?= $titulo ?></h4>
                <h3>¿Está seguro de querer borrar la película?</h3>
                <form action="borrar.php" method="post">
                    <input type="hidden" name="id" value="<?= $id ?>" />
                    <input type="submit" value="Sí" />
                    <a href="index.php"><input type="button" value="No" /></a>
                </form><?php
            endif;
        elseif (isset($_POST['id'])):
            $id = trim($_POST['id']);
            conectar();
            $res = pg_query_params("update copias
                                       set borrada = true
                                     where id = $1", array($id));
            if (pg_affected_rows($res) == 1): ?>
                <h3>Película borrada correctamente</h3><?php
            else: ?>
                <h3>No ha sido posible borrar la película</h3><?php
            endif;
            volver();
        else:
            header("Location: index.php");
        endif; ?>
    </body>
</html>
