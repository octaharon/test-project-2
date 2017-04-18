#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Controllers\Console\LocaleJsGenerator;
use Controllers\Console\Rotate;
use Controllers\Console\EPG;
use Controllers\Console\Import;
use Controllers\Console\ImagesSetup;
use Controllers\Console\CronTab;
use Controllers\Console\CompetitorsCache;
use Controllers\Console\AdminMaker;
use Controllers\Console\Backup;
use Controllers\Console\ChannelsIdReplacer;
use Controllers\Console\CompetitorTournamentFiller;
use Controllers\Console\CompetitorsCloner;
use Controllers\Console\CompetitorsJoin;
use Controllers\Console\Migration;

require(__DIR__ . '/init.php');

$app->boot();

$console = new Application('TVsport console');
// Сюда добавлять новые консольные команды
$console->add(new LocaleJsGenerator($app));
$console->add(new Rotate($app));
$console->add(new Import($app));
$console->add(new ImagesSetup($app));
$console->add(new EPG($app));
$console->add(new CronTab($app));
$console->add(new CompetitorsCache($app));
$console->add(new AdminMaker($app));
$console->add(new Backup($app));
$console->add(new ChannelsIdReplacer($app));
$console->add(new CompetitorTournamentFiller($app));
$console->add(new CompetitorsCloner($app));
$console->add(new CompetitorsJoin($app));
$console->add(new Migration($app));
$console->run();