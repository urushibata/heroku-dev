<?php

namespace app\aws;

require __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

/**
 * AWS Sns
 */
class Sns
{
    /**
     * @var string SUBSCRIPTION_CONFIRMATION
     */
    const SUBSCRIPTION_CONFIRMATION = 'SubscriptionConfirmation';

    /**
     * @var string NOTIFICATION
     */
    const NOTIFICATION = 'Notification';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->log = new Logger(Sns::class);
        $this->log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * Sns 受信
     */
    public function subscribe()
    {
        try {
            $message = Message::fromRawPostData();

            $this->log->info("SNS post data => " . var_export($message, true));

            $validator = new MessageValidator();

            if ($validator->isValid($message)) {
                if ($message['Type'] == self::SUBSCRIPTION_CONFIRMATION) {
                    return [
                        "type" => $message['Type'],
                        "contents" => file_get_contents($message['SubscribeURL'])
                    ];
                } elseif ($message['Type'] === self::NOTIFICATION) {
                    return [
                        "type" => $message['Type'],
                        "subject" => $message['Subject'],
                        "message" => json_decode($message['Message'])
                    ];
                }
            }

            return [];
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());
            throw $e;
        }
    }
}
