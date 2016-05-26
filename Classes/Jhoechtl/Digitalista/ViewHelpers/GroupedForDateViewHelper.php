<?php
namespace Jhoechtl\Digitalista\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Jhoechtl.Digitalista".  *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Fluid\Core\ViewHelper;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\ViewHelpers\GroupedForViewHelper;

class GroupedForDateViewHelper extends GroupedForViewHelper {

	/**
	 * Groups the given array by the specified groupBy property.
	 *
	 * @param array $elements The array / traversable object to be grouped
	 * @param string $groupBy Group by this property
	 * @return array The grouped array in the form array('keys' => array('key1' => [key1value], 'key2' => [key2value], ...), 'values' => array('key1' => array([key1value] => [element1]), ...), ...)
	 * @throws ViewHelper\Exception
	 */
	protected function groupElements(array $elements, $groupBy) {
		$groups = array('keys' => array(), 'values' => array());
		foreach ($elements as $key => $value) {
			if (is_array($value)) {
				$currentGroupIndex = isset($value[$groupBy]) ? $value[$groupBy] : NULL;
			} elseif (is_object($value)) {
				$currentGroupIndex = ObjectAccess::getPropertyPath($value, $groupBy);
				if ($currentGroupIndex instanceof \DateTime) {
					setlocale(LC_TIME, "de_DE");
					$currentGroupIndex = strftime('%d. %B %Y', $currentGroupIndex->getTimestamp());
				}
			} else {
				throw new ViewHelper\Exception('GroupedForViewHelper only supports multi-dimensional arrays and objects', 1253120365);
			}
			$currentGroupKeyValue = $currentGroupIndex;
			if ($currentGroupIndex instanceof \DateTime) {
				$currentGroupIndex = $currentGroupIndex->format(\DateTime::RFC850);
			} elseif (is_object($currentGroupIndex)) {
				$currentGroupIndex = spl_object_hash($currentGroupIndex);
			}
			$groups['keys'][$currentGroupIndex] = $currentGroupKeyValue;
			$groups['values'][$currentGroupIndex][$key] = $value;
		}
		return $groups;
	}
}
