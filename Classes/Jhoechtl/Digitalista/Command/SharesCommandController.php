<?php

namespace Jhoechtl\Digitalista\Command;

use Jhoechtl\Digitalista\Service\SocialSharesService;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\Arguments;
use TYPO3\Flow\Mvc\Controller\ControllerContext;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\Neos\Domain\Repository\SiteRepository;
use TYPO3\Neos\Service\LinkingService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;

/**
 * Class SharesCommandController
 *
 * @package Jhoechtl\Digitalista\Command
 * @Flow\Scope("singleton")
 */
class SharesCommandController extends CommandController {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var string
	 */
	protected $baseDomain;

	/**
	 * @var string
	 */
	protected $siteName;

	/**
	 * @var SocialSharesService
	 * @Flow\Inject
	 */
	protected $socialSharesService;

	/**
	 * @var ContextFactoryInterface
	 * @Flow\Inject
	 */
	protected $contextFactory;

	/**
	 * @var LinkingService
	 * @Flow\Inject
	 */
	protected $linkingService;

	/**
	 * @var SiteRepository
	 * @Flow\Inject
	 */
	protected $siteRepository;

	/**
	 * @var UriBuilder
	 */
	protected $uriBuilder;

	/**
	 * Inject the settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
		$this->baseDomain = $settings['baseDomain'];
		$this->siteName = $settings['siteName'];
	}

	public function readSharesCommand() {
		$site = $this->siteRepository->findOneByName($this->siteName);
		$rootNode = $this->contextFactory->create(['workspaceName' => 'live', 'currentSite' => $site])->getRootNode();
		$rootNode->getChildNodes('Jhoechtl.Digitalista:News');
		$flowQuery = new FlowQuery([$rootNode]);
		$newsNodes = $flowQuery->find('[instanceof Jhoechtl.Digitalista:News]')->get();
		/** @var NodeInterface $newsNode */
		foreach($newsNodes as $newsNode) {
			$nodePublicUri = $this->getUrlToNode($newsNode);
			$facebookShares = $this->socialSharesService->getFacebookSharesForUrl($nodePublicUri);
			$twitterShares = $this->socialSharesService->getTwitterSharesForUrl($nodePublicUri);
			$newsNode->setProperty('facebookShares', $facebookShares);
			$newsNode->setProperty('twitterShares', $twitterShares);
		}
	}

	/**
	 * The injection of the faked UriBuilder is necessary to generate frontend URLs from the backend
	 *
	 * @param ConfigurationManager $configurationManager
	 */
	public function injectUriBuilder(ConfigurationManager $configurationManager) {
		$_SERVER['FLOW_REWRITEURLS'] = 1;
		$httpRequest = Request::createFromEnvironment();
		$httpRequest->setBaseUri(new Uri($this->baseDomain));
		$request = new ActionRequest($httpRequest);
		$uriBuilder = new UriBuilder();
		$uriBuilder->setRequest($request);
		$uriBuilder->setCreateAbsoluteUri(TRUE);
		$this->uriBuilder = $uriBuilder;
	}

	/**
	 * Create the frontend URL to the node
	 *
	 * @param NodeInterface $node
	 * @return string The URL of the node
	 * @throws \TYPO3\Neos\Exception
	 */
	protected function getUrlToNode(NodeInterface $node) {
		$uri = $this->linkingService->createNodeUri(
			new ControllerContext(
				$this->uriBuilder->getRequest(),
				new Response(),
				new Arguments(array()),
				$this->uriBuilder
			),
			$node,
			$node->getContext()->getRootNode(),
			'html',
			TRUE
		);
		return $uri;
	}
}