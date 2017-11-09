<?php
namespace NF\Steam\ConnectedAccount\ProviderData;

use NF\Steam\Api;
use \XF\ConnectedAccount\ProviderData\AbstractProviderData;
use XF\ConnectedAccount\Storage\StorageState;

class Steam extends AbstractProviderData
{
    private $api;
    public function __construct($providerId, StorageState $storageState)
    {
        parent::__construct($providerId, $storageState);
        $this->api = new Api(\XF::app());
    }

    public function getDefaultEndpoint()
    {
        return Api::BASE_URL.'ISteamUser/GetPlayerSummaries/v0002/?key='. \XF::options()->nfSteamApiKey.'&steamids='.$this->providerId;
    }

    public function getProviderKey()
    {
        return $this->requestFromEndpoint('steamid');
    }

    public function getUrl()
    {
        return $this->requestFromEndpoint('profileurl');
    }

    public function requestFromEndpoint($key = null, $method = 'GET', $endpoint = null)
    {
        $endpoint = $endpoint ?: $this->getDefaultEndpoint();

        if ($value = $this->requestFromCache($endpoint, $key))
        {
            return $value;
        }

        $storageState = $this->storageState;
        $data = $storageState->retrieveProviderData();


        if ($data && $endpoint == $this->getDefaultEndpoint())
        {
            if ($key === null)
            {
                $value = $data;
            }
            else
            {
                $value = isset($data[$key]) ? $data[$key] : null;
            }
            $this->storeInCache($endpoint, $value, $key);
            return $value;
        }
        $provider = $storageState->getProvider();
        /** @var \NF\Steam\ConnectedAccount\Provider\Steam $handler */
        $handler = $provider->handler;
        try
        {
            $data = $this->api->getPlayerSummaries($handler->getAuth()->steamid);
            $data = json_decode($data, true);
            $data = $data['response']['players'][0];
            $this->storeInCache($endpoint, $data);
            if ($endpoint == $this->getDefaultEndpoint())
            {
                $storageState->storeProviderData($data);
            }
            return $this->requestFromCache($endpoint, $key);
        }
        catch(\Exception $e)
        {
            return null;
        }
    }
}