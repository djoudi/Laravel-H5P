<?php

/*
 *
 * @Project        Expression project.displayName is undefined on line 5, column 35 in Templates/Licenses/license-default.txt.
 * @Copyright      Djoudi
 * @Created        2017-02-21
 * @Filename       H5PExceptionHandler.php
 * @Description
 *
 */

namespace Djoudi\LaravelH5p\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class H5PExceptionHandler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     *
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param  $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        switch ($e) {

        case $e instanceof ModelNotFoundException:

            return $this->renderException($e);
            break;

        case $e instanceof H5PException:

            return $this->renderException($e);
            break;

        default:

            return parent::render($request, $e);
        }
    }

    protected function renderException($e)
    {
        switch ($e) {

        case $e instanceof ModelNotFoundException:
            return response()->view('errors.404', [], 404);
            break;

        case $e instanceof H5PException:
            return response()->view('errors.friendly');
            break;
        default:
            return (new SymfonyDisplayer(config('app.debug')))
                ->createResponse($e);
        }
    }
}
