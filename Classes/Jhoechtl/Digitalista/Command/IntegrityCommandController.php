<?php
namespace Jhoechtl\Digitalista\Command;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Neos\Domain\Repository\SiteRepository;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use TYPO3\TYPO3CR\Utility;

/**
 * @Flow\Scope("singleton")
 */
class IntegrityCommandController extends CommandController
{
    /**
     * @var array
     */
    protected $settings;
    /**
     * @var string
     */
    protected $siteName;
    /**
     * @var ContextFactoryInterface
     * @Flow\Inject
     */
    protected $contextFactory;
    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;
    /**
     * @var SiteRepository
     * @Flow\Inject
     */
    protected $siteRepository;

    /**
     * Inject the settings
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
        $this->siteName = $settings['siteName'];
    }

    /**
     * Check/Create nodes necessary for auto-publishing feature
     *
     * @throws \TYPO3\TYPO3CR\Exception\NodeTypeNotFoundException
     */
    public function checkCreateDraftContainerCommand()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $site = $this->siteRepository->findOneByName($this->siteName);
        $rootNode = $this->contextFactory->create(['workspaceName' => 'live', 'currentSite' => $site])->getRootNode();
        $draftNode = $this->createDraftNode($rootNode, 'Entwürfe', 'Jhoechtl.Digitalista:Folder', true);
        $this->createDraftNode($draftNode, 'Magazin');
        $this->createDraftNode($draftNode, 'Interviews');
        $this->createDraftNode($draftNode, 'Sounds');
    }

    /**
     * @param NodeInterface $baseNode
     * @param string $title
     * @param string $nodeType
     * @param bool $baseNodeIsSiteRoot
     * @return \TYPO3\TYPO3CR\Domain\Model\Node|NodeInterface
     * @throws \TYPO3\TYPO3CR\Exception\NodeException
     * @throws \TYPO3\TYPO3CR\Exception\NodeTypeNotFoundException
     */
    protected function createDraftNode(
        $baseNode,
        $title,
        $nodeType = 'Jhoechtl.Digitalista:Category',
        $baseNodeIsSiteRoot = false
    ) {
        $nodeName = Utility::renderValidNodeName($title);
        $draftNode = $baseNode->getNode($nodeName);
        if (is_null($draftNode)) {
            $draftNode = $baseNode->createNode($nodeName, $this->nodeTypeManager->getNodeType($nodeType));
            if ($baseNodeIsSiteRoot) {
                $draftNode->setPath('/sites/' . $this->siteName . '/' . $nodeName);
            }
            $draftNode->setHidden(true);
            $draftNode->setHiddenInIndex(true);
            $draftNode->setProperty('title', $title);
            $draftNode->setProperty('uriPathSegment', $nodeName);
        }

        return $draftNode;
    }

}