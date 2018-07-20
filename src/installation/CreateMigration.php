<?php

namespace Kadevjo\Fibonacci\Installation;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CreateMigration{
  private static $properties = [
    'dinero'  => "\$table->decimal('##replace_name##', 10, 2);",
    'texto'   => "\$table->string('##replace_name##', 191);",
    'entero'  => "\$table->integer('##replace_name##');",
    'email'   => "\$table->string('##replace_name##', 191);",
  ];

  public static function build($name, $fields){
    if (!File::exists('database/migrations/fibonacci')) { 
      File::makeDirectory('database/migrations/fibonacci');
    }

    $x = Artisan::call('make:migration', [
      'name' => 'create_'. $name .'_table',
      '--path' => 'database/migrations/fibonacci',
    ]);
    
    $createdName = trim(explode(':', Artisan::output())[1]);

    $migrationString = '';
    foreach ($fields as $key => $value) {      
      $currentField = static::$properties[$value[0]];
      $currentField = str_replace('##replace_name##', $key, $currentField);
      if (!in_array('requerido', $value[1])) {
        $currentField = str_replace(';', '->nullable();', $currentField);
      }

      $migrationString .= "\n\t\t\t$currentField";
    }

    $str = file_get_contents(database_path("migrations/fibonacci/$createdName.php"));
    $str = str_replace('$table->timestamps();', "$migrationString \n\t\t\t\$table->timestamps();", $str);
    file_put_contents(database_path("migrations/fibonacci/$createdName.php"), $str);
  }
}
