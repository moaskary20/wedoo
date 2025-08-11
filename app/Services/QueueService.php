<?php
namespace App\Services;

use Predis\Client as RedisClient;
use Config\Queue;

class QueueService
{
    protected $redis;

    public function __construct()
    {
        // Initialize Redis connection using Predis
        try {
            $config = new Queue();
            $this->redis = new RedisClient([
                'scheme' => 'tcp',
                'host'   => $config->redisHost,
                'port'   => $config->redisPort,
                'timeout' => $config->redisTimeout
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to connect to Redis: ' . $e->getMessage());
            throw new \RuntimeException('Could not connect to Redis server.');
        }
    }

    // Push a task onto the queue
    public function pushToQueue($queueName, $data)
    {
        try {
            return $this->redis->rpush($queueName, json_encode($data)); // Add task to the end of the list
        } catch (\Exception $e) {
            log_message('error', 'Failed to push task to Redis queue: ' . $e->getMessage());
            return false;
        }
    }

    // Pop a task from the queue
    public function popFromQueue($queueName)
    {
        try {
            $data = $this->redis->lpop($queueName); // Get task from the start of the list
            return $data ? json_decode($data, true) : null; // Decode the task data
        } catch (\Exception $e) {
            log_message('error', 'Failed to pop task from Redis queue: ' . $e->getMessage());
            return null;
        }
    }

    // Get the current length of the queue
    public function getQueueLength($queueName)
    {
        try {
            return $this->redis->llen($queueName); // Get the number of items in the list
        } catch (\Exception $e) {
            log_message('error', 'Failed to get Redis queue length: ' . $e->getMessage());
            return 0;
        }
    }
}
