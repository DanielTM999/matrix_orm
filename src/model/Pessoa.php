<?php

    use matrixOrm\DbManager;
    include "./src/MappingQuerys/DbManager.php";

    /**
     * @teble
     */
    class Pessoa extends DbManager{
        /**
         * @var identity
         */
        private $id;
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

    }
