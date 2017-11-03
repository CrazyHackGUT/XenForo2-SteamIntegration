<?php
/**
 * Created by PhpStorm.
 * User: Zach
 * Date: 11/3/2017
 * Time: 10:17 AM
 */

namespace NF\Steam\ConnectedAccount\Provider;


use NF\Steam\SteamAuth;
use OAuth\Common\Http\Uri\Uri;
use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\Entity\ConnectedAccountProvider;
use XF\Mvc\Controller;

class Steam extends AbstractProvider
{
    protected $auth;
    function __construct($providerId)
    {
        parent::__construct($providerId);
        $this->auth = new SteamAuth(\XF::options()->nfSteamApiKey, \XF::options()->boardUrl, \XF::options()->boardUrl.'/connected_account.php', '', true);
    }

    public function getTitle()
    {
        return \XF::phrase('nf_steam');
    }

    public function getDescription()
    {
        return \XF::phrase('nf_steam_description');
    }

    public function getOAuthServiceName()
    {
        return 'Steam';
    }

    public function getDefaultOptions()
    {
        return [
            'steam_api_key' => '',
        ];
    }

    public function getOAuthConfig(ConnectedAccountProvider $provider, $redirectUri = null)
    {
        return [
            'steam_api_key' => $provider->options['steam_api_key'],
        ];
    }

    public function handleAuthorization(Controller $controller, ConnectedAccountProvider $provider, $returnUrl)
    {
        $session = \XF::app()['session.public'];

        $session->set('connectedAccountRequest', [
            'provider' => $this->providerId,
            'returnUrl' => $returnUrl,
            'test' => $this->testMode
        ]);
        $session->save();

        return $controller->redirect($this->getAuthorizationUri());
    }

    public function getAuthorizationUri(array $additionalParameters = array())
    {
        return $this->auth->loginUrl();
    }

    public function getAuthorizationEndpoint()
    {
        return new Uri('https://steamcommunity.com/openid/login');
    }

}