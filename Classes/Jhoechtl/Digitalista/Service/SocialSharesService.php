<?php

namespace Jhoechtl\Digitalista\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * Class SocialSharesService
 *
 * @package Jhoechtl\Digitalista\Service
 * @Flow\Scope("singleton")
 */
class SocialSharesService {

	/**
	 * @param string $url
	 * @return int
	 */
	public function getTwitterSharesForUrl($url) {
		$jsonResponse = file_get_contents('https://cdn.api.twitter.com/1/urls/count.json?url=' . $url);
		$apiResponse = json_decode($jsonResponse, TRUE);
		if (isset($apiResponse['count'])) {
			return (int)$apiResponse['count'];
		}
		return 0;
	}

	/**
	 * @param string $url
	 * @return int
	 */
	public function getFacebookSharesForUrl($url) {
		$jsonResponse = file_get_contents('http://graph.facebook.com/?id=' . $url);
		$apiResponse = json_decode($jsonResponse, TRUE);
		if (isset($apiResponse['shares'])) {
			return (int)$apiResponse['shares'];
		}
		return 0;
	}

}