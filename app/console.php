#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;


require(__DIR__ . '/init.php');

$app->boot();

$console = new Application('sample console');

$console->run();