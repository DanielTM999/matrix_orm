<?php

    use matrixOrm\DbLoader;
    use matrixOrm\Connection;
    include "./src/MappingQuerys/DbLoader.php";

    DbLoader::autoloader();
    DbLoader::init();
    echo Connection::ShowDirEnv();


?>

