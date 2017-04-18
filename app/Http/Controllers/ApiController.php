<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Jobs\SendMixpanelAction;
use Acme\Transformers\Transformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response as IlluminateResponse;


class ApiController extends Controller
{
    /**
     * describes the response status code
     * @var integer 
     * defaults to HTTP_OK
     */
    protected $statusCode = IlluminateResponse::HTTP_OK;
    
    /**
     * array containing all the validation rules
     * @var array
     */
    protected $validation_errors = [];

    protected $request;

    /**
     * [responseNotFound description]
     * @param  string $message [description]
     * @return [type]          [description]
     */
    public function responseNotFound($message="Could not locate the specified resource")
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError($message);
    }

    /**
     * [responseInternalError description]
     * @param  string $message [description]
     * @return [type]          [description]
     */
    public function responseInternalError($message="Internal Error !")
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message);
    }
    /**
     * 
     * @param  array $data    [description]
     * @param  array  $headers [description]
     * @return json          
     */
    public function respond($data, $headers= [])
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }
    public function respondCreated($message="Successfully created!")
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)
                    ->respond([
                        'success'=>[
                            'message' => $message,
                            'status_code' => $this->getStatusCode()
                        ]
            		]);
    }

    /**
     * [respondWithError description]
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    public function respondWithError($message)
    {
        return $this->respond([
                'error'=>[
                    'message' => $message,
                    'status_code' => $this->getStatusCode()
                ]
            ]);
    }

    public function responseValidationError()
    {
    	return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError($this->getValidationErrors());
    }

    /**
     * Gets the value of statusCode.
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the value of statusCode.
     *
     * @param mixed $statusCode the status code
     *
     * @return self
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Gets the value of validation_errors.
     *
     * @return mixed
     */
    public function getValidationErrors()
    {
        return $this->validation_errors;
    }

    /**
     * Sets the value of validation_errors.
     *
     * @param mixed $validation_errors the validation errors
     *
     * @return self
     */
    protected function setValidationErrors($validation_errors)
    {
        $this->validation_errors = $validation_errors;

        return $this;
    }

    /**
     * validates a request object with a given set of rules
     * @param  array   $rules 
     * @return boolean        
     */
    public function isValidated(array $rules)
    {
    	$validator = Validator::make($this->getRequest()->all(), $rules);
        if (!$validator->fails()) {
        	return true;
        }
		$this->setValidationErrors($validator->errors());
    }

    /**
     * Gets the value of request.
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets the value of request.
     *
     * @param mixed $request the request
     *
     * @return self
     */
    protected function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * responses a pagtinated set of data with pagination meta data
     * @param  Paginator $model       array of objects of any model
     * @param  Transformer $transformer Transforms a model in a predefined structure
     * @return json
     */
    public function respondWithPagination(Paginator $model, Transformer $transformer)
    {
        $model_array = $model->toArray();
        return $this->respond([
            'data'=>$transformer->transformCollection(array_pop($model_array)),
            'pagination' => $model_array
        ]);
    }

    public function respondAsTransformattedArray(array $model, Transformer $transformer)
    {
        return $this->respond([
            'data'=>$transformer->transformCollection($model)
        ]);
    }

    public function respondTransformattedModel($model, Transformer $transformer)
    {
        return $this->respond([
            'data' => $transformer->transform($model)
        ]);
    }

    public function responseUnauthorized($message = 'Unauthorized action.')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respond(['message' => $message]);
    }

    public function getPaginated($data, $limit = 10)
    {
        $page = Input::get('page', 1);
        $perPage = $limit;
        $offset = ($page * $perPage) - $perPage;

        $paginated_result = new LengthAwarePaginator(
            array_slice($data, $offset, $perPage), // Only grab the items we need
            count($data), // Total items
            $perPage, // Items per page
            $page, // Current page
            ['path' => $this->request->url(), 'query' => $this->request->query()] // We need this so we can keep all old query parameters from the url
        );

        return $paginated_result;
    }
    
    /**
     * registers a action to MixPanel
     * @param  User    $user       [description]
     * @param  string  $action     [description]
     * @param  array   $properties [description]
     * @param  integer $ip         [description]
     * @return [type]              [description]
     */
    public function trackAction(User $user, $action, array $properties = [], $ip = 0)
    {
        $ip = $this->request->ip();
        $job = (new SendMixpanelAction($user, $action, $properties, $ip))->onConnection('sync');
        dispatch($job);
    }
}