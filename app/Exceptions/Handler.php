<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Services\ClientService;

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
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        if (config('services.key_token_telegram')) {
            $link = url()->full();
            if(strlen($link) > 2000){
                $link = substr($link, 0, 1000);
            }

            $url = "https://api.telegram.org/bot".config('services.key_token_telegram')."/sendMessage";

            $message = "Link : ".$link."\n".
            "File : ".$exception->getFile()."\n".
            "Line : ".$exception->getLine()."\n".
            "Code : ".$exception->getCode()."\n".
            "Message : ".mb_substr($exception->getMessage(), 0, 1000);

            $data = [
                "chat_id" => config('services.key_chatid_telegram'),
                "text" => $message,
                "disable_notification" => false
            ];

            (new ClientService)->request('get', $url, 'json', null, $data);
        }

        parent::report($exception);
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
        return parent::render($request, $exception);
    }
}
