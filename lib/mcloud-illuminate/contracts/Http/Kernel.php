<?php

namespace MediaCloud\Vendor\Illuminate\Contracts\Http;

interface Kernel
{
    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap();

    /**
     * Handle an incoming HTTP request.
     *
     * @param \MediaCloud\Vendor\Symfony\Component\HttpFoundation\Request  $request
     * @return \MediaCloud\Vendor\Symfony\Component\HttpFoundation\Response
     */
    public function handle($request);

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param \MediaCloud\Vendor\Symfony\Component\HttpFoundation\Request  $request
     * @param \MediaCloud\Vendor\Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    public function terminate($request, $response);

    /**
     * Get the Laravel application instance.
     *
     * @return \MediaCloud\Vendor\Illuminate\Contracts\Foundation\Application
     */
    public function getApplication();
}
