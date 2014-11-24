<?php

namespace Martha\Plugin\GitHub;

use Github\Client;
use Martha\Core\Domain\Entity\Project;
use Martha\Core\Domain\Entity\User;
use Martha\Core\Plugin\RemoteProjectProvider\AbstractRemoteProjectProvider;

/**
 * Class RemoteProjectProvider
 * @package Martha\Plugin\GitHub
 */
class RemoteProjectProvider extends AbstractRemoteProjectProvider
{
    /**
     * The name of this Remote Project Provider.
     *
     * @var string
     */
    protected $providerName = 'GitHub';

    /**
     * @var \GitHub\Client
     */
    protected $apiClient;

    /**
     * Get all available projects for the authenticated account, including organizations.
     *
     * @param User $user
     * @return array
     */
    public function getAvailableProjectsForUser(User $user)
    {
        $api = $this->getApi($user);
        // Get all repositories:
        $repositories = $api->me()->repositories();

        // Get all organizations:
        $orgs = $api->me()->organizations();

        foreach ($orgs as $org) {
            // Merge the organization repositories with the user repositories:
            $orgRepos = $api->organizations()->repositories($org['login']);
            $repositories = array_merge($repositories, $orgRepos);
        }

        $projects = [];

        foreach ($repositories as $repository) {
            $projects[$repository['full_name']] = $repository['full_name'];
        }

        ksort($projects);

        return $projects;
    }

    /**
     * Get information about a GitHub project. $identifier must be in the format of "owner/repo"
     *
     * @param User $user
     * @param string $identifier
     * @return array
     */
    public function getProjectInformation(User $user, $identifier)
    {
        list($owner, $repo) = explode('/', $identifier);
        $project = $this->getApi($user)->repository()->show($owner, $repo);

        return [
            'name' => $identifier,
            'description' => $project['description'],
            'scm' => 'git',
            'uri' => $project['private'] ? $project['ssh_url'] : $project['clone_url'],
            'private' => $project['private']
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function onProjectCreated(Project $project, $projectType)
    {
        // Only handle projects created by this provider
        if (strtolower($projectType) !== strtolower($this->plugin->getName())) {
            return;
        }

        list($owner, $repo) = explode('/', $project->getName());

        $this->getApi($project->getCreatedBy())->repositories()->hooks()->create(
            $owner,
            $repo,
            [
                'name' => 'web',
                'active' => true,
                'events' => [
                    'push',
                    'pull_request'
                ],
                'config' => [
                    'url' => $this->plugin->getPluginManager()->getSystem()->getSiteUrl() . '/build/github-web-hook',
                    'content_type' => 'json'
                ]
            ]
        );
    }

    /**
     * Gets an instance of a configured GitHub API client and returns it.
     *
     * @param User $user
     * @return Client|false
     */
    protected function getApi(User $user)
    {
        if ($this->apiClient) {
            return $this->apiClient;
        }

        $token = $user->getTokenForService('GitHub');

        if (!$token) {
            return false;
        }

        $token = $token->get('access-token');

        $this->apiClient = new Client();
        $this->apiClient->authenticate($token, null, Client::AUTH_HTTP_TOKEN);

        return $this->apiClient;
    }
}
