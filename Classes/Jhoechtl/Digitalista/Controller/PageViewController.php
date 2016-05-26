<?php
namespace Jhoechtl\Digitalista\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Jhoechtl.Digitalista".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Neos\Service\LinkingService;

class PageViewController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var \Jhoechtl\Digitalista\Domain\Repository\PageViewRepository
	 */
	protected $pageViewRepository;

	/**
	 * @Flow\Inject
	 * @var LinkingService
	 */
	protected $linkingService;

	/**
	 * @return void
	 */
	public function viewsAction() {
		$days = $this->request->getInternalArgument('__days');
		$numberOfItems = $this->request->getInternalArgument('__numberOfItems');
		/** @var \TYPO3\TYPO3CR\Domain\Model\Node $documentNode */
		$documentNode = $this->request->getInternalArgument('__documentNode');
		$parentPath = '/' . implode('/', array_slice(Arrays::trimExplode('/', $documentNode->getPath()), 0, 3)) . '/';
		$currentDimensions = $documentNode->getDimensions();
		$dimensionsHash = \TYPO3\TYPO3CR\Utility::sortDimensionValueArrayAndReturnDimensionsHash($currentDimensions);
		$news = (array)$this->pageViewRepository->mostViewedByDays($days, $numberOfItems, $parentPath, $dimensionsHash);
		$news = array_map(function($newsEntry) use ($documentNode) {
			/** @var \TYPO3\TYPO3CR\Domain\Model\NodeData $node */
			$node = $newsEntry['node'];
			$newsEntry['uri'] = $this->linkingService->createNodeUri(
				$this->getControllerContext(),
				$node->getPath(),
				$documentNode
			);
			return $newsEntry;
		}, $news);

		$this->view->assign('days', $days);
		$this->view->assign('numberOfItems', $numberOfItems);
		$this->view->assign('newsList', $news);
	}

	/**
	 * @return void
	 */
	public function sharesAction() {
		$this->view->assign('foos', array(
			'bar', 'baz'
		));
	}
}