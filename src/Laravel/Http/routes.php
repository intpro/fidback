<?php

Route::group(['middleware' => 'auth', 'prefix' => 'adm'], function()
{
    Route::get('/fidback/getform/{type}', ['as' => 'fidback_get',  'uses' => 'Interpro\Fidback\Laravel\Http\FidbackController@getForm']);
    Route::post('/fidback/sendform',      ['as' => 'fidback_post', 'uses' => 'Interpro\Fidback\Laravel\Http\FidbackController@sendMessage']);
});
