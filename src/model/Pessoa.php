<?php

    use matrixOrm\DbManager;
    include "./src/model/Pessoa.php";

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

    }
