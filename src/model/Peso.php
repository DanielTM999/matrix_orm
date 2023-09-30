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
        private $id_p2;
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

        public function getId_p2(){
            return $this->id_p2;
        }
        public function setId_p2($id){
            $this->id_p2 = $id;
        }

    }


?>
