<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInite8f612cfcc2265a38336faf63804f5a4
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInite8f612cfcc2265a38336faf63804f5a4', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInite8f612cfcc2265a38336faf63804f5a4', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInite8f612cfcc2265a38336faf63804f5a4::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
