<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\cmd\SortCommand;

// 显示帮助信息
if (in_array('-h', $argv) || in_array('--help', $argv)) {
    echo SortCommand::getHelp();
    exit(0);
}

try {
    $command = new SortCommand();
    $result = $command->run($argv);
    echo implode(',', $result) . PHP_EOL;
} catch (\InvalidArgumentException $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL . PHP_EOL);
    echo SortCommand::getHelp();
    exit(1);
}
