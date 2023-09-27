<?php
    namespace matrixOrm;
    use ReflectionClass;
    use ReflectionProperty;
    use PDO;


    if (!class_exists('matrixOrm\DbManager')){
        class DbManager {
            private static $loadedClass = [];


            public static function getClassContext(){
                return self::$loadedClass;
            }

            public function findAll(){
                $table = get_called_class();
                return "SELECT * FROM $table";
            }

            public function findById($id){
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $table = strtolower($reflection->getName());
                $sql = "SELECT * FROM $table WHERE ";
                $var = $this->reflectasloopvar($reflectionVars);
                $sql .= "$var = $id;";
                unset($reflection);
                unset($reflectionVars);
                return $this->ExecuteSelect($sql);
            }

            public function __call($method, $args){
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties();
                $table = get_called_class();

                foreach($reflectionVars as $var){
                    $exopected = 'findBy' . ucfirst($var->getName());
                    $expectedLow ='findBy' . $var->getName();
                    if (strpos($method, $exopected) === 0 || strpos($method, $expectedLow) === 0) {
                        $field = lcfirst(substr($method, 6));
                        $value = $args[0];
                        return "SELECT * FROM $table WHERE $field = '$value'";
                    }
                }
            }

            public function save(DbManager $entity){
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $var = $this->reflectasloopvar($reflectionVars);
                if($reflection->hasMethod("get".ucfirst($var))){
                    $reflectionMethod = $reflection->getMethod("get".ucfirst($var));
                    $id = $reflectionMethod->invoke($this);
                }
                $resultado = $this->findById($id);
                if(count($resultado) === 0){

                }
            }

            public function Create(){
                if(count(self::$loadedClass) == 0 ){
                    self::$loadedClass = array_unique(DbLoader::load());
                }
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $controll = count($reflectionVars);
                $table = strtolower($reflection->getName());
                $sql = "CREATE TABLE IF NOT EXISTS $table (";

                foreach ($reflectionVars as $property) {
                    $propertyName = $property->getName();
                    $propertyType = $this->getPropertyType($property);

                    if ($propertyType === 'identity') {
                        $sql .= "$propertyName INT AUTO_INCREMENT PRIMARY KEY";
                    } else {
                        $nonconfig = true;
                        if ($propertyType === 'int') {
                            $sql .= "$propertyName INT ";
                            $nonconfig = false;
                        } elseif ($propertyType === 'float') {
                            $sql .= "$propertyName FLOAT";
                            $nonconfig = false;
                        } elseif ($propertyType === 'varchar') {
                            $sql .= "$propertyName VARCHAR(255)";
                            $nonconfig = false;
                        }

                        foreach(self::$loadedClass as $classGetatribute){
                            if($classGetatribute !== "DbManager" && $classGetatribute !== "DbLoader"){
                                if(strtolower($propertyName) === strtolower($classGetatribute)){
                                    $reflectionSubclas = new ReflectionClass($classGetatribute);
                                    $primarykey = $reflectionSubclas->getProperties(ReflectionProperty::IS_PRIVATE);
                                    foreach($primarykey as $atribute){
                                        $var = $this->getIdentityVarname($atribute);
                                        if($this->getPropertyType($atribute) === "identity"){
                                            if($this->hasTableRelation($property) === "1xn"){
                                                $sql .= "$propertyName"."_id INT, FOREIGN KEY ($propertyName"."_id) REFERENCES $propertyName($var)";
                                            }else{
                                                $sql .= "$propertyName INT REFERENCES $propertyName($var)";
                                            }

                                            $nonconfig = false;
                                        }
                                    }

                                }
                           }
                        }



                        if($nonconfig){
                           $sql .= "$propertyName VARCHAR(255)";
                        }
                        if ($this->hasNotNullAnnotation($property)) {
                            $sql .= " NOT NULL";
                        }

                    }

                    $sql .= ",";
                }

                $sql = rtrim($sql, ",");
                $sql .= ");";

                unset($reflection);
                unset($reflectionVars);
                if($controll > 0){
                    return $this->ExecuteCreate($sql);
                }
            }

            private function getPropertyType(ReflectionProperty $property){
                $docComment = $property->getDocComment();
                if (preg_match('/@var\s+([a-zA-Z]+)/', $docComment, $matches)) {
                    return strtolower($matches[1]);
                }
                return null;
            }

            private function hasNotNullAnnotation(ReflectionProperty $property){
                $docComment = $property->getDocComment();
                return strpos($docComment, '@notnull') !== false;
            }

            private function hasTableRelation(ReflectionProperty $property){
                $docComment = $property->getDocComment();
                if(strpos($docComment, '@OneToMany') !== false){
                    return "1xn";
                }else{
                    return "1x1";
                }
            }

            private function getIdentityVarname(ReflectionProperty $property){
                if($this->getPropertyType($property) === "identity"){
                    return $property->getName();
                }
            }

            private function reflectasloopvar(array  $vars){
                foreach($vars as $atribute){
                    $var = $this->getIdentityVarname($atribute);
                }

                return $var;
            }

            private function ExecuteCreate($sql){
                $pdo = Connection::Conect();
                try {
                    $pdo->exec($sql);
                } catch (\Throwable $th) {
                    $error = '(errno: 150 "Foreign key constraint is incorrectly formed")';
                    if(strpos($th->getMessage(),$error) !== false){
                        echo "<script>
                            location.reload();
                        </script>";
                        echo "[WARING] ==> Tabela com chave estangeira em criação Reinicie o Servidor ate essa mensagem sumir para completarmos as criaçoes";
                    }
                }
            }

            private function ExecuteSelect($sql){
                $pdo = Connection::Conect();

                try {
                    $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

                    return $results;
                } catch (\Throwable $th) {
                    echo $th->getMessage();
                }
            }

        }
    }

?>
