<?php

    use matrixOrm\DbLoader;
    include "./src/MappingQuerys/DbLoader.php";
    include "../OrmPhp/src/model/Pessoa.php";
    include "../OrmPhp/src/model/Peso.php";


    DbLoader::init();
    $tes = new Pessoa();



?>

