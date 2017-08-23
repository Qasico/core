<?php

namespace Core\Supports;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as BaseRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FormRequest extends BaseRequest
{
    /**
     * Properties of response messages
     *
     * @var string
     */
    public $response_message;

    /**
     * Property of response element
     *
     * @var string
     */
    public $response_element;

    /**
     * @var array
     */
    protected $sanitized;

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $factory = $this->container->make('Illuminate\Validation\Factory');

        if (method_exists($this, 'validator')) {
            return $this->container->call([$this, 'validator'], compact('factory'));
        }

        return $factory->make(
            $this->sanitizeInput(), $this->container->call([$this, 'rules']), $this->messages()
        );
    }

    /**
     * Format the errors from the given Validator instance.
     *
     * @param  Validator $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        if ($message_bag = $validator->errors()->getMessages()) {
            $formated = array_map(function ($i) {
                return $i[0];
            }, $message_bag);

            return $formated;
        }

        return false;
    }

    /**
     * Get the response for a forbidden operation.
     *
     * @return \Illuminate\Http\Response
     */
    public function forbiddenResponse()
    {
        $errors = ['authorize' => 'Forbidden'];

        if ($this->ajax() || $this->wantsJson()) {
            return new JsonResponse($errors, 403);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }

    /**
     * Set custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Sanitize the input.
     *
     * @return array
     */
    protected function sanitizeInput()
    {
        if (method_exists($this, 'sanitize')) {
            return $this->sanitized = $this->container->call([$this, 'sanitize']);
        }

        return $this->all();
    }

    /**
     * Get sanitized input.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function sanitized($key = null, $default = null)
    {
        $input = is_null($this->sanitized) ? $this->all() : $this->sanitized;

        return array_get($input, $key, $default);
    }

    /**
     * Create response formater automatically
     * known if the request is ajax or not
     *
     * @param \Illuminate\Http\Response $response
     * @param bool                      $is_success
     * @return JsonResponse
     */
    public function makeResponse($response, $is_success = false)
    {
        if ($this->response_message != null) {
            $status = ($is_success != false) ? 'success' : 'danger';

            $response->with('message', $this->response_message)
                ->with('status', $status);
        }

        if ($this->ajax()) {
            $header = array(
                'Redirect' => $response->getTargetUrl(),
                'Message'  => $this->response_message,
                'Reload'   => $this->response_element,
            );

            // check if response contains errors
            if ($is_success == false) {
                $response_data   = ($errors = $response->getSession()->pull('errors')) ? $errors->toArray() : ['failed'];
                $response_status = 422;
            } else {
                $response_data   = ['success'];
                $response_status = 200;
            }

            return new JsonResponse($response_data, $response_status, $header);
        }

        return $response;
    }

    /**
     * Check user access control for this request
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    /**
     * Perform upload file into cloud and return the file name.
     *
     * @param string|UploadedFile $name
     * @param string              $filename
     * @param string              $path
     * @param string              $storage
     * @return bool|string
     */
    public function upload($name, $filename = null, $path = null, $storage = 's3')
    {
        if ($file = ($name instanceof UploadedFile) ? $name : (($this->hasFile($name)) ? $this->file($name) : false)) {
            return upload($file, $path, $filename, $storage);
        }

        return false;
    }
}