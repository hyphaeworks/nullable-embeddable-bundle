<?php

declare(strict_types=1);

use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\TestKernel;

require __DIR__.'/../vendor/autoload.php';

$kernel = new TestKernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine.orm.entity_manager');
