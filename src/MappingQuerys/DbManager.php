<?php
    namespace danieltm\matrix_orm;
    use Exception;
    use ReflectionClass;
    use ReflectionProperty;
    use PDO;


    if (!class_exists('matrixOrm\DbManager')){
        class DbManager {
            private static $loadedClass = [];
            private $format = true;


            public static function getClassContext(){
                return self::$loadedClass;
            }

            public function setFormat(bool $format){
                $this->format = $format;
            }

            public function QueryMaker($sql){
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $identity = $this->reflectasloopvar($reflectionVars);
                $privates_words = ["@table", "@id"];
                $data = [strtolower(get_called_class()), "$identity"];
                $tokens = explode(" ", $sql);
                $type = "";
                for($i = 0; $i < count($tokens); $i++){
                    $type = $tokens[0];
                    for($j = 0; $j < count($privates_words); $j++){
                        if(strtolower($tokens[$i]) === $privates_words[$j]){
                            $tokens[$i] = $data[$j];
                        }
                    }
                }
                unset($reflectionVars);
                unset($reflection);
                $sql = implode(" ", $tokens);
                $sql .= ";";
                return $this->autoselectSQL_Type($type, $sql);
            }

            public function findAll($withJoin = true){
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $props = $this->conteinsClassPropatyOne($reflectionVars);
                $table = strtolower($reflection->getName());
                $sql = "SELECT * FROM $table ";
                $responseBase = $this->ExecuteSelect("SELECT * FROM $table;");
                foreach($props as $classtoload){
                    $reflectionInternal = new ReflectionClass($classtoload);
                    $reflectionVarsinternal = $reflectionInternal->getProperties(ReflectionProperty::IS_PRIVATE);
                    $varId = $this->reflectasloopvar($reflectionVarsinternal);
                    $class = strtolower($classtoload);
                    $sql .= " JOIN $classtoload ON $table.$class = $class.$varId;";
                    $response = $this->ExecuteSelect($sql);
                    $entity = [];
                    for ($i=0; $i < count($responseBase); $i++) {
                        if(isset($response)){
                            foreach($response as $data){
                                if($responseBase[$i][$class] === $data[$class]){
                                    $entity = $data;
                                }
                            }
                            $responseBase[$i][$class] = $entity;
                        }
                    }
                }
                $idThisClass = $this->reflectasloopvar($reflectionVars);
                foreach($reflectionVars as $var){
                    foreach(self::$loadedClass as $classtoload){
                        if(strtolower($var->getName()) === strtolower($classtoload)){
                            for ($i=0; $i < count($responseBase); $i++){
                                if($this->oneToMany($var)){
                                    $reflectionInternal = new ReflectionClass($var->getName());
                                    $reflectionVarsinternal = $reflectionInternal->getProperties();
                                    $idValue = $responseBase[$i][$idThisClass];
                                    $SubSql = "SELECT * FROM $classtoload WHERE $table = $idValue";
                                    $response = $this->ExecuteSelect($SubSql);
                                    $responseBase[$i][$var->getName()] = $response;
                                    unset($reflectionInternal);
                                    unset($reflectionVarsinternal);

                                }
                            }
                        }
                    }
                }

                if($this->format){
                    return $this->FormatResponseBeta($reflection, $responseBase);
                }else{
                    return $responseBase;
                }
            }

            public function findById($id, $withJoin = true){
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $table = strtolower($reflection->getName());
                $var = $this->reflectasloopvar($reflectionVars);
                $sql = $this->genericSelect($table, $reflectionVars, $var, $id, $withJoin);
                $response = $this->ExecuteSelect($sql[0]["Mainquery"]);
                if(isset($sql[0]["query"])){
                    for ($i=0; $i < count($sql); $i++) {
                        $intern = $this->ExecuteSelect($sql[$i]["query"]);
                        $expected = strtolower($sql[0]["var"]);
                        for ($j=0; $j < count($response); $j++){
                            $response[$j][$expected] =  $intern;
                        }
                    }
                }


                if($this->format){
                    return $this->FormatResponseBeta($reflection, $response);
                }else{
                    unset($reflectionVars);
                    unset($reflection);
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
                    $expectedDelete = 'deleteBy'. ucfirst($var->getName());
                    $expectedGet = 'get'. ucfirst($var->getName());
                    $expectedSet = 'set'. ucfirst($var->getName());

                    foreach(self::$loadedClass as $class){
                        if(ucfirst($var->getName()) === ucfirst($class)){
                            if (strpos($method, $exopected) === 0 || strpos($method, $expectedLow) === 0){
                                if(is_object($args[0])){
                                    $sql = $this->findByesternalclass($class, $reflection, $args[0]);
                                    $response = $this->ExecuteSelect($sql[0]["Mainquery"]);
                                    for ($i=0; $i < count($sql); $i++) {
                                        if(isset($sql[$i]["query"])){
                                            $intern = $this->ExecuteSelect($sql[$i]["query"]);
                                            $expected = strtolower($sql[0]["var"]);
                                            for ($j=0; $j < count($response); $j++){
                                                $response[$j][$expected] =  $intern;
                                            }
                                        }
                                    }
                                    if($this->format){
                                        return $this->FormatResponseBeta($reflection, $response);
                                    }else{
                                        unset($reflectionVars);
                                        unset($reflection);
                                        return $response;
                                    }
                                }

                            }
                        }
                    }

                    if (strpos($method, $exopected) === 0 && strpos($method, $expectedLow) === 0 && false) {
                        $value = $args[0];
                        $field = lcfirst(substr($method, 6));
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
                            if(isset($sql[$i]["query"])){
                                $intern = $this->ExecuteSelect($sql[$i]["query"]);
                                $expected = strtolower($sql[0]["var"]);
                                for ($j=0; $j < count($response); $j++){
                                    $response[$j][$expected] =  $intern;
                                }
                            }
                        }
                        if($this->format){
                            return $this->FormatResponseBeta($reflection, $response);
                        }else{
                            unset($reflectionVars);
                            unset($reflection);
                            return $response;
                        }
                    }elseif(strtolower($method) === strtolower($expectedDelete)){
                        $value = $args[0];
                        $field = lcfirst(substr($method, 8));
                        $sql = "DELETE FROM $table WHERE $field = '$value';";
                        $response = $this->ExecuteDelete($sql);
                        if($response === 1){
                            unset($reflectionVars);
                            unset($reflection);
                            return true;
                        }else{
                            unset($reflectionVars);
                            unset($reflection);
                            return false;
                        }
                    }elseif(strtolower($method) === strtolower($expectedGet)){
                        $var->setAccessible(true);
                        $results = $var->getValue($this);
                        $var->setAccessible(false);
                        unset($reflectionVars);
                        unset($reflection);
                        return $results;
                    }elseif(strtolower($method) === strtolower($expectedSet)){
                        $value = $args[0];
                        $var->setAccessible(true);
                        $results = $var->setValue($this, $value);
                        $var->setAccessible(false);
                        unset($reflectionVars);
                        unset($reflection);
                    }

                }



            }

            public function Update(DbManager $entity){
                $table = strtolower(get_called_class());
                $sql = "UPDATE $table SET ";
                $reflection = new ReflectionClass($this);
                $reflectionVars = $reflection->getProperties();
                $identity = $this->reflectasloopvar($reflectionVars);
                $identityValue = 0;
                foreach($reflectionVars as $var){
                    $varName = $var->getName();
                    $isNoClass = true;
                    $className = null;
                    if($varName === $identity){
                        $var->setAccessible(true);
                        $identityValue = $var->getValue($entity);
                        $var->setAccessible(false);
                    }
                    foreach(self::$loadedClass as $class){
                        if(strtolower($class) === $var->getName()){
                            $isNoClass = false;
                            $className = $class;
                        }
                    }
                    if($isNoClass){
                        $var->setAccessible(true);
                        $value = $var->getValue($entity);
                        if($varName !== $identity){
                            $sql .= " $varName = '$value', ";
                        }
                        $var->setAccessible(true);
                        $value = $var->getValue($entity);
                    }else{
                        if(isset($className)){
                            $var->setAccessible(true);

                            $objectIntance = $var->getValue($entity);
                            $value = $this->getIdvalueClass($objectIntance);

                            $var->setAccessible(false);
                        }
                        if(isset($value)){
                            $sql .= " $varName = '$value', ";
                        }else{
                            $timezone = "America/Sao_Paulo";
                            date_default_timezone_set($timezone);
                            $dataHoraAtual = date('[d-m-Y] [H:i:s]');
                            echo "[ WARING ] ==> [$dataHoraAtual]{'$className'} Não encontrado, Sem altererações nesse compo!";
                        }
                    }

                }
                $sql = rtrim($sql, ', ') . " WHERE $identity = $identityValue";

                return $this->ExecuteUpdate($sql);
            }

            public function save(DbManager $entity){
                $reflection = new ReflectionClass($entity);
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $identity = $this->reflectasloopvar($reflectionVars);
                $table = strtolower($reflection->getName());
                $sql = "INSERT INTO $table(";
                $state = "(";
                $data = [];
                foreach($reflectionVars as $var){
                    if($var->getName() !== $identity){
                        $lasid = $this->conteinsExternalClassInsert($var, $entity);
                        $atribute = $var->getName();
                        $sql .= "$atribute,";
                        $var->setAccessible(true);
                        if(isset($lasid[0]) && isset($lasid[1])){
                            if($lasid[0]){
                                $data[] = $lasid[1];
                            }else{
                                $data[] = $var->getValue($entity);
                            }
                        }else{
                            $data[] = $var->getValue($entity);
                        }
                        $var->setAccessible(false);
                    }
                }

                foreach($data as $element){
                    $state .= "'$element', ";
                }

                $sql = rtrim($sql, ', ') . ") VALUES ";
                $state = rtrim($state, ', ') . ");";
                $sql = $sql . $state;
                $response = $this->ExecuteInsert($sql);
                unset($reflectionVars);
                unset($reflection);
                return $response;

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
                    $unique = $this->hasUnique($property);
                    if ($propertyType === 'identity') {
                        $sql .= "$propertyName INT AUTO_INCREMENT PRIMARY KEY";
                    } else {
                        $nonconfig = true;
                        $sql .= $this->choiceTypeAtrubite($propertyType, $propertyName, $nonconfig, $unique);
                        foreach(self::$loadedClass as $classGetatribute){
                            if($classGetatribute !== "DbManager" && $classGetatribute !== "DbLoader"){
                                if($this->oneToMany($property)){
                                    $nonconfig = false;
                                }
                                if(strtolower($propertyName) === strtolower($classGetatribute) && !$this->oneToMany($property)){
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

                    if(!$this->oneToMany($property)){
                        $sql .= ",";
                    }
                }

                $sql = rtrim($sql, ",");
                $sql .= ");";

                unset($reflection);
                unset($reflectionVars);
                if($controll > 0){
                  return $this->ExecuteCreate($sql);
                }
            }

            private function getIdvalueClass($object){
                $reflection = new ReflectionClass($object);
                $reflectionVars = $reflection->getProperties();
                $identity = $this->reflectasloopvar($reflectionVars);

                foreach($reflectionVars as $var){
                    $varName = $var->getName();
                    if($varName === $identity){
                        $var->setAccessible(true);
                        $identityValue = $var->getValue($object);
                        $var->setAccessible(false);
                    }
                }
                unset($reflection);
                unset($reflectionVars);
                return $identityValue;
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

            private function hasUnique(ReflectionProperty $property){
                $docComment = $property->getDocComment();
                return strpos($docComment, '@unique') !== false;
            }

            private function hasTableRelation(ReflectionProperty $property){
                $docComment = $property->getDocComment();
                if(strpos($docComment, '@OneToMany') !== false){
                    return "1xn";
                }else{
                    return "1x1";
                }
            }

            private function oneToMany(ReflectionProperty $property){
                $docComment = $property->getDocComment();
                if(strpos($docComment, '@OneToMany') !== false){
                    return true;
                }else{
                    return false;
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
                    return $pdo->exec($sql);
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

            private function ExecuteDelete($sql){
                $pdo = Connection::Conect();
                try {
                   return $pdo->exec($sql);
                } catch (\Throwable $th) {
                    throw new Exception($th->getMessage());
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

            private function ExecuteInsert($sql){
                $pdo = Connection::Conect();
                $response = [];
                try {
                    $status = $pdo->exec($sql);
                    if($status === 1 ){
                        $response[] = true;
                    }else{
                        $response[] = false;
                    }
                    $response[] = $pdo->lastInsertId();
                    return $response;
                } catch (\Throwable $th) {
                    return $th->getMessage();
                }
            }

            private function ExecuteUpdate($sql){
                $pdo = Connection::Conect();
                try {
                    $status = $pdo->exec($sql);
                    if($status === 1 ){
                        return true;
                    }else{
                        return false;
                    }
                } catch (\Throwable $th) {
                    return $th->getMessage();
                }
            }

            private function FormatResponseBeta(ReflectionClass $reflection, $response){
                $List = [];
                if(count($response) > 0){
                    try {
                        $this->isSingle($response);
                        $this->formatSingle($reflection, $response, $this);
                    } catch (\Throwable $th) {
                        $List = $this->formatMutiple($reflection, $response, $this);
                        return $List;
                    }
                }
            }

            private function formatSingle(ReflectionClass $reflection, $response, $Instance){
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                foreach($reflectionVars as $var){
                    $object = $this->subFormat($var, $response[0], $Instance);
                    $varName = $var->getName();
                    $var->setAccessible(true);
                    if($object !== null){
                        $var->setValue($Instance, $object);
                    }else{
                        $var->setValue($Instance, $response[0][$varName]);
                    }
                    $var->setAccessible(false);
                }

            }

            private function formatMutiple(ReflectionClass $reflection, $response, $Instance){
                $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                $instances = [];
                for($i = 0; $i < count($response); $i++){
                    $instance = $reflection->newInstance();
                    foreach($reflectionVars as $var){
                        $object = $this->subFormatMult($var, $response[$i]);
                        $varName = $var->getName();
                        $var->setAccessible(true);
                        if(isset($object)){
                            $var->setValue($instance, $object);
                        }else{
                            $var->setValue($instance, $response[$i][$varName]);
                        }
                        $var->setAccessible(false);
                    }
                    $instances[] = $instance;
                }

                return $instances;

            }

            private function subFormatMult(ReflectionProperty $reflection, $data){
                foreach(self::$loadedClass as $class){
                    if(strtolower($class) === strtolower($reflection->getName())){
                        $newClass = new ReflectionClass($class);
                        $reflectionVars = $newClass->getProperties(ReflectionProperty::IS_PRIVATE);
                        $instance = $newClass->newInstance();
                        foreach($reflectionVars as $var){
                            $object = $this->subFormatMult($var, $data[strtolower($class)][$var->getName()]);
                            if($object !== null && isset($object)){
                                $this->formatMutiple($newClass, $data[strtolower($class)], $instance);
                            }
                            $var->setAccessible(true);
                            if(isset($data[strtolower($class)][$var->getName()])){
                                $var->setValue($instance, $data[strtolower($class)][$var->getName()]);
                            }
                        }
                        return $instance;
                    }
                }

                return null;
            }

            private function subFormat(ReflectionProperty $reflection, $data, $instancecs){
                foreach(self::$loadedClass as $class){
                    if(strtolower($class) === strtolower($reflection->getName())){
                        $newClass = new ReflectionClass($class);
                        $instance = $newClass->newInstance();
                        try {
                            if(isset($data[strtolower($class)])){
                                $this->isSingle($data[strtolower($class)]);
                                $this->formatSingle($newClass, $data[strtolower($class)], $instance);
                            }
                        } catch (\Throwable $th) {
                            if($this->oneToMany($reflection)){
                                $instancesOfClass = $this->addAllMany($data, strtolower($class));
                                return $instancesOfClass;
                            }

                        }

                        return $instance;
                    }
                }

                return null;
            }

            private function addAllMany($data, $className){
                $itens = $data[strtolower($className)];
                $reflection = new ReflectionClass($className);
                $vars = $reflection->getProperties();
                $instances = [];

                foreach($itens as $iten){
                    $instance = $reflection->newInstance();
                    foreach($vars as $var){
                        $var->setAccessible(true);
                        $var->setValue($instance, $iten[$var->getName()]);
                        $var->setAccessible(false);
                    }
                    $instances[] = $instance;
                    unset($instance);
                }

                return $instances;

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
                        "Mainquery" => $sql."WHERE $table.$var = '$id'"
                    ];
                }
                return $query;
            }

            private function conteinsExternalClassInsert(ReflectionProperty $property, DbManager $entity){
                foreach(self::$loadedClass as $class){
                    if($property->getName() === strtolower($class)){
                        $property->setAccessible(true);
                        $subentity = $property->getValue($entity);
                        $reflection = new ReflectionClass($subentity);
                        $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
                        $identity = $this->reflectasloopvar($reflectionVars);
                        $table = strtolower($reflection->getName());
                        $sql = "INSERT INTO $table (";
                        $state = "(";
                        $data = [];
                        foreach($reflectionVars as $var){
                            if($var->getName() !== $identity){
                                $this->conteinsExternalClass($var, $subentity);
                                $atribute = $var->getName();
                                $sql .= "$atribute, ";
                                $var->setAccessible(true);
                                $data[] = $var->getValue($subentity);
                                $var->setAccessible(false);
                            }
                        }

                        foreach($data as $element){
                            $state .= "'$element', ";
                        }

                        $sql = rtrim($sql, ', ') . ") VALUES ";
                        $state = rtrim($state, ', ') . ");";
                        $sql = $sql . $state;
                        unset($reflection);
                        unset($reflectionVars);
                        $response = $this->ExecuteInsert($sql, $data);
                        return $response;
                    }
                }
            }

            private function choiceTypeAtrubite($propertyType, $propertyName, &$nonconfig, bool $unique){
                $sql = "";
                if ($propertyType === 'int') {
                    $sql = "$propertyName INT ";
                    $nonconfig = false;
                } elseif ($propertyType === 'float') {
                    $sql = "$propertyName FLOAT";
                    $nonconfig = false;
                } elseif ($propertyType === 'varchar') {
                    $sql = "$propertyName VARCHAR(255)";
                    $nonconfig = false;
                }elseif($propertyType === "json"){
                    $sql = "$propertyName JSON";
                    $nonconfig = false;
                }elseif($propertyType === "text"){
                    $sql = "$propertyName TEXT";
                    $nonconfig = false;
                }elseif($propertyType === "enum"){
                    $sql = "$propertyName ENUM";
                    $nonconfig = false;
                }elseif($propertyType === "bit"){
                    $sql = "$propertyName BIT";
                    $nonconfig = false;
                }elseif($propertyType === "uuid"){
                    $sql = "$propertyName UUID";
                    $nonconfig = false;
                }elseif($propertyType === "boolean"){
                    $sql = "$propertyName BOOLEAN";
                    $nonconfig = false;
                }elseif($propertyType === "datetime"){
                    $sql = "$propertyName DATETIME";
                    $nonconfig = false;
                }elseif($propertyType === "time"){
                    $sql = "$propertyName TIME";
                    $nonconfig = false;
                }elseif($propertyType === "date"){
                    $sql = "$propertyName DATE";
                    $nonconfig = false;
                }elseif($propertyType === "blob"){
                    $sql = "$propertyName BLOB";
                    $nonconfig = false;
                }

                if($unique){
                    $sql =  $sql . " UNIQUE";
                }

                if(!empty($sql)){
                    return $sql;
                }
            }

            private function isSingle($response){
                if(count($response) === 1){
                    return;
                }

                throw new Exception("fomatação multipla");
            }

            private function autoselectSQL_Type($identity, $sql){
                $identity = strtolower($identity);
                if($identity === "insert"){
                    return $this->ExecuteInsert($sql);
                }elseif($identity === "delete"){
                    return $this->ExecuteDelete($sql);
                }elseif($identity === "select"){
                    return $this->ExecuteSelect($sql);
                }
            }

            private function findByesternalclass($className, ReflectionClass $thisReflextion, $object){
                $table = $thisReflextion->getName();
                $reflectionVars = $thisReflextion->getProperties();
                foreach($reflectionVars as $var){
                    if(strtolower($className) === strtolower($var->getName())){
                        $reflection = new ReflectionClass($className);
                        $vars = $reflection->getProperties();
                        $id = $this->reflectasloopvar($vars);
                        foreach($vars as $expectedId){
                            if($expectedId->getName() === $id){
                                $expectedId->setAccessible(true);
                                $classWhere =  $expectedId->getValue($object);
                                $expectedId->setAccessible(false);
                            }
                        }
                    }
                }

                $sql = $this->genericSelect($table, $reflectionVars, $className, $classWhere);
                return $sql;
            }

        }
    }

?>
