<?php

declare(strict_types=1);

namespace Magefine\PerformanceMessDetector\Plugin;

use Psr\Log\LoggerInterface;

class LogSlowQueries
{
    /**
     * @param LoggerInterface $logger
     * @param int $slowQueryThreshold
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected int $slowQueryThreshold = 500
    ) {
        if (php_sapi_name() !== 'cli') {
            $this->slowQueryThreshold = 100;
        }
    }

    /**
     * Around plugin for SQL query execution
     *
     * @param \Magento\Framework\DB\Adapter\Pdo\Mysql $subject
     * @param callable $proceed
     * @param string $sql
     * @param array $bind
     * @return mixed
     */
    public function aroundQuery(
        \Magento\Framework\DB\Adapter\Pdo\Mysql $subject,
        callable $proceed,
        $sql,
        $bind = []
    ) {
        $startTime = microtime(true);

        $result = $proceed($sql, $bind);

        $executionTime = (microtime(true) - $startTime) * 1000;

        // Vérifie si la requête est lente
        if ($executionTime > $this->slowQueryThreshold) {
            $trace = (new \Exception('Slow SQL query detection'))->getTrace();
            $this->logger->debug(
                sprintf(
                    '[PMD] SLOW SQL QUERY DETECTED : %s | EXECUTION TIME : %d ms',
                    $sql,
                    $executionTime
                ) . ' || ' . json_encode($trace)
            );
        }

        return $result;
    }
}
