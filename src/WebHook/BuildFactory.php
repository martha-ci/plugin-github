<?php

namespace Martha\Plugin\GitHub\WebHook;

use Martha\Core\Domain\Entity\Build;
use Martha\Core\Domain\Repository\ProjectRepositoryInterface;

/**
 * Class Factory
 * @package Martha\Plugin\GitHub\WebHook
 */
class BuildFactory
{
    /**
     * @var ProjectRepositoryInterface
     */
    protected $projectRepository;

    /**
     * @param ProjectRepositoryInterface $projectRepository
     */
    public function __construct(ProjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * @param array $payload
     * @return Build
     * @throws PayloadException
     */
    public function createBuildFromPullRequest(array $payload)
    {
        $build = $this->createBuildFromPayload($payload)
            ->setRevisionNumber($payload['pull_request']['head']['sha'])
            ->setBranch(basename($payload['pull_request']['head']['ref']));
        $build->getMetadata()
            ->set('triggered-by', 'GitHubHook:PullRequest')
            ->set('pull-request', $payload['number']);

        return $build;
    }

    /**
     * @param array $payload
     * @return Build
     * @throws PayloadException
     */
    public function createFromPush(array $payload)
    {
        $build = $this->createBuildFromPayload($payload)
            ->setRevisionNumber($payload['head_commit']['id'])
            ->setBranch(basename($payload['ref']));
        $build->getMetadata()->set('triggered-by', 'GitHubHook:Push');

        return $build;
    }

    /**
     * @param array $payload
     * @return Build
     * @throws PayloadException
     */
    protected function createBuildFromPayload(array $payload)
    {
        $repositoryData = isset($payload['repository']) ? $payload['repository'] : false;
        if (!$repositoryData) {
            throw new PayloadException('Repository data is missing from the payload');
        }

        $projectName = isset($repositoryData['full_name']) ? $repositoryData['full_name'] : '';
        $project = $this->projectRepository->getBy(['name' => $projectName]);
        if (!count($project)) {
            throw new PayloadException('Project not found: ' . $projectName);
        }

        $build = new Build();
        $build->setProject($project[0]);
        $build->setMethod('GitHub:WebHook');
        $build->setStatus(Build::STATUS_PENDING);
        $build->setCreated(new \DateTime());

        return $build;
    }
}
