<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Queue\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use Yiisoft\Yii\Queue\AMQP\Settings\ExchangeSettingsInterface;
use Yiisoft\Yii\Queue\AMQP\Settings\Queue;
use Yiisoft\Yii\Queue\AMQP\Settings\QueueSettingsInterface;

final class QueueProvider implements QueueProviderInterface
{
    private AbstractConnection $connection;
    private QueueSettingsInterface $queueSettings;
    private ExchangeSettingsInterface $exchangeSettings;
    private ?AMQPChannel $channel = null;

    public function __construct(
        AbstractConnection $connection,
        Queue $queueSettings,
        ExchangeSettingsInterface $exchangeSettings
    ) {
        $this->connection = $connection;
        $this->queueSettings = $queueSettings;
        $this->exchangeSettings = $exchangeSettings;
    }

    public function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->connection->channel();
            $this->channel->queue_declare(...$this->queueSettings->getPositionalSettings());
            $this->channel->exchange_declare(...$this->exchangeSettings->getPositionalSettings());
            $this->channel->queue_bind($this->queueSettings->getName(), $this->exchangeSettings->getName());
        }

        return $this->channel;
    }

    public function getQueueSettings(): QueueSettingsInterface
    {
        return $this->queueSettings;
    }

    public function getExchangeSettings(): ExchangeSettingsInterface
    {
        return $this->exchangeSettings;
    }
}
