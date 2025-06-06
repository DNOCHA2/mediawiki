<?php
/**
 * Check digit transformation
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup MaintenanceLanguage
 */

use MediaWiki\Maintenance\Maintenance;

// @codeCoverageIgnoreStart
require_once __DIR__ . '/../Maintenance.php';
// @codeCoverageIgnoreEnd

/**
 * Maintenance script that check digit transformation.
 *
 * @ingroup MaintenanceLanguage
 */
class Digit2Html extends Maintenance {

	/**
	 * @var string[] A list of unicode numerals is available at:
	 * https://www.fileformat.info/info/unicode/category/Nd/list.htm
	 */
	private $mLangs = [
		'Ar', 'As', 'Bh', 'Bo', 'Dz',
		'Fa', 'Gu', 'Hi', 'Km', 'Kn',
		'Ks', 'Lo', 'Ml', 'Mr', 'Ne',
		'New', 'Or', 'Pa', 'Pi', 'Sa'
	];

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Check digit transformation' );
	}

	public function execute() {
		$languageNameUtils = $this->getServiceContainer()->getLanguageNameUtils();
		foreach ( $this->mLangs as $code ) {
			$filename = $languageNameUtils->getMessagesFileName( $code );
			$this->output( "Loading language [$code] ..." );
			unset( $digitTransformTable );
			require_once $filename;
			if ( !isset( $digitTransformTable ) ) {
				$this->error( "\$digitTransformTable not found for lang: $code" );
				continue;
			}

			$this->output( "OK\n\$digitTransformTable = [\n" );
			foreach ( $digitTransformTable as $latin => $translation ) {
				$htmlent = bin2hex( $translation );
				$this->output( "'$latin' => '$translation', # &#x$htmlent;\n" );
			}
			$this->output( "];\n" );
		}
	}
}

// @codeCoverageIgnoreStart
$maintClass = Digit2Html::class;
require_once RUN_MAINTENANCE_IF_MAIN;
// @codeCoverageIgnoreEnd
