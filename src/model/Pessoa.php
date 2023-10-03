<?php

use danieltm\matrix_orm\DbManager;

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
         * @notnull
         */
        private $nome;
        /**
         * @var int
         * @notnull
         */
        private $idade;


        function __construct($nome = "", $idade = 0)
        {
            $this->nome = $nome;
            $this->idade = $idade;
        }
    }
