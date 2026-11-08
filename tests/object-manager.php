<?php

require __DIR__ . '/bootstrap.php';
$kernel = new App\Kernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
