( function () {

	const saveOptionsRequests = {};

	Object.assign( mw.Api.prototype, /** @lends mw.Api.prototype */ {

		/**
		 * Asynchronously save the value of a single user option using the API.
		 * See [saveOptions()]{@link mw.Api#saveOptions}.
		 *
		 * @param {string} name
		 * @param {string|null} value
		 * @param {Object} [params] additional parameters for API.
		 * @return {jQuery.Promise}
		 */
		saveOption: function ( name, value, params ) {
			const options = {};
			options[ name ] = value;
			return this.saveOptions( options, params );
		},

		/**
		 * Asynchronously save the values of user options using the [Options API](https://www.mediawiki.org/wiki/API:Options).
		 *
		 * If a value of `null` is provided, the given option will be reset to the default value.
		 *
		 * Any warnings returned by the API, including warnings about invalid option names or values,
		 * are ignored. However, do not rely on this behavior.
		 *
		 * If necessary, the options will be saved using several sequential API requests. Only one promise
		 * is always returned that will be resolved when all requests complete.
		 *
		 * If a request from a previous `saveOptions()` call is still pending, this will wait for it to be
		 * completed, otherwise MediaWiki gets sad. No requests are sent for anonymous users, as they
		 * would fail anyway. See T214963.
		 *
		 * @param {Object} options Options as a `{ name: value, … }` object
		 * @param {Object} [params] additional parameters for API.
		 * @return {jQuery.Promise}
		 */
		saveOptions: function ( options, params ) {
			const grouped = [];

			// Logged-out users can't have user options; we can't depend on mw.user, that'd be circular
			if ( mw.config.get( 'wgUserName' ) === null || mw.config.get( 'wgUserIsTemp' ) ) {
				return $.Deferred().reject( 'notloggedin' ).promise();
			}

			let promise;
			// If another options request to this API is pending, wait for it first
			if (
				saveOptionsRequests[ this.defaults.ajax.url ] &&
				// Avoid long chains of promises, they may cause memory leaks
				saveOptionsRequests[ this.defaults.ajax.url ].state() === 'pending'
			) {
				promise = saveOptionsRequests[ this.defaults.ajax.url ].then(
					// Don't expose the old promise's result, it would be confusing
					() => $.Deferred().resolve(),
					() => $.Deferred().resolve()
				);
			} else {
				promise = $.Deferred().resolve();
			}

			for ( const name in options ) {
				const value = options[ name ] === null ? null : String( options[ name ] );

				let bundleable;
				// Can we bundle this option, or does it need a separate request?
				if ( this.defaults.useUS ) {
					bundleable = !name.includes( '=' );
				} else {
					bundleable =
						( value === null || !value.includes( '|' ) ) &&
						( !name.includes( '|' ) && !name.includes( '=' ) );
				}

				if ( bundleable ) {
					if ( value !== null ) {
						grouped.push( name + '=' + value );
					} else {
						// Omitting value resets the option
						grouped.push( name );
					}
				} else {
					if ( value !== null ) {
						promise = promise.then( function ( n, v ) {
							return this.postWithToken( 'csrf', Object.assign( {
								formatversion: 2,
								action: 'options',
								optionname: n,
								optionvalue: v
							}, params ) );
						}.bind( this, name, value ) );
					} else {
						// Omitting value resets the option
						promise = promise.then( function ( n ) {
							return this.postWithToken( 'csrf', Object.assign( {
								formatversion: 2,
								action: 'options',
								optionname: n
							}, params ) );
						}.bind( this, name ) );
					}
				}
			}

			if ( grouped.length ) {
				promise = promise.then( () => this.postWithToken( 'csrf', Object.assign( {
					formatversion: 2,
					action: 'options',
					change: grouped
				}, params ) ) );
			}

			saveOptionsRequests[ this.defaults.ajax.url ] = promise;

			return promise;
		}

	} );

}() );
