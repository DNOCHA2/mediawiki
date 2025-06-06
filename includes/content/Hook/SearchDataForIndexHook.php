<?php

namespace MediaWiki\Content\Hook;

use MediaWiki\Content\ContentHandler;
use MediaWiki\Page\WikiPage;
use MediaWiki\Parser\ParserOutput;
use SearchEngine;

/**
 * This is a hook handler interface, see docs/Hooks.md.
 * Use the hook name "SearchDataForIndex" to register handlers implementing this interface.
 *
 * @stable to implement
 * @deprecated since 1.40, use SearchDataForIndexHook2 instead.
 * @ingroup Hooks
 */
interface SearchDataForIndexHook {
	/**
	 * Use this hook to add data to search document. Allows you to add any data to
	 * the field map used to index the document.
	 *
	 * @since 1.35
	 *
	 * @param array &$fields Array of name => value pairs for fields
	 * @param ContentHandler $handler ContentHandler for the content being indexed
	 * @param WikiPage $page WikiPage that is being indexed
	 * @param ParserOutput $output ParserOutput that is produced from the page
	 * @param SearchEngine $engine SearchEngine for which the indexing is intended
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSearchDataForIndex( &$fields, $handler, $page, $output,
		$engine
	);
}
