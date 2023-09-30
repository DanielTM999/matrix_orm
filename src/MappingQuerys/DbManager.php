<?php
    namespace matrixOrm;

    use Exception;
    use ReflectionClass;
    use ReflectionProperty;
    use PDO;


    if (!class_exists('matrixOrm\DbManager')){
        class DbManager {
            private static $loadedClass = [];
            private $format = false;


            public static function getClassContext(){
                return self::$loadedClass;
            }

            public function setFormat(bool $format){
                $this->format = $format;
            }

            public function findAll(){
                $reflection = new ReflectionClass($this);
                $table = strtolower($reflection->getName());
                $sql = "SELECT * FROM $table";
                $response = $this->ExecuteSelect($sql);
                if($this->format){
                    return $this->formatResponse($reflection, $response);
                }else{
                    return $response;
                }
            }

            public function findById($id, $withJoin = true){
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $table = strtolower($reflection->getName());
                $var = $this->reflectasloopvar($reflectionVars);
                $sql = $this->genericSelect($table, $reflectionVars, $var, $id, $withJoin);
                $response = $this->ExecuteSelect($sql[0]["Mainquery"]);
                for ($i=0; $i < count($sql); $i++) {
                    $intern = $this->ExecuteSelect($sql[$i]["query"]);
                    $expected = strtolower($sql[0]["var"]);
                    for ($j=0; $j < count($response); $j++){
                        $response[$j][$expected] =  $intern;
                    }
                }
                if($this->format){
                    return $this->formatResponse($reflection, $response);
                }else{
                    return $response;
                }


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
                        if(isset($args[1])){
                            if(is_bool($args[1])){
                                $sql = $this->genericSelect($table, $reflectionVars, $field, $value, $args[1]);
                            }else{
                                $sql = $this->genericSelect($table, $reflectionVars, $field, $value);
                            }
                        }else{
                            $sql = $this->genericSelect($table, $reflectionVars, $field, $value);
                        }
                        $response = $this->ExecuteSelect($sql[0]["Mainquery"]);
                        for ($i=0; $i < count($sql); $i++) {
                            $intern = $this->ExecuteSelect($sql[$i]["query"]);
                            $expected = strtolower($sql[0]["var"]);
                            for ($j=0; $j < count($response); $j++){
                                $response[$j][$expected] =  $intern;
                            }
                        }
                        if($this->format){
                            return $this->formatResponse($reflection, $response);
                        }else{
                            return $response;
                        }
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
                    if($var !== null){
                        return $var;
                    }

                }

            }

            private function conteinsClassPropatyOne(array  $vars){
                $varToJoin = [];
                if(count(self::$loadedClass) == 0 ){
                    self::$loadedClass = array_unique(DbLoader::load());
                }
                foreach($vars as $atribute){
                    foreach(self::$loadedClass as $classload){
                        if(strtolower($atribute->getName()) === strtolower($classload)){
                            $typeManyOrOne = $this->hasTableRelation($atribute) ."<br>";
                            if(strpos($typeManyOrOne, "1x1") === 0){
                                $varToJoin[] = $classload;
                            }
                        }
                    }
                }

                return  $varToJoin;
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

            private function insert(DbManager $entity){
                $reflection = new ReflectionClass($entity);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
            }

            private function formatResponse(ReflectionClass $reflection, $response){
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $methods = $reflection->getMethods();
                if(count($response) === 1){
                    foreach($methods as $method){
                        foreach($reflectionVars as $var){
                            if(strtolower($method->getName()) === strtolower("set".$var->getName())){
                                $method->invoke($this, $response[0][$var->getName()]);
                            }
                        }
                    }
                    unset($reflection);
                    unset($reflectionVars);
                }elseif(count($response) > 1){
                    $arrayObject = [];
                    foreach($response as $dbres){
                        $newInstance = new $this;
                        $reflectionnovo = new ReflectionClass($newInstance);
                        $methods = $reflectionnovo->getMethods();
                        foreach($methods as $method){
                            foreach($reflectionVars as $var){
                                if(strtolower($method->getName()) === strtolower("set".$var->getName())){
                                    $method->invoke($newInstance, $dbres[$var->getName()]);
                                }
                            }
                        }
                        $arrayObject[] = $newInstance;
                    }

                    if(count($arrayObject) > 0 && $arrayObject !== null){
                        $error = true;
                        foreach($arrayObject as $res){
                            $verificlass = new ReflectionClass($res);
                            $reflectionVars = $verificlass->getProperties(ReflectionProperty::IS_PRIVATE);
                            foreach($reflectionVars as $var){
                                $var->setAccessible(true);
                                $propertyValue = $var->getValue($this);

                            }
                        }
                        unset($verificlass);
                    }else{
                        $error = true;
                    }

                    unset($reflection);
                    unset($reflectionVars);
                    if($error){
                        return $response;
                    }else{
                        return $arrayObject;
                    }
                }

            }

            private function genericSelect($table, $reflectionVars, $var, $id, $withJoin = true){
                $query = [];
                $sql = "SELECT * FROM $table ";
                $props = $this->conteinsClassPropatyOne($reflectionVars);
                if(count($props) > 0 && $withJoin){
                    foreach($props as $classtojion){
                        $reflectionInternal = new ReflectionClass($classtojion);
                        $reflectionVarsinternal = $reflectionInternal->getProperties(ReflectionProperty::IS_PRIVATE);
                        $varId = $this->reflectasloopvar($reflectionVarsinternal);
                        $sql .= " JOIN $classtojion ON $table.$classtojion = $classtojion.$varId ";
                        $sql .= "WHERE $table.$var = '$id';";
                        unset($reflectionInternal);
                        unset($reflectionVarsinternal);
                        $query[] = [
                            "Mainquery" => "SELECT * FROM $table WHERE $table.$var = '$id'",
                            "query" => $sql,
                            "var" => $classtojion
                        ];
                    }
                }else{
                    $query[] = [
                        "query" => $sql."WHERE $table.$var = '$id'",
                    ];
                }
                return $query;
            }

        }
    }

?>
