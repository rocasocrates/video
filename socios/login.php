<?php session_start() ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Videoclub</title>
        <meta charset="utf-8" />
    </head>
    <body><?php
        require '../comunes/auxiliar.php';

        if (usuario_conectado()) {
            header("Location: ../alquileres/index.php");
            return;
        }

        if (isset($_POST['numero'], $_POST['password'])):
            $numero = trim($_POST['numero']);
            $password = trim($_POST['password']);
            conectar();
            $res = pg_query_params("select id
                                      from socios
                                     where numero = $1 and
                                           password = md5($2)",
                                    array($numero, $password));
            if (pg_num_rows($res) > 0):
                $fila = pg_fetch_assoc($res, 0);
                $id = $fila['id'];
                $_SESSION['socio_id'] = $id;
                $_SESSION['socio_numero'] = $numero;
                header("Location: ../alquileres/index.php");
                return;
            else: ?>
                <h3>Error: usuario incorrecto</h3><?php
            endif;
        endif; ?>
        <form action="login.php" method="post">
            <label for="numero">Número:</label>
            <input type="text" name="numero" /><br/>
            <label for="password">Contraseña:</label>
            <input type="password" name="password" /><br/>
            <input type="submit" value="Login" />
        </form>
    </body>
</html>

