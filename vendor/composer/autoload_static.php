<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0a209a920303fb66695daa3296057f95
{
    public static $prefixLengthsPsr4 = array (
        'd' => 
        array (
            'danieltm\\matrix-orm\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'danieltm\\matrix-orm\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/MappingQuerys',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0a209a920303fb66695daa3296057f95::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0a209a920303fb66695daa3296057f95::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0a209a920303fb66695daa3296057f95::$classMap;

        }, null, ClassLoader::class);
    }
}
