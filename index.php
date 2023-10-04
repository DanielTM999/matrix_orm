<?php


    include "./vendor/autoload.php";
    use danieltm\matrix_orm\DbLoader;

    DbLoader::autoloader();
    DbLoader::init();

    $casa = new Casa();
    $casa->setFormat(true);
    $casa->findByPessoa("teste");



?>
