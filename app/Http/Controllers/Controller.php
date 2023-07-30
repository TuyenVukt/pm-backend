<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
        /**
     * The response factory implementation.
     *
     * @var ResponseFactory
     */
    protected $response;

    /**
     * Create a new controller instance.
     *
     * @param ResponseFactory $response
     */
    public function __construct(ResponseFactory $response)
    {
        $this->response = $response;
    }

    /**
     * Return a JSON response.
     *
     * @param string $status
     * @param string $message
     * @param int $code_bug
     * @param array $data
     * @param int $status_code
     * @return JsonResponse
     */
    protected function jsonResponse($status, $message, $data = [], $status_code = 200): JsonResponse
    {
        return $this->response->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $status_code);
    }

    // protected function checkPermission(Request $request)
    // protected function checkInsideWorkspace(Resquest $request);
    // protected function checkInsideProject(Resquest $request);
    // protected function getROLE(Resquest $request);
    

}
