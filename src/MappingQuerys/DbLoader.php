<?php
    namespace matrixOrm;
    use ReflectionClass;


    class DbLoader {

        private static $rootDirectory = __DIR__;
        private static $classesWithAnnotation = [];
        private static $classFiles = [];
        private static $initCalled = false;

        public static function autoloader(){
            self::load();
            foreach(self::$classFiles as $incleders){
                if(strpos($incleders,"DbLoader.php") === false && strpos($incleders,"DbManager.php") === false){
                    include $incleders;
                }
            }
        }

        public static function init(){
            if (!self::$initCalled) {
                self::load();
                foreach(self::$classesWithAnnotation as $classinst){
                    if($classinst !== "DbLoader" && $classinst !== "DbManager" && $classinst !== "Con"){
                        try {
                            $reflection = new ReflectionClass($classinst);
                            $reflectionMethods = $reflection->newInstance();
                            $reflectionMethods->Create();
                            unset($reflectionMethods);
                            unset($reflection);
                        } catch (\Throwable $th) {
                            echo $th->getMessage();
                        }
                    }
                }
                self::$initCalled = true;
            }
        }

        public static function load(){
            if(count(self::$classesWithAnnotation) <= 0){
                self::scanDirectory(dirname(self::$rootDirectory), self::$classesWithAnnotation);
            }
            return self::$classesWithAnnotation;
        }



        private static function findClassesWithAnnotation($file, &$classesWithAnnotation, $dir) {
            $fileContent = file_get_contents($file);
            if (strpos($fileContent, '@teble') !== false) {
                if (preg_match('/class\s+([^\s]+)/', $fileContent, $matches)) {
                    $className = $matches[1];
                    $classesWithAnnotation[] = substr($className, 0, strlen($className));
                    self::$classFiles[substr($className, 0, strlen($className) - 1)] = $file;
                }
            }
        }

        private static function scanDirectory($dir, &$classesWithAnnotation){
            $files = glob($dir . '/*');

            foreach ($files as $file) {
                if (is_dir($file)) {
                    self::scanDirectory($file, $classesWithAnnotation);
                } elseif (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                    self::findClassesWithAnnotation($file, $classesWithAnnotation, dirname($file));
                }
            }
        }

    }

?>
