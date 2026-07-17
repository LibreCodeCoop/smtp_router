<?php

declare(strict_types=1);

use OCA\SmtpRouter\Command\Route\GetCommand;
use OCA\SmtpRouter\Command\Route\SetCommand;
use OCP\Server;

$application->add(Server::get(GetCommand::class));
$application->add(Server::get(SetCommand::class));
