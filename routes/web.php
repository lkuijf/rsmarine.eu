<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\SubmitController;
use App\Http\Controllers\ImageController;
// use App\Http\Controllers\AjaxController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*********************/
    // Moved web.config rewrite for media to this route (since Laravel toolkit, and others, for Plesk messes web.config up)
    Route::get('/media/{year}/{month}/{file}', [ImageController::class, 'renderImage'])->where(['year' => '[0-9]{4}','month' => '[0-9]{2}']);
    // Moved web.config rewrite for Carbon Fields -bug to this route (since Laravel toolkit, and others, for Plesk messes web.config up)
    Route::get('/_mcfu638b-cms/wp-json/carbon-fields/v1/attachment', function () {
        return redirect(str_replace('/_mcfu638b-cms/wp-json/carbon-fields/v1/attachment', '/_mcfu638b-cms/index.php/wp-json/carbon-fields/v1/attachment', Request::fullUrl()));
    });
/*********************/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [PagesController::class, 'showPage'])->defaults('section', 'homepage')->defaults('page', false)->defaults('subpage', false)->name('home');
Route::post('/submit-contact-form', [SubmitController::class, 'submitContactForm']);


/* Than check for a page request */
Route::get('/{section}', [PagesController::class, 'showPage'])->defaults('page', false)->defaults('subpage', false)->where([
    'section' => '[a-z0-9_-]+',
]);
Route::get('/{section}/{page}', [PagesController::class, 'showPage'])->defaults('subpage', false)->where([
    'section' => '[a-z0-9_-]+',
    'page' => '[a-z0-9_-]+',
]);
Route::get('/{section}/{page}/{subpage}', [PagesController::class, 'showPage'])->where([
    'section' => '[a-z0-9_-]+',
    'page' => '[a-z0-9_-]+',
    'subpage' => '[a-z0-9_-]+',
]);
