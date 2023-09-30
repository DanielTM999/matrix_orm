<?php

    use matrixOrm\DbLoader;
    include "./src/MappingQuerys/DbLoader.php";


    DbLoader::autoloader();
    DbLoader::init();

    $tes = new Pessoa2();
    $tes->setId(1);
    $tes->save($tes);





?>

