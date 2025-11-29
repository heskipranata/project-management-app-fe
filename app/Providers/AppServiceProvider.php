<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $codes = config('api_response.codes');

        Response::macro('success', function ($data = null, $meta = []) use ($codes) {
            $payload = ['data' => $data];
            if (!empty($meta)) {
                $payload['meta'] = $meta;
            }
            return Response::json($payload, $codes['ok']);
        });

        Response::macro('created', function ($data = null) use ($codes) {
            return Response::json(['data' => $data], $codes['created']);
        });

        Response::macro('noContent', function () use ($codes) {
            return Response::make('', $codes['no_content']);
        });

        Response::macro('badRequest', function ($errors = null) use ($codes) {
            return Response::json(['errors' => $errors], $codes['bad_request']);
        });

        Response::macro('unauthorized', function ($message = null) use ($codes) {
            return Response::json(['message' => $message ?? config('api_response.messages.unauthorized')], $codes['unauthorized']);
        });

        Response::macro('forbidden', function ($message = null) use ($codes) {
            return Response::json(['message' => $message ?? config('api_response.messages.forbidden')], $codes['forbidden']);
        });

        Response::macro('notFound', function ($message = null) use ($codes) {
            return Response::json(['message' => $message ?? config('api_response.messages.not_found')], $codes['not_found']);
        });

        Response::macro('serverError', function ($message = null) use ($codes) {
            return Response::json(['message' => $message ?? config('api_response.messages.server_error')], $codes['server_error']);
        });
    }
}
