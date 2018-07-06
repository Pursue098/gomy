<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


/**
 * @SWG\Swagger(
 *   basePath="/",
 *   @SWG\Info(
 *     title="Cyrano api swagger",
 *     version="1.0.0"
 *   )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function invoke(\App\Channel $channel, $method, $parameters = []) {
        if (! preg_match('/App\\\Http\\\Controllers\\\([a-zA-Z]*)Controller/', get_called_class(), $name)) {
            abort(404);
        }

        $controller = app("App\\Http\\Controllers\\Channels\\{$this->channels[$channel->type]}\\{$name[1]}");

        if (! in_array($method, get_class_methods($controller))) {
            abort(404);
        }

        return call_user_func_array([$controller, $method], $parameters);
    }
}
