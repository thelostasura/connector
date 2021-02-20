<?php

namespace TheLostAsura\Connector\Lib;

use TheLostAsura\Connector\Utils\Http;

class SDK {

	/**
	 *
	 * @param object $provider
	 * @param string $license_key
	 *
	 * @return mixed
	 */
	public function license_domains_register( $provider, $license_key ) {
		return $this->remoteRequest( 'post', [
			'key'    => $license_key,
			'domain' => home_url(),
		], "/licenses/domains/register", $provider );
	}

	/**
	 *
	 * @param string $method
	 * @param array $query
	 * @param string $path
	 * @param object $provider
	 *
	 * @return mixed
	 */
	public function remoteRequest( $method, $query = [], $path, $provider ) {
		return Http::timeout( 60 )
		           ->baseUrl( "{$provider->provider}/{$provider->endpoint}/{$provider->version}" )
		           ->acceptJson()
		           ->{$method}( $path, array_merge( $query, [
			           'api_key'    => $provider->api_key,
			           'api_secret' => $provider->api_secret,
		           ] ) );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $license_key
	 *
	 * @return mixed
	 */
	public function license_domains_deregister( $provider, $license_key ) {
		return $this->remoteRequest( 'post', [
			'key'    => $license_key,
			'domain' => home_url(),
		], "/licenses/domains/deregister", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 *
	 * @return mixed
	 */
	public function license_terms_index( $provider, $hash ) {
		return $this->remoteRequest( 'get', [
			'hash' => $hash,
		], "/licenses/terms", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public function oxygenbuilder_items( $provider, $hash, $term_slug ) {
		return $this->remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "/oxygenbuilder/items", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 * @param string|int $post_id
	 *
	 * @return mixed
	 */
	public function oxygenbuilder_pageclasses( $provider, $hash, $term_slug, $post_id ) {
		return $this->remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
			'post_id'   => $post_id,
		], "/oxygenbuilder/pageclasses", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 * @param string|int $post_id
	 * @param string|int $component_id
	 *
	 * @return mixed
	 */
	public function oxygenbuilder_componentclasses( $provider, $hash, $term_slug, $post_id, $component_id ) {
		return $this->remoteRequest( 'get', [
			'hash'         => $hash,
			'term_slug'    => $term_slug,
			'post_id'      => $post_id,
			'component_id' => $component_id,
		], "/oxygenbuilder/componentclasses", $provider );
	}

	/**
	 *
	 * @param object $provider
	 * @param string $hash
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public function oxygenbuilder_colors( $provider, $hash, $term_slug ) {
		return $this->remoteRequest( 'get', [
			'hash'      => $hash,
			'term_slug' => $term_slug,
		], "/oxygenbuilder/colors", $provider );
	}

}