<?php
    namespace matrixOrm;

    use ReflectionClass;
    use ReflectionProperty;
    use BadMethodCallException;

    /**
     * @teble
     */
    class DbManager {
        private $conn = null;
        private static $loadedClass = [];

        public function setConectContext($conn){
            $this->conn = $conn;
        }

        public static function getClassContext(){
            print_r(self::$loadedClass);
        }


        public function findAll(){
            $table = get_called_class();
            return "SELECT * FROM $table";
        }

        public function __call($method, $args){
            $reflection = new ReflectionClass($this);
            $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
            $table = get_called_class();

            foreach($reflectionVars as $var){
                $exopected = 'findBy' . ucfirst($var->getName());
                if (strpos($method, $exopected) === 0) {
                    $field = lcfirst(substr($method, 6));
                    $value = $args[0];
                    return "SELECT * FROM $table WHERE $field = '$value'";
                } else {
                    throw new BadMethodCallException("Método $method não encontrado.");
                }
            }
        }

        public function Create(){
            self::$loadedClass = array_unique(DbLoader::load());
            $reflection = new ReflectionClass($this);
            $reflectionVars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
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

            return $sql;
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

        private function hasTableAnnotation(ReflectionProperty $property){
            $docComment = $property->getDocComment();
            return strpos($docComment, '@teble') !== false;
        }

    }

?>
