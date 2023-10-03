<?php

use danieltm\matrix_orm\DbManager;

    /**
     * @teble
     */
    class Casa extends DbManager{

        /**
         * @var identity
         */
        private $id_casa;

        /**
         *
         */
        private $pessoa;

        /**
         * @var varchar
         * @notnull
         */
        private $rua;

        /**
         * @var int
         * @notnull
         */
        private $numero;


        function __construct($rua = "", $numero = 0, $pessoa = null)
        {
            $this->rua = $rua;
            $this->numero = $numero;
            $this->pessoa = $pessoa;
        }
    }
