<?php
/**
 * API_Request_Manager class file.
 *
 * @package WPE_Update_Source_Selector
 */

namespace WPE_Update_Source_Selector;

use WP_Error;

/**
 * Class: API_Request_Manager
 *
 * Manages filtering of API requests.
 */
class API_Request_Manager {
	/**
	 * Instance of the main class for easy access.
	 *
	 * @var WPE_Update_Source_Selector
	 */
	private $wpe_uss;

	/**
	 * Core domains to be searched for in requests.
	 *
	 * @var array<string,string>
	 */
	private $search_domains = array();

	/**
	 * Alt domains to be used for replacements in requests.
	 *
	 * @var array<string,string>
	 */
	private $replace_domains = array();

	/**
	 * API Request Manager constructor.
	 *
	 * @param WPE_Update_Source_Selector $wpe_uss Instance of the main class.
	 *
	 * @return void
	 */
	public function __construct( WPE_Update_Source_Selector $wpe_uss ) {
		$this->wpe_uss = $wpe_uss;

		// Set up filtering of requests.
		add_filter( 'pre_http_request', array( $this, 'filter_pre_http_request' ), 10, 3 );
		add_filter( 'wpe_uss_pre_http_request', array( $this, 'init' ) );
	}

	/**
	 * Initialize the API Request Manager.
	 *
	 * This is only done on the first http request to be filtered, to delay
	 * initialization as long as possible. In some cases, for example frontend
	 * only processes that do not perform a remote http request, this means
	 * initialization may not be needed at all.
	 *
	 * @handles wpe_uss_pre_http_request
	 *
	 * @param bool $skip  Whether filtering should be skipped.
	 * @param bool $force Whether initialization should be forced if previously done, default false.
	 *
	 * @return bool Whether filtering should be skipped.
	 */
	public function init( bool $skip, bool $force = false ): bool {
		static $initialized = false;

		if ( ( ! $force && $initialized ) || $skip ) {
			return $skip;
		}
		$initialized = true;

		// No rewrites necessary, bail early.
		if ( $this->wpe_uss->get_core_source()::get_key() === $this->wpe_uss->get_alt_source()::get_key() ) {
			return true;
		}

		$core_domains = $this->wpe_uss->get_core_source()::get_domains();
		$alt_domains  = $this->wpe_uss->get_alt_source()::get_domains();

		// No core or alt source domains to be used in rewrite?
		if ( empty( $core_domains ) || empty( $alt_domains ) ) {
			return true;
		}

		// Check that all core source domains are covered by alt domains.
		// We currently do not allow for only some core domains to be rewritten.
		if ( ! empty( array_diff_key( $core_domains, $alt_domains ) ) ) {
			return true;
		}

		// Set up the search and replace pairs.
		foreach ( $core_domains as $type => $domain ) {
			$this->search_domains[ $type ]  = $domain;
			$this->replace_domains[ $type ] = $alt_domains[ $type ];
		}

		// If here, rewrites are possible, and skip hasn't been requested yet.
		return false;
	}

	/**
	 * Returns the filtered array of search domains.
	 *
	 * For performance, this function is set up to only fire its filter once,
	 * when a request is made, assuming there are search domains returned.
	 *
	 * @return array<string,string>
	 */
	protected function get_search_domains(): array {
		static $search_domains;

		if ( ! empty( $search_domains ) ) {
			return $search_domains;
		}

		/**
		 * Filter enables modifying the associative array of domains
		 * to be searched for replacement.
		 *
		 * See the `wpe_uss_replace_domains` for where replacements must be specified
		 * using the same array keys. If they do not match, no replacement will happen.
		 *
		 * @param array<string,string> $search_domains Associative array of domains to replace.
		 */
		$search_domains = apply_filters( 'wpe_uss_search_domains', $this->search_domains );

		if ( empty( $search_domains ) || ! is_array( $search_domains ) ) {
			return array();
		}

		// Make sure filtered domains have valid keys and values.
		$valid = true;
		foreach ( $search_domains as $type => $domain ) {
			if ( ! is_string( $type ) || ! is_string( $domain ) ) {
				$valid = false;
				break;
			}
		}

		if ( ! $valid ) {
			return array();
		}

		// Ensure domains are in a consistent order based on unique type.
		ksort( $search_domains );

		return $search_domains;
	}

