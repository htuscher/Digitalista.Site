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
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\Node;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

class RelatedOperation extends AbstractOperation {
	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	static protected $shortName = 'related';
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
	/**
	 * {@inheritdoc}
	 *
	 * @param FlowQuery $flowQuery the FlowQuery object
	 * @param array $arguments the arguments for this operation
	 * @return mixed
	 */
	public function evaluate(FlowQuery $flowQuery, array $arguments)
	{
		if (!isset($arguments[0]) || empty($arguments[0]) || !isset($arguments[1]) || empty($arguments[1])) {
			throw new FlowQueryException('related(propertyName, relatedNode) must be provided', 1332492263);
		} else {
			$nodes = $flowQuery->getContext();
			// Property that contains the reference
			$lookupProperty = $arguments[0];
			/** @var NodeInterface $relatedNode */
			$relatedNode = $arguments[1];
			// The last element of the nodes array is the one for which the type should be compared
			$identifier = $relatedNode->getIdentifier();
			// Define an output array
			$relatedNodes = array();
			/** @var Node $node */
			foreach ($nodes as $node) {
				if($node->hasProperty($lookupProperty)) {
					if($this->containsMatchingReference($node->getProperty($lookupProperty),$identifier)){
						$relatedNodes[] = $node;
					}
				}
			}
			$flowQuery->setContext($relatedNodes);
		}
	}
	/**
	 * Checks a parameters identifier against the identifier also passed to the function
	 * Parameter can be a node or a nodearray
	 *
	 * @param $nodeProperty single node or nodes array
	 * @param $identifier node identifier to match
	 * @return bool matching identifiers or at least one matching identifier in the nodes array
	 */
	private function containsMatchingReference($nodeProperty, $identifier) {
		// NodeProperty is of type "Reference"
		if($nodeProperty instanceof NodeInterface) {
			if($identifier === $nodeProperty->getIdentifier()) {
				return true;
			}
		}
		// NodeProperty is of type "References" - search is done for at least one matching node with a matching identifier
		elseif(is_array($nodeProperty)){
			foreach($nodeProperty as $subnode){
				if($subnode instanceof NodeInterface) {
					if($identifier === $subnode->getIdentifier()) {
						return true;
					}
				}
			}
		}
		return false;
	}
}