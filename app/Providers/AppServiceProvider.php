<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        \Braintree_Configuration::environment(config('services.braintree.environment'));
        \Braintree_Configuration::merchantId(config('services.braintree.merchant_id'));
        \Braintree_Configuration::publicKey(config('services.braintree.public_key'));
        \Braintree_Configuration::privateKey(config('services.braintree.private_key'));


        $this->app['translator']->addNamespace('adminlte_lang', base_path() . '/resources/lang/adminlte_lang');

        Validator::extend('missing_with', function ($attribute, $value, $parameters, $validator) {
            foreach($validator->getData() as $field => $v) {
                if (in_array($field, $parameters)) {
                    return false;
                }
            }

            return true;
        });

        Validator::replacer('missing_with', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':other', implode(',', $parameters), $message);
        });

        Validator::extend('uuid', function ($attribute, $value, $parameters, $validator) {
            return (bool) preg_match('/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/', $value);
        });

        Validator::extend('tid', function ($attribute, $value, $parameters, $validator) {
            return (bool) preg_match('/^urn:tid:[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/', $value);
        });

        // \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
        //     'facebook' => 'App\Channels\Facebook',
        // ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);

        $this->app->singleton(\Elasticsearch\Client::class, function ($app) {
            return \Elasticsearch\ClientBuilder::create()->setHosts(['elastic-search-001'])->build();
        });

        $this->app->singleton(\GoogleMapsGeocoder::class, function ($app) {
            $geo = new \GoogleMapsGeocoder();
            $geo->setLanguage('en');
            $geo->setApiKey('');

            return $geo;
        });

        $this->app->singleton('Mobimesh\Openid', function($app) {
            $dsn = 'mysql:dbname=' . config('database.connections.mysql.database') . ';host=' . config('database.connections.mysql.host');

            $storage = new \OAuth2\Storage\Pdo([
                'dsn'      => $dsn,
                'username' => config('database.connections.mysql.username'),
                'password' => config('database.connections.mysql.password')
            ], [
                'client_table'          => 'openid_clients',
                'access_token_table'    => 'openid_access_tokens',
                'scope_table'           => 'openid_scopes',
                'code_table'            => 'openid_authorization_codes',
                //'refresh_token_table' => 'openid_refresh_tokens',
                //'user_table'          => 'openid_users',
                //'jwt_table'           => 'openid_jwt',
                //'jti_table'           => 'openid_jti',
                //'public_key_table'    => 'openid_public_keys',
            ]); // \App\MyPdo

            $config['use_openid_connect'] = true;
            $config['issuer'] = 'http://cyrano.teia.company';

            // create the server
            $server = new \App\OpenID($storage, $config);
            $server->addGrantType(new \OAuth2\OpenID\GrantType\AuthorizationCode($server->getStorage('authorization_code')));

            $privateKey  = file_get_contents(\Laravel\Passport\Passport::keyPath('oauth-private.key'));
            $publicKey = file_get_contents(\Laravel\Passport\Passport::keyPath('oauth-public.key'));

            // create storage
            $keyStorage = new \OAuth2\Storage\Memory([
                'keys' => [
                    'public_key'  => $publicKey,
                    'private_key' => $privateKey,
                ]
            ]);

            $server->addStorage($keyStorage, 'public_key');

            // $config = array_intersect_key($config, array_flip(explode(' ', 'allow_implicit enforce_state require_exact_redirect_uri')));

            // $server->getAuthorizeController();

            // responseTypes['id_token'] = $this->createDefaultIdTokenResponseType();
            // $server->setAuthorizeController(new \App\AuthorizeController($server->getStorage('client'), $server->getResponseTypes(), $config, $server->getScopeUtil()));

            return $server;
        });
    }
}
