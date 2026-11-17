<?php
return ['commands' => [
 'migrate:run' => \app\command\MigrateRun::class,
 'migrate:status' => \app\command\MigrateStatus::class,
 'admin:create' => \app\command\AdminCreate::class,
]];
