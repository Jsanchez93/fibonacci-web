<?php

namespace Kadevjo\Fibonacci\Installation;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use TCG\Voyager\Facades\Voyager;

class CreateBREAD{
  private static $types = [
    'name' => 'text',
    'document' => 'image',
    'image' => 'image',
    'file' => 'file',
    'date' => 'date',
    'time' => 'time',
    'datetime' => 'timestamp',
    'money' => 'number',
    'email'   => 'text',
    'phone' => 'text',
    'url' => 'text',
    'address' => 'text_area',
    'location' => 'text_area',
    'description' => 'text_area',
    'number' => 'number',
  ];
  
  public static function build($name, $fields){
    $Model = studly_case(str_singular($name));
        
    if (!File::exists("app/$Model.php")) { 
      Artisan::call('make:model', [
        'name' => $Model,
      ]);
    }
    $str = file_get_contents(app_path("$Model.php"));
    $str = str_replace('//', "protected \$table = '$name';", $str);
    file_put_contents(app_path("$Model.php"), $str);
    
    $bread = [
      "name" => $name,
      "display_name_singular" => $name,
      "display_name_plural" => $name,
      "slug" => $name,
      "icon" => null,
      "model_name" => "App\\$Model",
      "controller" => null,
      "policy_name" => null,
      "generate_permissions" => "on",
      "order_column" => null,
      "order_display_column" => null,
      "description" => null,

      "field_required_id" => "1",
      "field_order_id" => "1",
      "field_id" => "id",
      "field_input_type_id" => "text",
      "field_display_name_id" => "Id",
      "field_details_id" => null,
    ];

    $order = 2;
    foreach ($fields as $key => $value) {
      $field = [
        "field_required_$key" => (in_array('required', $value[1])) ? "1" : "0",
        "field_order_$key" => $order,
        "field_browse_$key" => "on",
        "field_read_$key" => "on",
        "field_edit_$key" => "on",
        "field_add_$key" => "on",
        "field_delete_$key" => "on",
        "field_$key" => "$key",
        "field_input_type_$key" => static::$types[$value[0]],
        "field_display_name_$key" => studly_case(str_singular($key)),
        "field_details_$key" => null,
      ];
      $bread = array_merge($bread, $field);
      $order++;
    }
    
    $timestamps = [
      "field_required_created_at" => "0",
      "field_order_created_at" => $order+1,
      "field_browse_created_at" => "on",
      "field_read_created_at" => "on",
      "field_created_at" => "created_at",
      "field_input_type_created_at" => "timestamp",
      "field_display_name_created_at" => "Created At",
      "field_details_created_at" => null,
      
      "field_required_updated_at" => "0",
      "field_order_updated_at" => $order+2,
      "field_updated_at" => "updated_at",
      "field_input_type_updated_at" => "timestamp",
      "field_display_name_updated_at" => "Updated At",
      "field_details_updated_at" => null,
    ];
    $bread = array_merge($bread, $timestamps);
    $response = '';
    try {
      $dataType = Voyager::model('DataType');
      $res = $dataType->updateDataType($bread, true);
      if($res) {
        $response = "$name BREAD added";
      } else {
        $response = 'Could not add BREAD';
      }      
    } catch (Exception $e) {
      $response = 'Error creating BREAD';
    }

    return $response;
  }
}
