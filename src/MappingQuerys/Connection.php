<?php
    namespace matrixOrm;
    use PDO;

    class Connection{

        public static function Conect(){
            $host = "localhost";
            $user = "root";
            $pass = "";
            $port = 3306;
            $db = "orm";
            $dialect = "mysql";

            try {
                $pdo = new PDO("$dialect:host=$host;port=$port;dbname=$db", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } catch (\Throwable $th) {
                return "Erro na conexão com o banco de dados: " . $th->getMessage();
            }
        }
    }

?>
