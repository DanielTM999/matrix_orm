<?php

    use matrixOrm\DbLoader;
    include "./src/MappingQuerys/DbLoader.php";

    DbLoader::autoloader();
    DbLoader::init();

    $pessoa = new Pessoa("teste", 25, "F");


?>

