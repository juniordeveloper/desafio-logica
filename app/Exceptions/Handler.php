<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException as ModelNotFoundException;
use Illuminate\Database\QueryException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        return $this->getJsonResponse($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        return $this->getJsonResponse($exception);
    }

    /**
     * Get the json response for the exception.
     *
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponse(Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        /*
         * Handle validation errors thrown using ValidationException.
         */
        if ($exception instanceof ValidationException) {

            $validationErrors = $exception->validator->errors()->getMessages();
            $allErrors = [];
            foreach($validationErrors as $error) {
                foreach($error as $err) {
                    $allErrors[] = $err;
                }
            }
            $data['errors'] = $allErrors;
            return $this->responseError($data, 400);
        }
        /*
         * Handle database errors thrown using QueryException.
         * Prevent sensitive information from leaking in the error message.
         */
        if ($exception instanceof QueryException) {
            $message = $exception->getMessage();
            $data['errors'] = [$message];
            return $this->responseError($data, 400);
        }    
        
        /*
         * Handle database errors thrown using QueryException.
         * Prevent sensitive information from leaking in the error message.
         */
        if ($exception instanceof ModelNotFoundException) {
            $message = $exception->getMessage();
            $data['errors'] = [$message];
            return $this->responseError($data, 400);
        }

        $statusCode = $this->getStatusCode($exception);
        if (! isset($message) && ! ($message = $exception->getMessage())) {
            $message = sprintf('%d %s', $statusCode, Response::$statusTexts[$statusCode]);
        }
        
        $errors = [
            'errors' => [$message],
            'status_code' => $statusCode,
        ];
        return $this->responseError($errors, $statusCode);
    }

    /**
     * Get the status code from the exception.
     *
     * @param \Exception $exception
     * @return int
     */
    protected function getStatusCode(Throwable $exception)
    {
        $statusCode = $this->isHttpException($exception) ? $exception->getCode() : $exception->getCode();
        return $statusCode == 0 ? 500 : $statusCode;
    }

    protected function responseError($data, $statusCode)
    {
        return response()->json($data, $statusCode, [], JSON_PRETTY_PRINT+JSON_NUMERIC_CHECK);
    }
}
