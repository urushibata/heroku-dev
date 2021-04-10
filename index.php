<?php

require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use app\aws\Sns;

$log = new Logger('index');
$log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

try {
    $sns = new Sns();
    $subscribe = $sns->subscribe();
    if ($subscribe && $subscribe['type'] === Sns::NOTIFICATION) {
        $subject = $subscribe['subject'];
        $message = $subscribe['message'];

        $container_overrides = $message->detail->overrides->containerOverrides[0];
        $command = $container_overrides->command;
        $log->info("command: " . var_export($command, true));

        $shell = $command[0];
        $pdf = $command[1];

        $info = pathinfo($pdf);
        $sort_pdf = $info['dirname'] . '/' . $info['filename'] . '/' . $info['basename'];

        $log->info('shell: ' . $shell);
        $log->info('pdf: ' . $pdf);
        $log->info('Sort_pdf: ' . $sort_pdf);
    }
} catch (\Exception $e) {
    $log->error($e->getMessage());
}

$header = var_export(getallheaders(), true);
$get = var_export($_GET, true);
$post = var_export($_POST, true);

$log->info("HEADER: ${header}");
$log->info("GET: ${get}");
$log->info("POST: ${post}");

?>

<h1>Heroku top page!</h1>

<hr>
<h2>Http</h2>
<hr>
<div>
    HEADER: <?= $header ?>
</div>
<div>
    GET: <?= $get ?>
</div>
<div>
    POST: <?= $post ?>
</div>