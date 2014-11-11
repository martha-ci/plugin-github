<?php

namespace Martha\Plugin\GitHub\WebHook\Strategy;

/**
 * Abstract Class AbstractStrategy
 * @package Martha\Plugin\GitHub\WebHook\Strategy
 */
abstract class AbstractStrategy
{
    /**
     * @param array $payload
     */
    abstract public function handlePayload(array $payload);
}
