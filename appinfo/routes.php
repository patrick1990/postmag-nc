<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Postmag\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	    ['name' => 'page#index',     'url' => '/',                   'verb' => 'GET'],    // return user interface
        ['name' => 'config#getconf', 'url' => '/config',             'verb' => 'GET'],    // get global config
        ['name' => 'config#setconf', 'url' => '/config',             'verb' => 'PUT'],    // set global config
        ['name' => 'user#getinfo',   'url' => '/userinfo',           'verb' => 'GET'],    // get user info
	    ['name' => 'alias#index',    'url' => '/alias',              'verb' => 'GET'],    // return a list of all aliases
        ['name' => 'alias#create',   'url' => '/alias',              'verb' => 'POST'],   // create a new alias
        ['name' => 'alias#read',     'url' => '/alias/{id}',         'verb' => 'GET'],    // get a specified alias
        ['name' => 'alias#update',   'url' => '/alias/{id}',         'verb' => 'PUT'],    // update alias properties
        ['name' => 'alias#delete',   'url' => '/alias/{id}',         'verb' => 'DELETE'], // delete an alias
        ['name' => 'mail#sendtest',  'url' => '/mail/sendtest/{id}', 'verb' => 'PUT']     // send a test mail
    ]
];
