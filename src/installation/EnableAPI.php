<?php

namespace Kadevjo\Fibonacci\Installation;

use Kadevjo\Fibonacci\Models\ApiConfig;

class EnableAPI{
  public static function enable($table){
    $defaultAPI = '{
      "browse": {"enable": true,"secure": true},
      "read": {"enable": true,"secure": true}, 
      "edit": {"enable": true,"secure": true}, 
      "add": {"enable": true,"secure": true}, 
      "delete": {"enable": true,"secure": true} 
    }';

    $newApi = new ApiConfig;
    $newApi->config = $defaultAPI;
    $newApi->table_name = $table;

    if ($newApi->save()) {
      $response = 'API created.';
    } else {
      $response = 'Error creating API.';
    }
    
    return $response;
  }
}
