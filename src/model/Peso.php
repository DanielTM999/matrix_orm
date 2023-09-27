<?php

    use matrixOrm\DbManager;
    include "./src/MappingQuerys/DbManager.php";

    /**
     * @teble
     */
    class Pessoa2 extends DbManager

    {
        /**
         * @var identity
         */
        private $id;
        /**
         * @var varchar
         */
        private $nome;
        /**
         * @var int
         */
        private $idade;
        /**
         * @OneToMany
         */
        private $pessoa;
    }


?>
