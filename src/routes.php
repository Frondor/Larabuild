<?php

/**
 * This route action should behave depending on hosting driver
 */

Route::get('larabuild/install', 'Frondor\Larabuild\Controllers\InstallationController@install');