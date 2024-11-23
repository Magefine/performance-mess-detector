<?php

declare(strict_types=1);

namespace Magefine\PerformanceMessDetector\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class DetectSlowController
{
    public function __construct(
        protected LoggerInterface $logger,
        protected UrlInterface $url,
        protected int $slowControllerThreshold = 500
    ) {}

    /**
     * Plugin around dispatch method to measure execution time
     *
     * @param Action $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundDispatch(Action $subject, callable $proceed, ...$args)
    {
        $startTime = microtime(true);

        // Exécution du contrôleur
        $result = $proceed(...$args);

        $executionTime = (microtime(true) - $startTime) * 1000;

        // Si le contrôleur dépasse le seuil
        if ($executionTime > $this->slowControllerThreshold) {
            $controllerClass = get_class($subject);
            $actionName = $subject->getRequest()->getFullActionName();
            $url = $this->url->getCurrentUrl();

            $this->logger->debug(sprintf(
                '[PMD] SLOW ACTION DETECTED : %s (%s) %s | EXECUTION TIME : %d ms',
                $actionName,
                $controllerClass,
                $url,
                $executionTime
            ));
        }

        return $result;
    }
}
