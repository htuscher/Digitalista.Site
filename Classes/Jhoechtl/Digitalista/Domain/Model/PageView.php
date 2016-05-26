<?php
namespace Jhoechtl\Digitalista\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Jhoechtl.Digitalista".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class PageView {

	/**
	 * @ORM\ManyToOne
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @var \TYPO3\TYPO3CR\Domain\Model\NodeData
	 */
	protected $nodeData;

	/**
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @return \TYPO3\TYPO3CR\Domain\Model\NodeData
	 */
	public function getNodeData() {
		return $this->nodeData;
	}

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeData $nodeData
	 * @return void
	 */
	public function setNodeData($nodeData) {
		$this->nodeData = $nodeData;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 * @return void
	 */
	public function setDate($date) {
		$this->date = $date;
	}


}