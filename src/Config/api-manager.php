<?php

return [
    /*
    |--------------------------------------------------------------------------
    | api activate
    |--------------------------------------------------------------------------
    | you can active or inactive your api with change the value to true or false
    */
    
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | api namespace
    |--------------------------------------------------------------------------
    | namespace for your api location, we recomend use App\Http\Api
    */
    
    'namespace' => 'App\Http\Api',

    /*
    |--------------------------------------------------------------------------
    | api prefix
    |--------------------------------------------------------------------------
    | prefix url for your api, example http://yourdomain.com/{prefix}/
    */

    'prefix' => 'api',

    /*
    |--------------------------------------------------------------------------
    | middleware
    |--------------------------------------------------------------------------
    | define global api middleware for all versions, the value can be string
    | or array for multiple middleware assinged
    */

    'middleware' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Versioning of api
    |--------------------------------------------------------------------------
    | here you can enable disable your api version
    |
    | for example you api folder structure look like this:
    |
    | App/Http
    | |_ Api
    |    |_ v1 <-- your first version name
    |       |_ Controllers
    |       |_ Requests
    |       |_ route.php
    |    |_ v2 <-- your second version name
    |       |...
    |
    | your version setting may as following:
    |
    | where key of array versions is name of version's folder
    | option
    | - enabled boolean OPTIONAL
    | - prefix string OPTIONAL
    | - middleware mixed string or array OPTIONAL
    |
    | 'versions'=>[
    |   'v1'=> ['enabled'=>true, 'prefix'=>'v1', 'middleware'=>'api'],
    |   'v2'=> ['enabled'=>true, 'prefix'=>'v2', 'middleware'=>['api','test']],
    | ]
    |
    | your version prefix will placed in second url prefix
    | http://yourdomain.com/{api-prefix}/{version-prefix}/
    |
    */

    'versions' => [
        //
    ],
    
    /*
    |--------------------------------------------------------------------------
    | default version
    |--------------------------------------------------------------------------
    | version default for access api without version segment
    | default value is null
    */

    'default_version' => null,
];