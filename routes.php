<?

// New AJAX handlers, changing to POST-only and testing
Route::post('(:bundle)/lastGeneral', 'larachat::chat@lastGeneral'); //
Route::post('(:bundle)/readFromUntilID', 'larachat::chat@markAsReadFromUntilID'); //
Route::post('(:bundle)/generalUpdate', 'larachat::chat@generalUpdate'); //
Route::post('(:bundle)/storeChatToCache', 'larachat::chat@storeChat'); //
Route::post('(:bundle)/removeChatFromCache', 'larachat::chat@removeChat'); //
Route::post('(:bundle)/read', 'larachat::chat@markAsRead'); //
Route::any('(:bundle)/lastTen', 'larachat::chat@lastTen'); //

Route::post('(:bundle)/message', 'larachat::chat@message');
Route::any('(:bundle)', 'larachat::chat@chat');


?>