	/**
	 * Returns the filtered array of replace domains.
	 *
	 * For performance, this function is set up to only fire its filter once,
	 * when a request is made, assuming there are replace domains returned.
	 *
	 * @return array<string,string>
	 */
	protected function get_replace_domains(): array {
		static $replace_domains;

		if ( ! empty( $replace_domains ) ) {
			return $replace_domains;
		}

		/**
		 * Filter enables modifying the associative array of domains
		 * to be used for replacement.
		 *
		 * See the `wpe_uss_search_domains` for where search domains must be specified
		 * using the same array keys. If they do not match, no replacement will happen.
		 *
		 * @param array<string,string> $replace_domains Associative array of domains to use for replacement.
		 */
		$replace_domains = apply_filters( 'wpe_uss_replace_domains', $this->replace_domains );

		if ( empty( $replace_domains ) || ! is_array( $replace_domains ) ) {
			return array();
		}

		// Ensure domains are in a consistent order based on unique type.
		ksort( $replace_domains );

		return $replace_domains;
	}

	/**
	 * Returns the given domains each prefixed to improve search/replace accuracy.
	 *
	 * @param array<string,string> $domains Bare domains to be prefixed.
	 *
	 * @return array<string,string>
	 */
	protected static function prefix_domains( array $domains ): array {
		return array_map(
			function ( $domain ): string {
				return "://$domain";
			},
			$domains
		);
	}

	/**
	 * Filters the preemptive return value of an HTTP request.
	 *
	 * If request is for a core source domain to be rewritten, request is made with rewritten URLs.
	 *
	 * @handles pre_http_request
	 *
	 * @param false|array|WP_Error $response    A preemptive return value of a HTTP request. Default false.
	 * @param string|array         $parsed_args HTTP request arguments.
	 * @param mixed                $url         The request URL.
	 *
	 * @return false|array|WP_Error Response array if URLs rewritten, otherwise passed in response.
	 */
	public function filter_pre_http_request( $response, $parsed_args = array(), $url = null ) {
		// Already handled.
		if ( false !== $response ) {
			return $response;
		}

		// If there's no URL, bail.
		if ( empty( $url ) || ! is_string( $url ) ) {
			return false;
		}

		// Enable skipping of rewrites, e.g. during source status checks,
		// or if current settings dictate we'll never rewrite core requests.
		if ( apply_filters( 'wpe_uss_pre_http_request', false ) ) {
			return false;
		}

		$new_url = $this->rewrite_url( $url );

		if ( $url !== $new_url ) {
			// Perform the request with the updated URL.
			return wp_remote_request(
				$new_url,
				$parsed_args
			);
		}

		return false;
	}

	/**
	 * Rewrite URL, if needed.
	 *
	 * @param string $url The request URL.
	 *
	 * @return string URL, possibly with rewritten domain.
	 */
	protected function rewrite_url( string $url ): string {
		if ( empty( $url ) ) {
			return $url;
		}

		$search_domains  = $this->get_search_domains();
		$replace_domains = $this->get_replace_domains();

		// Check that all search domains are covered by replace domains.
		if ( ! empty( array_diff_key( $search_domains, $replace_domains ) ) ) {
			return $url;
		}

		// Check host is rewritable.
		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( empty( $host ) || ! in_array( $host, $search_domains, true ) ) {
			return $url;
		}

		// Rewrite URL.
		return str_replace(
			static::prefix_domains( $search_domains ),
			static::prefix_domains( $replace_domains ),
			$url
		);
	}
}
