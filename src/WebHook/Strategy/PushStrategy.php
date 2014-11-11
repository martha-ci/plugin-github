<?php

namespace Martha\Plugin\GitHub\WebHook\Strategy;

use Martha\Core\Domain\Repository\BuildRepositoryInterface;
use Martha\Core\Domain\Repository\ProjectRepositoryInterface;
use Martha\Plugin\GitHub\WebHook\BuildFactory;

/**
 * Class PushStrategy
 * @package Martha\Plugin\GitHub\WebHook\Strategy
 */
class PushStrategy extends AbstractStrategy
{
    /**
     * @var ProjectRepositoryInterface
     */
    protected $projectRepository;

    /**
     * @var BuildRepositoryInterface
     */
    protected $buildRepository;

    /**
     * @var BuildFactory
     */
    protected $buildFactory;

    /**
     * @param BuildRepositoryInterface $build
     * @param BuildFactory $factory
     */
    public function __construct(BuildRepositoryInterface $build, BuildFactory $factory)
    {
        $this->buildRepository = $build;
        $this->buildFactory = $factory;
    }

    /**
     * @param array $payload
     * @throws \Martha\Plugin\GitHub\WebHook\PayloadException
     */
    public function handlePayload(array $payload)
    {
        $build = $this->buildFactory->createFromPush($payload);
        $this->buildRepository->persist($build)->flush();
    }
}
