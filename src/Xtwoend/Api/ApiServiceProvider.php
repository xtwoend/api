<?php namespace Xtwoend\Api;

/*
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

use Xtwoend\Api\Api;
use Xtwoend\Api\Facades\Response;
use Xtwoend\Api\OAuth2\OAuthServer;

class ApiServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
     * All of the application's route middleware keys.
     *
     * @var array
     */
    protected $middleware = [
        'api.oauth'         => 'Xtwoend\Api\Middleware\Oauth',
        'api.ua.required'   => 'Xtwoend\Api\Middleware\UserAgent',
        'api.limit'         => 'Xtwoend\Api\Middleware\LimitRequest',
        'api.content.md5'   => 'Xtwoend\Api\Middleware\ValidationMd5'
    ];

    protected $stack = [
        'Xtwoend\Api\Middleware\AfterMiddleware',
    ];
    
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('xtwoend/api', 'xtwoend/api');
	    
        //load middleware
        $this->addMiddleware();
    }

    /**
     * Add the short-hand middleware names to the router.
     *
     * @return void
     */
    protected function addMiddleware()
    {
        $router = $this->app['router'];
        
        $this->app->call([$this, 'map']);

        foreach ($this->middleware as $key => $value)
        {
            $router->middleware($key, $value);
        }
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->post('authorizations', array('before' => array(
            'api.ua.required', 
            'api.limit', 
            //'api.content.md5'
        ), function() {
            return $this->app->make('api')->performAccessTokenFlow();
        }));
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->registerOAuthServer();
        $this->registerApi();

        // Register artisan commands
		$this->registerCommands();
	}

    /**
     * Register the OAuth server.
     *
     * @return void
     */
    private function registerOAuthServer()
    {
        $this->app->bind('League\OAuth2\Server\Storage\ClientInterface', 'Xtwoend\Api\OAuth2\Repositories\FluentClient');
        $this->app->bind('League\OAuth2\Server\Storage\ScopeInterface', 'Xtwoend\Api\OAuth2\Repositories\FluentScope');
        $this->app->bind('League\OAuth2\Server\Storage\SessionInterface', 'Xtwoend\Api\OAuth2\Repositories\FluentSession');
        $this->app->bind('Xtwoend\Api\OAuth2\Repositories\SessionManagementInterface', 'Xtwoend\Api\OAuth2\Repositories\FluentSession');

        $this->app['api.authorization'] = $this->app->share(function ($app) {

            $server = $app->make('League\OAuth2\Server\Authorization');

            $config = $app['config']->get('xtwoend/api::oauth2');

            // add the supported grant types to the authorization server
            foreach ($config['grant_types'] as $grantKey => $grantValue) {

                $server->addGrantType(new $grantValue['class']($server));
                $server->getGrantType($grantKey)->setAccessTokenTTL($grantValue['access_token_ttl']);

                if (array_key_exists('callback', $grantValue)) {
                    $server->getGrantType($grantKey)->setVerifyCredentialsCallback($grantValue['callback']);
                }

                if (array_key_exists('auth_token_ttl', $grantValue)) {
                    $server->getGrantType($grantKey)->setAuthTokenTTL($grantValue['auth_token_ttl']);
                }

                if (array_key_exists('refresh_token_ttl', $grantValue)) {
                    $server->getGrantType($grantKey)->setRefreshTokenTTL($grantValue['refresh_token_ttl']);
                }

                if (array_key_exists('rotate_refresh_tokens', $grantValue)) {
                    $server->getGrantType($grantKey)->rotateRefreshTokens($grantValue['rotate_refresh_tokens']);
                }
            }

            $server->requireStateParam($config['state_param']);

            $server->requireScopeParam($config['scope_param']);

            $server->setScopeDelimeter($config['scope_delimiter']);

            $server->setDefaultScope($config['default_scope']);

            $server->setAccessTokenTTL($config['access_token_ttl']);

            return new OAuthServer($server);

        });

        $this->app['api.resource'] = $this->app->share(function ($app) {

            return $app->make('Xtwoend\Api\OAuth2\Resource');

        });
    }

    /**
     * Register the Api.
     *
     * @return void
     */
    public function registerApi()
    {
        $this->app['api'] = $this->app->share(function ($app) {
            $config = $app['config']->get('xtwoend/api::config');
            $config['oauth2'] = $app['config']->get('xtwoend/api::oauth2');

            return new Api($config, $app['request'], new Response(), $app['api.authorization'], $app['api.resource']);
        });
    }

	/**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        // Command to create a new OAuth client
        $this->app['command.api.newOAuthClient'] = $this->app->share(function ($app) {
            return $app->make('Xtwoend\Api\Commands\NewOAuthClient');
        });

        // Command to create a new OAuth scope
        $this->app['command.api.newOAuthScope'] = $this->app->share(function ($app) {
            return $app->make('Xtwoend\Api\Commands\NewOAuthScope');
        });

        // Command to clean expired OAuth tokens
        $this->app['command.api.cleanExpiredTokens'] = $this->app->share(function ($app) {
            return $app->make('Xtwoend\Api\Commands\CleanExpiredTokens');
        });

        $this->commands(
        	'command.api.newOAuthClient', 
            'command.api.newOAuthScope', 
            'command.api.cleanExpiredTokens'
        );
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('api', 'api.authorization', 'api.resource');
	}

}