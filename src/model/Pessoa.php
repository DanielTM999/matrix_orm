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

        function __construct($nome = "", $idade = "", $sexo = "")
        {
            $this->nome = $nome;
            $this->idade = $idade;
            $this->sexo = $sexo;
        }


        public function getId(){
            return $this->id;
        }

        public function setId($id){
            $this->id = $id;
        }
        public function getNome(){
            return $this->nome;
        }

        public function setNome($nome){
            $this->nome = $nome;
        }
        public function getIdade(){
            return $this->id;
        }

        public function setIdade($idade){
            $this->idade = $idade;
        }
        public function getSexo(){
            return $this->id;
        }

        public function setSexo($sexo){
            $this->sexo = $sexo;
        }

    }
