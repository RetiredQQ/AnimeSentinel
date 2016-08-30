<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Auth::routes();

// Welcome Page
Route::get('/', 'AnimeController@home');

// Information Pages
Route::get('/about', 'PagesController@about');
Route::get('/news', 'PagesController@news'); //TODO

// Streamers Pages
Route::get('/streamers', 'StreamersController@list'); //TODO
Route::get('/streamers/{streamer}', 'StreamersController@details'); //TODO

// Anime Listings
Route::get('/anime', 'AnimeController@list');
Route::get('/anime/recent', 'AnimeController@recent');
Route::get('/anime/recent/list', 'AnimeController@recentList');
Route::get('/anime/recent/grid', 'AnimeController@recentGrid');
Route::get('/anime/search', 'AnimeController@search');

// Anime Details
Route::get('/anime/{show}/{title?}', 'ShowController@details');

// Show Modifications
Route::post('/anime/add', 'ShowController@insert');

// Stream Pages
Route::get('/anime/{show}/{title}/{translation_type}/episode-{episode_num}', 'EpisodeController@gotoEpisode');
Route::get('/anime/{show}/{title}/{translation_type}/episode-{episode_num}/{streamer}/{mirror}', 'EpisodeController@episode');
Route::get('/stream/{video}/video', 'EpisodeController@static');

// Profile Pages (TODO)