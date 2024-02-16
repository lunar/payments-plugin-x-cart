<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite14fc6365c6815581ef34018ebf415b5
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'Lunar\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Lunar\\' => 
        array (
            0 => __DIR__ . '/..' . '/lunar/payments-api-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite14fc6365c6815581ef34018ebf415b5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite14fc6365c6815581ef34018ebf415b5::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}