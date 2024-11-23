<?php

declare(strict_types=1);

namespace Magefine\PerformanceMessDetector\Plugin;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class DuplicateQueries
{
    public static $loads = [];

    public function __construct(
        protected UrlInterface $url,
        protected LoggerInterface $logger
    ) {}

    public function aroundFetchRow(Mysql $subject,  callable $proceed, ...$args)
    {
        $result =  $proceed(...$args);

        try {
            $this->handleCount($args);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    public function aroundFetchAll(Mysql $subject,  callable $proceed, ...$args)
    {
        $result =  $proceed(...$args);

        try {
            $this->handleCount($args, 'fetchAll');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    public function aroundFetchAssoc(Mysql $subject,  callable $proceed, ...$args)
    {
        $result =  $proceed(...$args);

        try {
            $this->handleCount($args, 'fetchAssoc');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    public function aroundFetchCol(Mysql $subject,  callable $proceed, ...$args)
    {
        $result =  $proceed(...$args);

        try {
            $this->handleCount($args, 'fetchCol');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    public function aroundFetchPairs(Mysql $subject,  callable $proceed, ...$args)
    {
        $result =  $proceed(...$args);

        try {
            $this->handleCount($args, 'fetchPairs');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    public function aroundFetchOne(Mysql $subject,  callable $proceed, ...$args)
    {
        $result =  $proceed(...$args);

        try {
            $this->handleCount($args, 'fetchOne');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    public function aroundExec(Mysql $subject,  callable $proceed, ...$args)
    {
        $result =  $proceed(...$args);

        try {
            $this->handleCount($args, 'exec');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    protected function handleCount($args, $type = 'fetchRow')
    {
        $sql = reset($args);
        $sql = strval($sql);
        $trace = (new \Exception('Duplicate SQL queries detection'))->getTrace();
        if (!isset(self::$loads[$type][base64_encode($sql)])) {
            self::$loads[$type][base64_encode($sql)] = [
                'sql' => $sql,
                'count' => 1,
            ];
        } else {
            self::$loads[$type][base64_encode($sql)]['count']++;
        }
        self::$loads[$type][base64_encode($sql)]['traces'][] = $trace;
    }

    public function __destruct()
    {
        foreach (self::$loads as $type => $logPayloads) {
            foreach ($logPayloads as $logPayload) {
                if ($logPayload['count'] >= 2) {
                    $toLog = [
                        'url' => (php_sapi_name() !== 'cli') ? $this->url->getCurrentUrl() : 'cli://',
                        'query' => $logPayload['sql'],
                        'count' => $logPayload['count'],
                        'traces' => $logPayload['traces']
                    ];
                    $this->logger->debug('[PMD] MULTIPLE IDENTICAL SQL QUERIES DETECTED : ' . json_encode($toLog));
                }
            }
        }
    }
}
