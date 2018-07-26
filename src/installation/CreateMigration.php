<?php

namespace Kadevjo\Fibonacci\Installation;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CreateMigration{
  private static $properties = [
    'name' => "\$table->string('##replace_name##', 191);",
    'document' => "\$table->text('##replace_name##');",
    'image' => "\$table->text('##replace_name##');",
    'file' => "\$table->text('##replace_name##');",
    'date' => "\$table->date('##replace_name##');",
    'time' => "\$table->time('##replace_name##');",
    'datetime' => "\$table->dateTime('##replace_name##');",
    'money' => "\$table->decimal('##replace_name##', 10, 2);",
    'email'   => "\$table->string('##replace_name##', 191)->unique();",
    'phone' => "\$table->string('##replace_name##', 191);",
    'url' => "\$table->longText('##replace_name##');",
    'address' => "\$table->longText('##replace_name##');",
    'location' => "\$table->text('##replace_name##');",
    'description' => "\$table->text('##replace_name##');",
    'number' => "\$table->integer('##replace_name##');",
  ];

  public static function build($name, $fields){
    if (!File::exists('database/migrations/fibonacci')) { 
      File::makeDirectory('database/migrations/fibonacci');
    }

    Artisan::call('make:migration', [
      'name' => 'create_'. $name .'_table',
      '--path' => 'database/migrations/fibonacci',
    ]);
    
    $createdName = trim(explode(':', Artisan::output())[1]);

    $migrationString = '';
    foreach ($fields as $key => $value) {      
      $currentField = isset(static::$properties[$value[0]]) ? static::$properties[$value[0]] : null;
      if ($currentField) { 
        $currentField = str_replace('##replace_name##', $key, $currentField);
        if (!in_array('required', $value[1])) {
          $currentField = str_replace(';', '->nullable();', $currentField);
        }
        $migrationString .= "\n\t\t\t$currentField";
      }      
    }

    $str = file_get_contents(database_path("migrations/fibonacci/$createdName.php"));
    $str = str_replace('$table->timestamps();', "$migrationString \n\t\t\t\$table->timestamps();", $str);
    file_put_contents(database_path("migrations/fibonacci/$createdName.php"), $str);
  }
}
