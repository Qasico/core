<?php

namespace Core\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class FormSubmission
{
    /**
     * Prevent multiple submission form.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $cache         = Cache::tags(["mw_submissions"]);
        $submission    = false;
        $url_requested = $request->url();
        $keys          = 1 . $url_requested . '.' . $request->user()->token;

        // run only for production
        if ($request->method() != "GET" && app()->environment() != "local") {
            // cek if already has submission
            if ($used = $cache->get($keys)) {
                $error = "The previous submission is still on progress";
                if ($request->ajax()) {
                    $header = array(
                        'Redirect' => $url_requested,
                        'Message'  => $error,
                    );

                    return new JsonResponse(["failed"], 422, $header);
                }

                return redirect()->to($url_requested)->withErrors(["submission" => "duped"], $error);
            }

            // if not, we save the submission
            $submission = true;
            $cache->put($keys, $request->ip(), Carbon::now()->addSeconds(30));
        }

        $response = $next($request);

        // clear the submission
        if ($submission && $keys) {
            $cache->forget($keys);
        }

        return $response;
    }
}
