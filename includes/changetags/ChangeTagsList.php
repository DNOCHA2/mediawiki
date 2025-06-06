<?php
/**
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
 */

namespace MediaWiki\ChangeTags;

use InvalidArgumentException;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\RevisionList\RevisionListBase;
use MediaWiki\Status\Status;

/**
 * Generic list for change tagging.
 *
 * @ingroup ChangeTags
 * @property ChangeTagsLogItem $current
 * @method ChangeTagsLogItem next()
 * @method ChangeTagsLogItem reset()
 * @method ChangeTagsLogItem current()
 */
abstract class ChangeTagsList extends RevisionListBase {
	public function __construct( IContextSource $context, PageIdentity $page, array $ids ) {
		parent::__construct( $context, $page );
		$this->ids = $ids;
	}

	/**
	 * Create a ChangeTagsList instance of the given type.
	 *
	 * @param string $typeName 'revision' or 'logentry'
	 * @param IContextSource $context
	 * @param PageIdentity $page
	 * @param array $ids
	 * @return ChangeTagsList An instance of the requested subclass
	 * @throws InvalidArgumentException If you give an unknown $typeName
	 */
	public static function factory( $typeName, IContextSource $context,
		PageIdentity $page, array $ids
	) {
		switch ( $typeName ) {
			case 'revision':
				$className = ChangeTagsRevisionList::class;
				break;
			case 'logentry':
				$className = ChangeTagsLogList::class;
				break;
			default:
				throw new InvalidArgumentException( "Class $typeName requested, but does not exist" );
		}

		return new $className( $context, $page, $ids );
	}

	/**
	 * Reload the list data from the primary DB.
	 */
	public function reloadFromPrimary() {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		$this->res = $this->doQuery( $dbw );
	}

	/**
	 * Add/remove change tags from all the items in the list.
	 *
	 * @param string[] $tagsToAdd
	 * @param string[] $tagsToRemove
	 * @param string|null $params
	 * @param string $reason
	 * @param Authority $performer
	 * @return Status
	 */
	abstract public function updateChangeTagsOnAll(
		array $tagsToAdd,
		array $tagsToRemove,
		?string $params,
		string $reason,
		Authority $performer
	);
}

/** @deprecated class alias since 1.44 */
class_alias( ChangeTagsList::class, 'ChangeTagsList' );
