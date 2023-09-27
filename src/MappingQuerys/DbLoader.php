<?php
    namespace matrixOrm;
    use ReflectionClass;
    use ReflectionProperty;


    class DbLoader {

        private static $rootDirectory = __DIR__;
        private static $classesWithAnnotation = [];
        private static $classFiles = [];

        private static function spl_autoload_register() {
            foreach(self::$classFiles as $i){
                include_once $i;
            }
        }

        public static function init(){
            self::load();
            self::spl_autoload_register();

            foreach(self::$classesWithAnnotation as $classinst){
                if($classinst !== "DbLoader" && $classinst !== "DbManager"){
                    try {
                        $reflection = new ReflectionClass($classinst);
                        $reflectionMethods = $reflection->newInstance();
                        $reflectionMethods->Create();
                        $reflectionMethods = null;
                        $reflection = null;
                    } catch (\Throwable $th) {
                        echo $th->getMessage();
                    }
                }
            }
        }

        public static function load(){
            self::scanDirectory(dirname(self::$rootDirectory), self::$classesWithAnnotation);
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
