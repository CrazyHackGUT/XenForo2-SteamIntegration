<?php

namespace NF\Steam\ConnectedAccount\Provider;

use NF\Steam\SteamAuth;
use OAuth\Common\Http\Uri\Uri;
use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\ConnectedAccount\Storage\StorageState;
use XF\Entity\ConnectedAccountProvider;
use XF\Http\Request;
use XF\Mvc\Controller;

class Steam extends AbstractProvider
{
    protected $auth;

    function __construct($providerId)
    {
        parent::__construct($providerId);

        $this->auth = new SteamAuth(
        	\XF::options()->nfSteamApiKey,
	        \XF::options()->boardUrl,
	        \XF::options()->boardUrl.'/connected_account.php',
	        '',
	        true
        );
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

    public function requestProviderToken(StorageState $storageState, Request $request, &$error = null, $skipStoredToken = false)
    {
	    $id = substr($request->get('openid_claimed_id'), strrpos($request->get('openid_claimed_id'), '/') + 1);

	    $storageState->storeToken($id);
	    return $id;
    }
}