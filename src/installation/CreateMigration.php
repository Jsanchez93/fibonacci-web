<?php

namespace Kadevjo\Fibonacci\Installation;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CreateMigration{
  public static function build($name, $fields){
    if (!File::exists('database/migrations/fibonacci')) { 
      File::makeDirectory('database/migrations/fibonacci');
    }

    $x = Artisan::call('make:migration', [
      'name' => 'create_'. $name .'_table',
      '--path' => 'database/migrations/fibonacci',
    ]);
    
    $createdName = trim(explode(':', Artisan::output())[1]);

    $str = file_get_contents(database_path("migrations/fibonacci/$createdName.php"));
    $str = str_replace('$table->timestamps();', "\$table->timestamps(); \n\t\t\t// test", $str);
    file_put_contents(database_path("migrations/fibonacci/$createdName.php"), $str);    

    dd($createdName);

  }
}
