<?php
 /***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Hans Höchtl <jhoechtl@gmail.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace Jhoechtl\Digitalista\TypoScript\Eel\FlowQueryOperations;


use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Eel\FlowQuery\FlowQueryException;
use TYPO3\Eel\FlowQuery\Operations\AbstractOperation;
use TYPO3\TYPO3CR\Domain\Model\Node;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

class SortOperation extends AbstractOperation {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	static protected $shortName = 'sort';
	/**
	 * {@inheritdoc}
	 *
	 * @var integer
	 */
	static protected $priority = 100;

	/**
	 * {@inheritdoc}
	 *
	 * We can only handle TYPO3CR Nodes.
	 *
	 * @param mixed $context
	 * @return boolean
	 */
	public function canEvaluate($context) {
		return (isset($context[0]) && ($context[0] instanceof NodeInterface));
	}

	public function evaluate(FlowQuery $flowQuery, array $arguments) {
		if (!isset($arguments[0]) || empty($arguments[0])) {
			throw new FlowQueryException('sort() needs property name by which nodes should be sorted', 1332492263);
		} else {
			$nodes = $flowQuery->getContext();
			$sortByPropertyPath = $arguments[0];
			$sortOrder = 'DESC';
			if (isset($arguments[1]) && !empty($arguments[1]) && in_array($arguments[1], array('ASC', 'DESC'))) {
				$sortOrder = $arguments[1];
			}
			$sortedNodes = array();
			$sortSequence = array();
			$nodesByIdentifier = array();
			/** @var Node $node  */
			foreach ($nodes as $node) {
				$propertyValue = $node->getProperty($sortByPropertyPath);
				// \TYPO3\Flow\var_dump($propertyValue);
				if ($propertyValue instanceof \DateTime) {
					$propertyValue = $propertyValue->getTimestamp();
				}
				$sortSequence[$node->getIdentifier()] = $propertyValue;
				$nodesByIdentifier[$node->getIdentifier()] = $node;
			}
			if ($sortOrder === 'DESC') {
				arsort($sortSequence);
			} else {
				asort($sortSequence);
			}
			foreach ($sortSequence as $nodeIdentifier => $value) {
				$sortedNodes[] = $nodesByIdentifier[$nodeIdentifier];
			}
			$flowQuery->setContext($sortedNodes);
		}
	}
}