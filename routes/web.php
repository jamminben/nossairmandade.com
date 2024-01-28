<?php
Route::get('/goodbye', 'StaticPageController@goodbye');
Route::post('/goodbye', 'StaticPageController@handleGoodbye');
/*
Route::get('/dev', function() { return view('dev'); });
Route::get('/canabia', function() { return view('canabia'); });
*/
/**
 * Static pages
 */

if(app()->environment('local')) {
    Route::get('/secretlogin/{user_id}', function($user_id) {
        Auth::loginUsingId($user_id);
        return redirect()->route('home');
    });
}
 

Route::get('/', 'StaticPageController@index')->name('home');
Route::get('/index.php', 'StaticPageController@index');
Route::get('/about', 'StaticPageController@about');
Route::get('/about.php', 'StaticPageController@about');
Route::get('/whats-new', 'StaticPageController@whatsNew');
Route::get('/contact', 'StaticPageController@contact');
Route::get('/contact.php', 'StaticPageController@contact');
Route::post('/contact', 'StaticPageController@handleContact');
Route::get('/books', 'StaticPageController@books');
Route::get('/forest.php', 'StaticPageController@books');
//Route::get('/portuguese/vocabulary/{letter?}', 'StaticPageController@vocabulary');
//Route::get('/vocabulary.php', 'StaticPageController@vocabulary');
Route::get('/portuguese/pronunciation', 'StaticPageController@pronunciation');
Route::get('/pronunciation.php', 'StaticPageController@pronunciation');
Route::get('/musicians', 'StaticPageController@musicians');
Route::get('/friends', 'StaticPageController@friends');
Route::get('/links.php', 'StaticPageController@friends');
Route::get('/donate', 'StaticPageController@donate');
Route::get('/search_old', 'SearchController@advanced');
Route::get('/search', 'SearchController@advanced_livewire');
Route::get('/faq', 'StaticPageController@faq');
Route::get('/portuguese/for-beginners', 'StaticPageController@forBeginners');
Route::get('/norms-and-rituals', 'StaticPageController@normsAndRituals');

Route::get('register-complete', 'StaticPageController@registerComplete');
Route::get('password/reset-confirm', 'StaticPageController@registerComplete');

/**
 * Languages
 */
//Route::get('/language/{languageCode}', 'StaticPageController@language');

/**
 * Profile
 */

Route::group(['middleware' => ['auth']], function() {
    Route::get('/profile', 'ProfileController@show');
    Route::put('/profile', 'ProfileController@update');
});

/**
 * Person
 */

Route::get('/person/{personId}/{personName}', 'PersonController@show');

/**
 * Hinario Lists
 */

Route::get('/hinarios/individual', 'HinarioListController@individual');
Route::get('/hinarios/official_hinarios.php', 'HinarioListController@individual');
Route::get('/hinarios/other_hinarios.php', 'HinarioListController@individual');
Route::get('/hinarios/compilations', 'HinarioListController@compilations');
Route::get('/hinarios/compilation_hinarios.php', 'HinarioListController@compilations');
Route::get('/hinarios/local', 'HinarioListController@local');
Route::get('/hinarios/local_hinarios.php', 'HinarioListController@local');

Route::group(['middleware' => ['auth']], function() {
    Route::get('/hinarios/personal', 'HinarioListController@personal');
    Route::get('/favorites.php', 'HinarioListController@favorites');

    // Ajax
    Route::get('/submit-feedback', 'AjaxController@submitFeedback');

    Route::get('/submit-upvote', 'AjaxController@submitUpVote');
    Route::get('/submit-downvote', 'AjaxController@submitDownVote');

    Route::get('/add-hymn-to-user-hinario', 'AjaxController@addHymnToUserHinario');

    Route::get('/delete-personal-hinario/{hinarioId}', 'UserHinarioController@deleteUserHinario');
    Route::post('/create-personal-hinario/', 'UserHinarioController@createUserHinario');
    Route::get('/edit-personal-hinario/{hinarioId}', 'UserHinarioController@editUserHinario');
    Route::post('/edit-personal-hinario/{hinarioId}', 'UserHinarioController@saveUserHinario');
});

/**
 * Hinarios
 */

Route::get('/test/stanza1', 'HinarioController@testStanza1');
Route::get('/test/stanza2', 'HinarioController@testStanza2');
Route::get('/test/stanza3', 'HinarioController@testStanza3');
Route::get('/test/stanza4', 'HinarioController@testStanza4');

Route::get('/hinario/{hinarioId}/{hinarioName?}', 'HinarioController@showPreloaded')
    ->name('get-hinario');
Route::get('/hinario.php?hid={hinarioId}', 'HinarioController@show');

Route::get('/hinario/{hinarioId}/{hinarioName?}/pdf', 'HinarioController@showPdf');

Route::get('/raw_hinario.php?hid={hinarioId}', 'HinarioController@plainText');

Route::get('/user-hinario/{code}', 'HinarioController@userHinario');
Route::get('/user-hinario/{code}/pdf', 'UserHinarioController@showPdf');

/**
 * Hymns
 */

Route::get('/hymn/{hymnId}/{hymnName?}', 'HymnController@show');
Route::get('/hymn.php?hid={hymnId}', 'HymnController@show');

Auth::routes(['verify' => true]);

/**
 * Admin
 */

Route::group(['middleware'=>'auth', 'prefix' => 'admin'], function() {
    Route::get('/enter-hymn', 'Admin\HymnController@show')->name('get-enter-hymn');
    Route::post('/enter-hymn', 'Admin\HymnController@save');
    Route::get('/load-hymn', 'Admin\HymnController@load');
    Route::post('/edit-hymn', 'Admin\HymnController@save');
    Route::get('/edit-hymn', 'Admin\HymnController@save')->name('get-edit-hymn');
});
 
Route::group(['middleware' => ['admin'], 'prefix' => 'admin'], function() {


    Route::get('/hinarios', 'Admin\HinarioController@superadminHinario')
        ->name('superadmin-hinario');

    Route::get('/feedback', 'Admin\HymnController@feedback')->name('feedback');

    Route::get('/edit-person', 'Admin\PersonController@show');
    Route::post('/edit-person', 'Admin\PersonController@save');
    Route::get('/get-edit-person', 'Admin\PersonController@save')->name('get-edit-person');

    Route::get('/move-hymn-files', 'Admin\MediaImportController@showMoveHymns');
    Route::post('/move-hymn-files', 'Admin\MediaImportController@moveHymns');
    Route::get('/import-people-audio-files', 'Admin\ImportMediaController@showImportPeopleAudioForm');
    Route::post('/import-people-audio-files', 'Admin\ImportMediaController@importPeople');

    Route::get('/generate-hinario-zip-files', 'Admin\ImportMediaController@showGenerateHinarioRecordingsZipsForm');
    Route::post('/generate-hinario-zip-files', 'Admin\ImportMediaController@generateHinarioRecordingsZips');

    Route::post('/add-media', 'Admin\MediaImportController@addMedia');

    Route::get('/view-pattern/{id?}', 'Admin\PatternController@view');

    Route::get('/edit-musician-files', 'Admin\MusicianResourceController@show');
    Route::post('/edit-musician-files', 'Admin\MusicianResourceController@save');

});

