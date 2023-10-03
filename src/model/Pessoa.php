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
         * @unique
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

    }
