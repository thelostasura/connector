<?php

namespace TheLostAsura\Connector\Lib;

use TheLostAsura\Connector\Utils\Http;

class SDK {

    public function remoteRequest( string $method, array $query = [], string $path, $provider ) {
        return Http::timeout( 60 )
                        ->baseUrl( "{$provider->provider}/{$provider->endpoint}/{$provider->version}" )
                        ->acceptJson()
                        ->{$method}( $path, array_merge($query, [
                            'api_key'    => $provider->api_key,
                            'api_secret' => $provider->api_secret,
                        ]) );
    }

    public function license_domains_register($provider, string $license_key) 
    {
        return $this->remoteRequest('post', [
            'key' => $license_key,
            'domain' => home_url(),
        ], "/licenses/domains/register", $provider);
    }

    public function license_domains_deregister($provider, string $license_key) 
    {
        return $this->remoteRequest('post', [
            'key' => $license_key,
            'domain' => home_url(),
        ], "/licenses/domains/deregister", $provider);
    }

    public function license_terms_index($provider, string $hash) 
    {
        return $this->remoteRequest('get', [
            'hash' => $hash,
        ], "/licenses/terms", $provider);
    }

    public function oxygenbuilder_items($provider, string $hash, string $term_slug) 
    {
        return $this->remoteRequest('get', [
            'hash' => $hash,
            'term_slug' => $term_slug,
        ], "/oxygenbuilder/items", $provider);
    }

    public function oxygenbuilder_pageclasses($provider, string $hash, string $term_slug, int $post_id) 
    {
        return $this->remoteRequest('get', [
            'hash' => $hash,
            'term_slug' => $term_slug,
            'post_id' => $post_id,
        ], "/oxygenbuilder/pageclasses", $provider);
    }

    public function oxygenbuilder_componentclasses($provider, string $hash, string $term_slug, int $post_id, int $component_id) 
    {
        return $this->remoteRequest('get', [
            'hash' => $hash,
            'term_slug' => $term_slug,
            'post_id' => $post_id,
            'component_id' => $component_id,
        ], "/oxygenbuilder/componentclasses", $provider);
    }

}