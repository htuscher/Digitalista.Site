<?php
namespace Jhoechtl\Digitalista\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Jhoechtl.Digitalista".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class PageViewRepository extends Repository {

	/**
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $entityManager;

	/**
	 * @param int $days
	 * @param int $maxResults
	 * @param string $parentPath
	 * @param bool|string $dimensionsHash
	 * @return mixed
	 */
	public function mostViewedByDays($days, $maxResults = 5, $parentPath = '/', $dimensionsHash = false) {
		$startDate = new \DateTime();
		$startDate->sub(new \DateInterval('P' . $days . 'D'));
		$queryParameters = [
			'dayStart' => $startDate,
			'nodeType' => 'Jhoechtl.Digitalista:News',
			'parentPath' => $parentPath . '%'
		];
		$dql = 'SELECT ' .
			'nd as node, COUNT(pv.date) as viewCount ' .
			'FROM Jhoechtl\Digitalista\Domain\Model\PageView pv JOIN TYPO3\TYPO3CR\Domain\Model\NodeData nd ';
		$dql .= 'WHERE nd.nodeType = :nodeType AND nd.path LIKE :parentPath AND pv.date >= :dayStart AND pv.nodeData = nd ';
		if ($dimensionsHash) {
			$dql .= 'AND nd.dimensionsHash = :dimensionsHash ';
			$queryParameters['dimensionsHash'] = $dimensionsHash;
		}
		$dql .= 'GROUP BY pv.nodeData ORDER BY viewCount DESC';
		/** @var \Doctrine\ORM\Query $query */
		$query = $this->entityManager->createQuery($dql);
		$query->setMaxResults($maxResults);
		$query->setQueryCacheLifetime(0);
		$query->setParameters($queryParameters);

		return $query->execute();
	}

}