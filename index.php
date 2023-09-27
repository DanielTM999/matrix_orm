<?php

    use matrixOrm\DbLoader;
    include "./src/MappingQuerys/DbLoader.php";

    $main = new DbLoader();
    $main->init();
    $tes = new Pessoa();
    print_r($tes::getClassContext());


?>

