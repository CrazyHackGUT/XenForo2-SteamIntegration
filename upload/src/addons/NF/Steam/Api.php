<?php

namespace NF\Steam;

use XF\App;
use GuzzleHttp\Exception\RequestException;

class Api
{
	const BASE_URL = 'http://api.steampowered.com/';

	protected $app;

	/** @var  \GuzzleHttp\Client */
	protected $client;
	protected $key;

	public function __construct(App $app)
	{
		$this->app = $app;
		$this->key = $app->options()->nfSteamApiKey;
	}

	/**
	 * @return \GuzzleHttp\Client
	 */
	protected function getHttpClient()
	{
		if (!$this->client)
		{
			$this->client = $this->app->http()->client();
		}

		return $this->client;
	}

	/**
	 * @param        $url
	 * @param string $method
	 *
	 * @return \GuzzleHttp\Message\Request|\GuzzleHttp\Message\RequestInterface
	 */
	protected function getHttpRequest($url, $method = 'GET')
	{
		$client = $this->getHttpClient();
		$request = $client->createRequest($method, self::BASE_URL . $url, [
			'headers' => [
				'Content-Type' => 'application/json',
			],
		]);

		return $request;
	}

	protected function getJsonResponse($url, $method = 'GET')
	{
		$data = [];
		try
		{
			$client = $this->getHttpClient();
			$request = $this->getHttpRequest($url, $method);

			$data = $client->send($request);
			$data = trim($data->getBody()->getContents());
		}
		catch (RequestException $e)
		{
			$this->app()->logException($e, false, 'SteamApi HTTP error: ');
		}

		return $data;
	}

	/**
	 * Returns the latest of a game specified by its appID
	 *
	 * @param     $appId
	 * @param int $count
	 * @param int $maxLength
	 *
	 * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
	 */
	public function getNewsForApp($appId, $count = 3, $maxLength = 300)
	{
		return $this->getJsonResponse("ISteamNews/GetNewsForApp/v0002/?appid=$appId&count=$count&maxlength=$maxLength");
	}

	/**
	 * Returns on global achievements overview of a specific game in percentages
	 *
	 * @param $gameId int app id from Steam
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getGlobalAchievementPercentagesForApp($gameId)
	{
		return $this->getJsonResponse("ISteamUserStats/GetGlobalAchievementPercentagesForApp/v0002/?gameid=$gameId");
	}

	/**
	 * Returns basic profile information for a list of 64-bit Steam IDs
	 *
	 * @param $steamIds string|array Single or multiple steamIds
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getPlayerSummaries($steamIds)
	{
		if (is_array($steamIds))
		{
			$steamIds = implode(',', $steamIds);
		}

		return $this->getJsonResponse("ISteamUser/GetPlayerSummaries/v0002/?key=$this->key&steamids=$steamIds");
	}

	/**
	 * Returns the friend list of any Steam user, provided their Steam Community profile visibility is set to "Public"
	 *
	 * @param        $steamId
	 * @param string $relationship
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getFriendList($steamId, $relationship = 'all')
	{
		if (!in_array($relationship, [
			'friend',
			'all',
		]))
		{
			$relationship = 'all';
		}

		return $this->getJsonResponse("ISteamUser/GetFriendList/v0001/?key=$this->key&steamid=$steamId&relationship=$relationship");
	}

	/**
	 * Returns a list of achievements for this user by app id
	 *
	 * @param $steamId
	 * @param $appId
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getPlayerAchievements($steamId, $appId)
	{
		return $this->getJsonResponse("ISteamUserStats/GetPlayerAchievements/v0001/?key=$this->key&appid=$appId&steamid=$steamId");
	}

	/**
	 * Returns user player times for all games they have played
	 *
	 * @param $steamId
	 * @param $appId
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getUserStatsForGames($steamId)
	{
		return $this->getJsonResponse("IPlayerService/GetOwnedGames/v0001/?key=$this->key&steamid=$steamId");
	}

	/**
	 * Returns a list of games a player owns along with some playtime information, if the profile is publicly visible.
	 * Private, friends-only, and other privacy settings are not supported unless you are asking for your own personal details
	 *
	 * @param $steamId
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getOwnedGames($steamId)
	{
		return $this->getJsonResponse("IPlayerService/GetOwnedGames/v0001/?key=$this->key&steamid=$steamId");
	}

	/**
	 * @see Steam::getOwnedGames()
	 *
	 * @param     $steamId
	 * @param int $limit
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getRecentlyPlayedGames($steamId, $limit = 10)
	{
		$url = "IPlayerService/GetRecentlyPlayedGames/v0001/?key=$this->key&steamid=$steamId";

		if (is_int($limit) && $limit > 0)
		{
			$url .= "&count=$limit";
		}

		return $this->getJsonResponse($url);
	}

	/**
	 * Returns the original owner's SteamID if a borrowing account is currently playing this game.
	 * If the game is not borrowed or the borrower currently doesn't play this game, the result is always 0
	 *
	 * @param $steamId
	 * @param $appId
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function isPlayingSharedGame($steamId, $appId)
	{
		return $this->getJsonResponse("IPlayerService/IsPlayingSharedGame/v0001/?key=$this->key&steamid=$steamId&appid_playing=$appId");
	}

	/**
	 * Returns game name, game version and available game stats(achievements and stats)
	 *
	 * @param $appId
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getSchemaForGame($appId)
	{
		return $this->getJsonResponse("ISteamUserStats/GetSchemaForGame/v2/?key=$this->key&appid=$appId");
	}

	/**
	 * Returns Community, VAC, and Economy ban statuses for given players
	 *
	 * @param $steamIds string|array
	 *
	 * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null|string
	 */
	public function getGameBans($steamIds)
	{
		if (is_array($steamIds))
		{
			$steamIds = implode(',', $steamIds);
		}

		return $this->getJsonResponse("ISteamUser/GetPlayerBans/v1/?key=$this->key&steamids=$steamIds");
	}

	/**
	 * @return App
	 */
	protected function app()
	{
		return $this->app;
	}
}