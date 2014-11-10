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
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class LimitRequest implements Middleware
{	

	protected $app;


	protected $response;
	/**
	 * 
	 * @params
	 */	
	public function __construct(Application $app, Response $respose)
	{
		$this->app 		= $app;
		$this->response = $respose
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, \Closure $next)
	{
		if ( ! $this->app->make('api')->checkRequestLimit()) {
	        // 400 Bad Request - The request is malformed, such as if the body does not parse
	        return $this->respose->make('Too many request performed',400);
	    }
	    
		return $next($request);
	}
}