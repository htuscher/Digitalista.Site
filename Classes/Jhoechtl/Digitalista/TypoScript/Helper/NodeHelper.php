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

namespace Jhoechtl\Digitalista\TypoScript\Helper;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Eel\ProtectedContextAwareInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Neos\Domain\Exception;

class NodeHelper implements ProtectedContextAwareInterface {

	/**
	 * Check if the given node is already a collection, find collection by nodePath otherwise, throw exception
	 * if no content collection could be found
	 *
	 * @param NodeInterface $node
	 * @param string $nodePath
	 * @return NodeInterface
	 * @throws Exception
	 */
	public function nonEmptyPathCollection(NodeInterface $node, $nodePath) {
		$contentCollectionType = 'TYPO3.Neos:ContentCollection';
		if ($node->getNodeType()->isOfType($contentCollectionType) && $node->hasChildNodes()) {
			return $node;
		} else {
			if ((string)$nodePath === '') {
				throw new Exception(sprintf('No content collection of type %s could be found in the current node and no node path was provided. You might want to configure the nodePath property with a relative path to the content collection.', $contentCollectionType), 1409300545);
			}
			$subNode = $node->getNode($nodePath);
			if ($subNode !== NULL && $subNode->getNodeType()->isOfType($contentCollectionType) && $subNode->hasChildNodes()) {
				return $subNode;
			} else {
				return $this->nonEmptyPathCollection($node->getParent(), $nodePath);
			}
		}
	}

	/**
	 * If the given $node is inside the $collection, the node next to it will be returned, NULL otherwise
	 *
	 * @param NodeInterface[] $collection
	 * @param NodeInterface $node
	 * @return null
	 */
	public function inCollectionNextToNode(array $collection, NodeInterface $node) {
		foreach ($collection as $k => $collectionItem) {
			if ($collectionItem->getIdentifier() === $node->getIdentifier() && isset($collection[$k+1])) {
				return $collection[$k+1];
			}
		}
		return NULL;
	}

	/**
	 * If the given $node is inside the $collection, the node previous to it will be returned, NULL otherwise
	 *
	 * @param NodeInterface[] $collection
	 * @param NodeInterface $node
	 * @return null
	 */
	public function inCollectionPrevToNode(array $collection, NodeInterface $node) {
		foreach ($collection as $k => $collectionItem) {
			if ($collectionItem->getIdentifier() === $node->getIdentifier() && $k > 0) {
				return $collection[$k-1];
			}
		}
		return NULL;
	}

	/**
	 * @param string $methodName
	 * @return boolean
	 */
	public function allowsCallOfMethod($methodName) {
		return TRUE;
	}

}