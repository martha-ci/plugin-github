<?php

namespace Martha\Plugin\GitHub\WebHook\Strategy;

use Martha\Core\Plugin\PluginManager;
use Martha\Plugin\GitHub\WebHook\BuildFactory;

/**
 * Class HookStrategyFactory
 * @package Martha\Plugin\GitHub\WebHook\Strategy
 */
class HookStrategyFactory
{
    /**
     * @var PluginManager
     */
    protected $pluginManager;

    /**
     * @param PluginManager $plugin
     */
    public function __construct(PluginManager $plugin)
    {
        $this->pluginManager = $plugin;
    }

    /**
     * @param string $event
     * @return AbstractStrategy|bool
     */
    public function createStrategyForEvent($event)
    {
        if ($event == 'pull_request') {
            return new PullRequestStrategy(
                $this->pluginManager->getSystem()->getRepositoryFactory()->createBuildRepository(),
                new BuildFactory(
                    $this->pluginManager->getSystem()->getRepositoryFactory()->createProjectRepository()
                )
            );
        } elseif ($event == 'push') {
            return new PushStrategy(
                $this->pluginManager->getSystem()->getRepositoryFactory()->createBuildRepository(),
                new BuildFactory(
                    $this->pluginManager->getSystem()->getRepositoryFactory()->createProjectRepository()
                )
            );
        }

        return false;
    }
}
