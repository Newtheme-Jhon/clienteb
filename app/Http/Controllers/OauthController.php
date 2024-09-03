<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class OauthController extends Controller
{
    
    public function redirect(Request $request){

        $request->session()->put('state', $state = Str::random(40));
 
        $query = http_build_query([
            'client_id' => config('services.clienteb.client_id'),
            'redirect_uri' => route('callback'),
            'response_type' => 'code',
           'scope' => 'read-post create-post update-post delete-post',
            'state' => $state,
            // 'prompt' => '', // "none", "consent", or "login"
        ]);
 
        return redirect('http://api.blog.test/oauth/authorize?'.$query);

    }

    public function callback(Request $request){
        $state = $request->session()->pull('state');
 
        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class,
            'Invalid state value.'
        );
     
        $response = Http::asForm()->post('http://api.blog.test/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.clienteb.client_id'),
            'client_secret' => config('services.clienteb.client_secret'),
            'redirect_uri' => route('callback'),
            'code' => $request->code,
        ]);
     
        return $response->json();
    }
}
