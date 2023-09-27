<?php

    use matrixOrm\DbManager;
    include "./src/MappingQuerys/DbManager.php";

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
         * @OneToOne
         */
        private $pessoa;
    }


?>
