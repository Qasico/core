<?php

namespace Core;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * Current user loggedin.
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $user;

    /**
     * Controller constructor.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->setupUser($auth);
    }
    
    /**
     * Initial data user loggedin.
     *
     * @param Guard $auth
     */
    public function setupUser(Guard $auth)
    {
        if ($auth->check()) {
            $this->user = $auth->user();
            $this->compose('me', $this->user);
        }
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  array $parameters
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function missingMethod($parameters = [])
    {
        return abort(404);
    }

    /**
     * Compose variable into views.
     *
     * @param string|array $key
     * @param string|bool  $value
     * @return void
     */
    public function compose($key, $value = false)
    {
        if (is_string($key)) {
            view()->share($key, $value);
        }

        if (is_array($key)) {
            view()->share($key);
        }
    }

    /**
     * Serve data for layout template blade.
     *
     * @param array $data
     * @return void
     */
    public function layoutData($data = [])
    {
        $this->compose($data);
    }
}