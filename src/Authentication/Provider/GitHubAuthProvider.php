<?php

namespace Martha\Plugin\GitHub\Authentication\Provider;

use Github\Client;
use League\OAuth2\Client\Provider\Github;
use Martha\Core\Authentication\AuthenticationResult;
use Martha\Core\Authentication\Provider\AbstractOAuthProvider;
use Martha\Core\Plugin\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GitHubAuthProvider
 * @package Martha\Plugin\GitHub
 */
class GitHubAuthProvider extends AbstractOAuthProvider
{
    /**
     * @var string
     */
    protected $name = 'GitHub';

    /**
     * @var string
     */
    protected $icon = '/images/github-icon.png';

    /**
     * @var GitHub
     */
    protected $provider;

    /**
     * @param AbstractPlugin $plugin
     * @param array $config
     */
    public function __construct(AbstractPlugin $plugin, array $config)
    {
        parent::__construct($plugin, $config);

        $this->provider = new Github([
            'clientId' => $this->config['client_id'],
            'clientSecret' => $this->config['client_secret'],
            'redirectUri' => $config['callback-url'],
            'scopes' => ['user', 'user:email', 'repo', 'write:repo_hook', 'write:public_key'],
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->provider->getAuthorizationUrl();
    }

    /**
     *
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param Request $request
     * @return bool|AuthenticationResult
     */
    public function validateResult(Request $request)
    {
        if (!$request->get('code')) {
            return false;
        }

        try {
            $token = $this->provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $request->get('code')
                ]
            );
        } catch (\Exception $e) {
            return false;
        }

        $token = $token->accessToken;

        $client = new Client();
        $client->authenticate($token, null, Client::AUTH_HTTP_TOKEN);

        $userInfo = $client->me()->show();
        $emails = $client->me()->emails()->all();

        $result = new AuthenticationResult();
        $result->setName($userInfo['name']);
        $result->setAlias($userInfo['login']);
        $result->setService('GitHub');
        $result->setCredentials(['access-token' => $token]);

        foreach ($emails as $email) {
            $result->addEmail($email);
        }

        return $result;
    }
}
