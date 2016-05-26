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

namespace Jhoechtl\Digitalista\Aspect;

use Detection\MobileDetect;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\Http\Request;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class AmpMobileAspect {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Inject the settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @Flow\Before("method(TYPO3\Neos\Controller\Frontend\NodeController->showAction())")
	 * @param JoinPointInterface $joinPoint
	 * @return void
	 */
	public function trackPageViewAspect(JoinPointInterface $joinPoint) {
		/** @var NodeInterface $node */
		$node = $joinPoint->getMethodArgument('node');
		if ($node instanceof NodeInterface
			&& isset($this->settings['enableAMP'])
			&& $this->settings['enableAMP'] === TRUE) {

			$request = Request::createFromEnvironment();
			$requestAbsolutePath = '/' . $request->getRelativePath();
			$mobileDetector = new MobileDetect();

			if ($mobileDetector->isMobile()
				&& strpos($requestAbsolutePath, '.amp.html') === FALSE
				&& $node->getNodeType()->getName() === 'Jhoechtl.Digitalista:News') {
				header('Location: ' . substr($requestAbsolutePath, 0, -5) . '.amp.html');
				die();
			}
		}
	}
}
