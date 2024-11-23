<?php

declare(strict_types=1);

namespace Magefine\PerformanceMessDetector\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class MultipleProductLoads
{
    public static $loads = [];

    public function __construct(
        protected UrlInterface $url,
        protected LoggerInterface $logger
    ) {}

    public function aroundLoad(Product $subject,  callable $proceed, ...$args)
    {
        $result = $proceed(...$args);

        try {
            $productId = reset($args);
            $storeId = $subject->getStoreId() ?: '0';
            $trace = (new \Exception('Multiple product loads detection'))->getTrace();
            self::$loads[$productId][$storeId][] = [
                'trace' => $trace
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    protected function countMultipleLoads($productId): int
    {
        $count = 0;
        foreach (self::$loads[$productId] as $load) {
            $count += count($load);
        }
        return $count;
    }

    public function __destruct()
    {
        foreach (self::$loads as $productId => $loads) {
            if ($this->countMultipleLoads($productId) >= 2) {
                $toLog = [
                    'url' => (php_sapi_name() !== 'cli') ? $this->url->getCurrentUrl() : 'cli://',
                    'traces' => $loads
                ];
                $this->logger->debug('[PMD] MULTIPLE PRODUCT LOAD DETECTED : ' . json_encode($toLog));
            }
        }
    }
}
