<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        
        // Handle Spatie Permission Exception
        $this->renderable(function (UnauthorizedException $e, $request) {

            // If request is AJAX or expects JSON
            if ($request->ajax() || $request->expectsJson()) {

                return response()->json([
                    'status'  => false,
                    'message' => 'You do not have permission to perform this action.'
                ], 403);
            }else{

                // Normal web request
                abort(403, 'You do not have permission to access this page.');
            }

        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
