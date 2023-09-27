<?php

    use matrixOrm\DbManager;

    /**
     * @teble
     */
    class Pessoa2 extends DbManager
    {
        /**
         * @var varchar
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


?>
