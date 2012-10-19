<?

Route::any('(:bundle)', 'larachat::chat@chat');
Route::post('(:bundle)/message', 'larachat::chat@message');
Route::any('(:bundle)/update', 'larachat::chat@getNewMessages');
Route::any('(:bundle)/users', 'larachat::chat@getOnlineUsers');
Route::any('(:bundle)/notification', 'larachat::chat@getNotification');
Route::any('(:bundle)/name', 'larachat::chat@getName');
Route::any('(:bundle)/read', 'larachat::chat@markAsRead');
Route::any('(:bundle)/lastGeneral', 'larachat::chat@lastGeneral');
Route::any('(:bundle)/readFromUntilID', 'larachat::chat@markAsReadFromUntilID');

Route::any('(:bundle)/test', 'larachat::chat@generalUpdate');
Route::any('chat2', 'larachat::chat@test');

?>