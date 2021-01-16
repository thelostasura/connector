<?php

namespace TheLostAsura\Connector\Lib;

use TheLostAsura\Connector\Utils\Http;

class SDK {
    public function remoteRequest( string $method, array $query = [], string $path, $provider ) {
        return Http::timeout( 60 )
                        ->baseUrl( $provider->provider )
                        ->acceptJson()
                        ->{$method}( $path, array_merge($query, [
                            'api_key'    => $provider->api_key,
                            'api_secret' => $provider->api_secret,
                        ]) );
    }

    public function license_domains_register($provider, $license_key) 
    {
        return $this->remoteRequest('post', [
            'key' => $license_key,
            'domain' => home_url(),
        ], "/{$provider->endpoint}/{$provider->version}/licenses/domains/register", $provider);
    }

    public function license_domains_deregister($provider, $license_key) 
    {
        return $this->remoteRequest('post', [
            'key' => $license_key,
            'domain' => home_url(),
        ], "/{$provider->endpoint}/{$provider->version}/licenses/domains/deregister", $provider);
    }
}