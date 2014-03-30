<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\ClassLoader\UniversalClassLoader;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$loader2 = new UniversalClassLoader();
$loader2->registerPrefixes(array(
    'Tcpdf_'           => __DIR__.'/../vendor/tcpdf/lib',
));
$loader2->register();


return $loader;
