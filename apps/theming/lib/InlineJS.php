<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Theming;

use OCP\AppFramework\Http\Inline\IInline;
use OCP\IConfig;

class InlineJS implements IInline {
	/** @var ThemingDefaults */
	private $themingDefaults;
	/** @var IConfig */
	private $config;
	/** @var Util */
	private $util;

	public function __construct(IConfig $config, Util $util, ThemingDefaults $themingDefaults) {
		$this->themingDefaults = $themingDefaults;
		$this->config = $config;
		$this->util = $util;
	}

	function getData(): string {
		$cacheBusterValue = $this->config->getAppValue('theming', 'cachebuster', '0');
		$responseJS = '(function() {
	OCA.Theming = {
		name: ' . json_encode($this->themingDefaults->getName()) . ',
		url: ' . json_encode($this->themingDefaults->getBaseUrl()) . ',
		slogan: ' . json_encode($this->themingDefaults->getSlogan()) . ',
		color: ' . json_encode($this->themingDefaults->getColorPrimary()) . ',
		imprintUrl: ' . json_encode($this->themingDefaults->getImprintUrl()) . ',
		privacyUrl: ' . json_encode($this->themingDefaults->getPrivacyUrl()) . ',
		inverted: ' . json_encode($this->util->invertTextColor($this->themingDefaults->getColorPrimary())) . ',
		cacheBuster: ' . json_encode($cacheBusterValue) . '
	};
})();';
		return $responseJS;
	}
}
