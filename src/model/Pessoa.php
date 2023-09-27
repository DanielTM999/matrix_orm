<?php

    use matrixOrm\DbManager;
    use matrixOrm\Connection;
    include "./src/MappingQuerys/DbManager.php";
    include "./src/MappingQuerys/Connection.php";

    /**
     * @teble
     */
    class Pessoa extends DbManager{
        /**
         * @var varchar
         *
         */
        private $nome;
        /**
         * @var int
         */
        private $idade;
        /**
         * @var varchar
         */
        private $sexo;
        /**
         * @var identity
         */
        private $id;

        public function getId(){
            return $this->id;
        }
        public function setId($id){
            $this->id = $id;
        }

    }
