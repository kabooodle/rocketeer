<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Binaries;

use Rocketeer\Abstracts\AbstractBinary;

class Bower extends AbstractBinary
{
	/**
	 * Get an array of default paths to look for
	 *
	 * @return string[]
	 */
	protected function getKnownPaths()
	{
		return array(
			'bower',
			$this->releasesManager->getCurrentReleasePath().'/node_modules/.bin/bower'
		);
	}
}
