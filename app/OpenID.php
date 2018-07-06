<?php

namespace App;

class OpenID extends \OAuth2\Server {
    protected function createDefaultIdTokenResponseType()
    {
        if (!isset($this->storages['user_claims'])) {
            throw new \LogicException('You must supply a storage object implementing OAuth2\OpenID\Storage\UserClaimsInterface to use openid connect');
        }
        if (!isset($this->storages['public_key'])) {
            throw new \LogicException('You must supply a storage object implementing OAuth2\Storage\PublicKeyInterface to use openid connect');
        }

        $config = array_intersect_key($this->config, array_flip(explode(' ', 'issuer id_lifetime')));

        return new IdToken($this->storages['user_claims'], $this->storages['public_key'], $config);
    }
}