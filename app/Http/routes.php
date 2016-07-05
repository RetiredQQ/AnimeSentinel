<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::auth();

Route::get('/', 'PagesController@home');
Route::get('/about', 'PagesController@about');
Route::get('/news', 'PagesController@news');

Route::get('/anime/recent', 'AnimeController@recent');
Route::get('/anime/search', 'AnimeController@search');

Route::get('/anime/{show}', 'AnimeController@details');
Route::get('/anime/{show}/{anime_type}/episode-{episode_num}', 'AnimeController@episode');

Route::get('/anime/{show}/{anime_type}/episode-{episode_num}/{streamer_id}', 'EpisodeController@stream');