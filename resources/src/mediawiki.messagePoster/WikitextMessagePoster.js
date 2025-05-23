( function () {
	/**
	 * @classdesc Posts messages to wikitext talk pages.
	 *
	 * @class mw.messagePoster.WikitextMessagePoster
	 * @extends mw.messagePoster.MessagePoster
	 * @type {mw.messagePoster.WikitextMessagePoster}
	 *
	 * @constructor
	 * @description Create an instance of `mw.messagePoster.WikitextMessagePoster`.
	 * @param {mw.Title} title Wikitext page in a talk namespace, to post to
	 * @param {mw.Api} api mw.Api object to use
	 */
	function WikitextMessagePoster( title, api ) {
		this.api = api;
		this.title = title;
	}

	OO.inheritClass(
		WikitextMessagePoster,
		mw.messagePoster.MessagePoster
	);

	/**
	 * @inheritdoc
	 * @param {string} subject Section title.
	 * @param {string} body Message body, as wikitext. Signature code will automatically be added unless the message already contains the string ~~~.
	 * @param {Object} [options] Message options:
	 * @param {string} [options.tags] [Change tags](https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Tags) to add to the message's revision, pipe-separated.
	 */
	WikitextMessagePoster.prototype.post = function ( subject, body, options ) {
		options = options || {};
		mw.messagePoster.WikitextMessagePoster.super.prototype.post.call( this, subject, body, options );

		// Add signature if needed
		if ( !body.includes( '~~~' ) ) {
			body += '\n\n~~~~';
		}

		const additionalParams = { redirect: true };
		if ( options.tags !== undefined ) {
			additionalParams.tags = options.tags;
		}
		return this.api.newSection(
			this.title,
			subject,
			body,
			additionalParams
		).then( ( resp, jqXHR ) => {
			if ( resp.edit.result === 'Success' ) {
				return $.Deferred().resolve( resp, jqXHR );
			} else {
				// mw.Api checks for response error.  Are there actually cases where the
				// request fails, but it's not caught there?
				return $.Deferred().reject( 'api-unexpected' );
			}
		}, ( code, details ) => $.Deferred().reject( 'api-fail', code, details ) ).promise();
	};

	mw.messagePoster.factory.register( 'wikitext', WikitextMessagePoster );
	mw.messagePoster.WikitextMessagePoster = WikitextMessagePoster;
}() );
