<?php
namespace Jhoechtl\Digitalista;

use Jhoechtl\Digitalista\Service\ArticlePublisher;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\Workspace;

class Package extends BasePackage
{

    /**
     * @param Bootstrap $bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(Workspace::class, 'beforeNodePublishing', ArticlePublisher::class, 'moveArticleOnPublish',
            true);

        // After the controller is done we should have a consistent state and can move any queued articles around.
        $dispatcher->connect('TYPO3\Flow\Mvc\Dispatcher', 'afterControllerInvocation',
            function ($request) use ($bootstrap) {
                $articlePublisher = $bootstrap->getObjectManager()->get(ArticlePublisher::class);
                $articlePublisher->flushQueue();
            });
    }


}