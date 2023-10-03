<?php
    namespace danieltm\matrix_orm;
    use PDO;
    use Exception;

    class Connection{

        public static function Conect(){
            self::loadEnv(self::ShowDirEnv() . '/.env');
            $host = $_ENV['HOST'];

            $user = $_ENV['USER'];

            $pass = $_ENV['PASSWORD'];

            if(isset($_ENV['PORT'])){
                $port = $_ENV['PORT'];
            }else{
                $port = 3306;
            }

            $db = $_ENV['DATABASE'];

            if(isset($_ENV['DIALECT'])){
                $dialect = $_ENV['DIALECT'];
            }else{
                $dialect = "mysql";
            }

            try {
                $pdo = new PDO("$dialect:host=$host;port=$port;dbname=$db", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } catch (\Throwable $th) {
                return "Erro na conexão com o banco de dados: " . $th->getMessage();
            }
        }

        public static function ShowDirEnv(){
            $limit = 10;
            $baseDir = __DIR__;
            while (!file_exists($baseDir . '/.env') && !file_exists($baseDir . '\index.php')) {
                $baseDir = dirname($baseDir);
                if($limit === 0){
                    break;
                }
                $limit--;
            }
            return $baseDir;
        }

        private static function loadEnv ($filePath){
            if (!file_exists($filePath)) {
                throw new Exception('O arquivo .env não foi encontrado.');
            }

            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!empty($key)) {
                        $_ENV[$key] = $value;
                    }
                }
            }
        }

    }

?>
