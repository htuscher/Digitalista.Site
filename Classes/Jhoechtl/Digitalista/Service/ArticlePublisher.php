<?php
namespace Jhoechtl\Digitalista\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\Workspace;
use TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Domain\Service\NodeService;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use TYPO3\TYPO3CR\Utility;

/**
 * @Flow\Scope("singleton")
 */
class ArticlePublisher
{

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var NodeService
     */
    protected $nodeService;

    /**
     * @Flow\InjectConfiguration(path="autoMove")
     * @var array
     */
    protected $autoMoveConfiguration;

    /**
     * This is the list of articles that should be moved at the end of the request.
     *
     * @var NodeInterface[]
     */
    protected $articleQueue = [];

    /**
     * @param NodeInterface $node
     * @param Workspace $targetWorkspace
     */
    public function moveArticleOnPublish(NodeInterface $node, $targetWorkspace)
    {
        if (!$targetWorkspace->isPublicWorkspace()) {
            return;
        }

        if ($node->getNodeType()->isOfType('Jhoechtl.Digitalista:News') !== true) {
            return;
        }

        if (strpos($node->getPath(), $this->autoMoveConfiguration['pickupPath']) === false) {
            return;
        }

        $this->articleQueue[] = $node;
    }

    /**
     * Moves an article to the matching folder.
     * Much of the logic is configurable via settings, but two things are hardcoded for now:
     * - the nodeToMove should have a property named "date" which is of type \DateTime
     * - the configured "folderNodeType" should have a property title, because that is set in the process.
     *
     * @param NodeInterface $nodeToMove
     */
    public function moveArticle(NodeInterface $nodeToMove)
    {
        $defaultContext = $this->contextFactory->create();

        $node = $defaultContext->getNodeByIdentifier($nodeToMove->getIdentifier());
        if ($node === null) {
            $this->systemLogger->log(sprintf('Node "%s" given does not exist in live workspace and therefore cannot be moved to target location.',
                $nodeToMove->getPath()), LOG_WARNING);

            return;
        }

        $matchPaths = $this->autoMoveConfiguration['matchPaths'];
        if (!isset($matchPaths[$node->getParentPath()])) {
            return;
        }

        $categoryBase = $defaultContext->getNode($matchPaths[$node->getParentPath()]);

        /** @var \DateTime $articleDate */
        $articleDate = $node->getProperty('creationDate');

        $yearNode = $this->getOrCreateCategoryNodeWithTitle($categoryBase, $articleDate->format('Y'));

        // Choose the title of the month node
        setlocale(LC_ALL, 'de_DE', 'de', 'de_DE@euro');
        $monthTitle = strftime('%B', $articleDate->getTimestamp());

        $monthNode = $this->getOrCreateCategoryNodeWithTitle($yearNode, $monthTitle);
        $dayNode = $this->getOrCreateCategoryNodeWithTitle($monthNode, $articleDate->format('d'));

        $node->moveInto($dayNode);
    }

    /**
     * After the controller is done we can flush the queue of "to be moved" articles and actually move them.
     */
    public function flushQueue()
    {
        $folderNode = null;
        foreach ($this->articleQueue as $articleNode) {
            $folderNode = $articleNode->getParent();
            $this->moveArticle($articleNode);
            $this->createShortcutForNodeInFolder($articleNode, $folderNode);
        }
        $this->articleQueue = [];
        if ($folderNode) {
            $this->garbageCollectOldShortcutNodes($folderNode);
        }
    }

    /**
     * We check from a baseNode if a node of type Jhoechtl.Digitalista:Category
     * exists beyond it with the specified title. Otherwise we create it.
     *
     * @param NodeInterface $baseNode
     * @param string $nodeTitle
     * @return NodeInterface
     */
    protected function getOrCreateCategoryNodeWithTitle($baseNode, $nodeTitle)
    {
        // Search for the node representing the year
        $categoryNode = null;
        /** @var NodeInterface $childNode */
        foreach ($baseNode->getChildNodes('Jhoechtl.Digitalista:Category') as $childNode) {
            if ($childNode->getProperty('title') === $nodeTitle) {
                $categoryNode = $childNode;
            }
        }
        // It doesn't yet exist, so create it
        if (is_null($categoryNode)) {
            $categoryNode = $baseNode->createNode($this->nodeService->generateUniqueNodeName($baseNode->getPath()),
                $baseNode->getNodeType());
            $categoryNode->setProperty('title', $nodeTitle);
            $categoryNode->setProperty('uriPathSegment', Utility::renderValidNodeName($nodeTitle));

            $mainCollection = $categoryNode->getPrimaryChildNode();
            $mainCollection->createNode($this->nodeService->generateUniqueNodeName($mainCollection->getPath()),
                $this->nodeTypeManager->getNodeType('Jhoechtl.Digitalista:NewsList'));

            return $categoryNode;
        }

        return $categoryNode;
    }

    /**
     * Creates a Shortcut for the given node in folder
     *
     * @param NodeInterface $articleNode
     * @param NodeInterface $folderNode
     * @throws \TYPO3\TYPO3CR\Exception\NodeTypeNotFoundException
     */
    protected function createShortcutForNodeInFolder($articleNode, $folderNode)
    {

        // get all nodes in the live workspace
        // not needed for the $articleNode right now, but just to be safe and consistent..
        $defaultContext = $this->contextFactory->create(['invisibleContentShown' => true]);
        $folderNode = $defaultContext->getNode($folderNode->getPath());
        $articleNode = $defaultContext->getNodeByIdentifier($articleNode->getIdentifier());

        $shortcutNode = $folderNode->createNode($articleNode->getName(),
            $this->nodeTypeManager->getNodeType('TYPO3.Neos:Shortcut'));

        // the article already being moved, we can use the same URI path segment
        $shortcutNode->setProperty('uriPathSegment', $articleNode->getProperty('uriPathSegment'));
        $shortcutNode->setProperty('targetMode', 'selectedTarget');
        $shortcutNode->setProperty('target', 'node://' . $articleNode->getIdentifier());
        $shortcutNode->setProperty('title', $articleNode->getProperty('title'));

        $this->nodeDataRepository->persistEntities();
        $this->systemLogger->log(sprintf('Shortcut %s for node %s created.', $shortcutNode, $articleNode), LOG_DEBUG);

    }

    /**
     * We use the Shortcut Nodes to replace just moved article nodes. This method do the gc, to remove
     * old nodes after a configurable period of time
     *
     * @param NodeInterface $folderNode
     */
    protected function garbageCollectOldShortcutNodes($folderNode)
    {
        /** @var NodeInterface $shortcutNode */
        $offset = new \DateInterval($this->autoMoveConfiguration['maximumAgeForShortcutNodes']);
        foreach ($folderNode->getChildNodes('TYPO3.Neos:Shortcut') as $shortcutNode) {
            if ($shortcutNode->getNodeData()->getLastModificationDateTime()->add($offset) < (new \DateTime())) {
                $shortcutNode->remove();
            }
            $this->systemLogger->log(sprintf('ArticlePublisher: Shortcut %s removed (gc)', $shortcutNode), LOG_DEBUG);
        }
        $this->nodeDataRepository->persistEntities();
    }
}