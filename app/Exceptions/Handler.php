<?php

namespace App\Exceptions;

use App\Services\Responder;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'آدرس مورد نظر یافت نشد',
                'code' => 'route_not_found'
            ], 403);
        } else if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'رکورد مورد نظر یافت نشد',
                'code' => 'record_not_found'
            ], 403);
        } else if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return response()->json([
                'message' => 'شما مجاز به استفاده از این قابلیت نیستید',
                'code' => 'access_denied'
            ], 403);
        } else if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'message' => 'تعداد درخواست ها بیش از حد مجاز شده است',
                'code' => 'too_many_attempts'
            ], 403);
        } else {
            return parent::render($request, $exception);
        }
    }
}
