<?php

namespace App\Http\Middleware;

use App\Constants\UserRole;
use App\Models\User;
use App\Traits\ReturnsJsonResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Utils\Utility;
use App\Constants\Response as HttpResponse;

class VerifyAdmin
{
    use ReturnsJsonResponses;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user_id = $request->user_id;
        if (!$user_id) {
            return $this->error_response(
                HttpResponse::ERR_BAD_PARAMS,
                "Malformed Request",
                400
            );
        }
        $user = User::where('id', $user_id)->where('role', UserRole::ADMIN)->first();
        // dd($user);
        if (is_null($user)) {
            return $this->error_response(
                HttpResponse::NOT_AUTHORIZED,
                "Failed",
                401
            );
        }
        return $next($request);
    }
}
