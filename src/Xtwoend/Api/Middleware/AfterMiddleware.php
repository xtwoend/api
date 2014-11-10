<?php namespace Xtwoend\Api\Middleware;
    	
/**
 * Part of the package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    
 * @version    0.1
 * @author     Abdul Hafidz Anshari
 * @license    BSD License (3-clause)
 * @copyright  (c) 2014 
 */
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Contracts\Foundation\Application;

class AfterMiddleware implements Middleware
{	

	protected $app;

	/**
	 * 
	 * @params
	 */	
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);
        // Do stuff
		if ($request->isMethod('OPTIONS')) {
	        $headers = $this->app->make('api')->getConfig('headers');

	        if ( ! empty($headers)) {
	            foreach ($headers as $key => $value) {
	                $response->header($key, $value);
	            }
	        }
	        unset($headers);
	    }

        return $response;
	}
}