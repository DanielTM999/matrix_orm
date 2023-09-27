<?php

    use matrixOrm\DbLoader;
    include "./src/MappingQuerys/DbLoader.php";

    DbLoader::init();
    $tes = new Pessoa();
    print_r($tes::getClassContext());


?>

