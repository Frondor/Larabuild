<?php

namespace Frondor\Larabuild\Controllers;

use App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class InstallationController
{
    public function install(Request $request)
    {
    	if (!App::routesAreCached() && config('app.env')  == 'production') {
	    	//Artisan::call('auth:clear-resets');
	    	Artisan::call('storage:link');
	    	Artisan::call('config:cache');
	    	Artisan::call('route:cache');
	    	return redirect('/?well-done');
    	} else {
    		return abort(403);
    	}
    }
}