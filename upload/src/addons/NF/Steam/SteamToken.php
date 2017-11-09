<?php
namespace NF\Steam;

use OAuth\Common\Token\TokenInterface;
class SteamToken implements TokenInterface
{
    private $accessToken;

    public function __construct($accessToken = null)
    {
        $this->accessToken = $accessToken;
    }
    public function getAccessToken()
    {
        return $this->getRefreshToken();
    }

    /**
     * @return int
     */
    public function getEndOfLife()
    {
        return null;
    }

    /**
     * @return array
     */
    public function getExtraParams()
    {
        return null;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        return null;
    }

    /**
     * @param int $endOfLife
     */
    public function setEndOfLife($endOfLife)
    {
        return null;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime($lifetime)
    {
        return null;
    }

    /**
     * @param array $extraParams
     */
    public function setExtraParams(array $extraParams)
    {
        return null;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return null;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        return null;
    }
}