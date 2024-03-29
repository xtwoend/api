<?php namespace Xtwoend\Api\Facades;

/*
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Facade;

/**
 * @see \Xtwoend\Api\Api
 */
class API extends Facade {

	/**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'api'; }

}
