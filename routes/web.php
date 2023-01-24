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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [PagesController::class, 'showPage'])->defaults('section', 'homepage')->defaults('page', false)->defaults('subpage', false)->name('home');

Route::post('/submit-contact-form', [SubmitController::class, 'submitContactForm']);

// Route::get('/media/{year}/{month}/{file}', [ImageController::class, 'renderImage']);

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
