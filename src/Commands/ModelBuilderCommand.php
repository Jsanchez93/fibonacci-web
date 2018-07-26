<?php

namespace Kadevjo\Fibonacci\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Kadevjo\Fibonacci\Installation\CreateMigration;
use Kadevjo\Fibonacci\Installation\CreateBREAD;
use Kadevjo\Fibonacci\Installation\EnableAPI;
use Illuminate\Support\Facades\Artisan;

class ModelBuilderCommand extends Command
{
  protected $name = 'fibonacci:build-model';
  
  protected function getOptions()
  {
    return [
      ['name', null, InputOption::VALUE_OPTIONAL, 'Custom CSV file name', 'model.xlsx'],
    ];
  }

  public function handle(Filesystem $filesystem)
  {
    if ($this->option('name')!='') {
      $fileName = strpos($this->option('name'),'.xlsx') ? $this->option('name') : $this->option('name').'.xlsx';
      if (file_exists(app_path("Fibonacci/$fileName"))) {
        
        $helper = new Sample();
        $inputFileName = app_path("Fibonacci/$fileName");
        $helper->log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . ' using IOFactory to identify the format');
        $spreadsheet = IOFactory::load($inputFileName);        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        $tables = $this->OrganizeData($sheetData);

        foreach ($tables as $name => $info) {
          if (in_array('migrate', $info['options'])) {
            $this->info('Creating migration of '. $name .' table');
            CreateMigration::build($name, $info['fields']);
          } 
        }

        $this->info('Running migrations...');
        Artisan::call('migrate', [
          '--path' => 'database/migrations/fibonacci',
        ]);

        foreach ($tables as $name => $info) {
          if (in_array('bread', $info['options'])) {
            $this->info('Creating BREAD of '. $name .' table.');
            CreateBREAD::build($name, $info['fields']);
          }

          if (in_array('api', $info['options'])) {
            $this->info('Enabling API of '. $name);
            EnableAPI::enable($name);
          }
        }


      } else {
        $this->error('File doesn\'t exist!');
        $this->error("Make sure that the $fileName file exists in app\Fibonacci");
      }      
    } else {
      $this->error('Filename empty!');
    }    
  }

  private function OrganizeData($sheetData) {
    $tables = array();
    $currentTable = '';
    foreach ($sheetData as $value) {
      if ($value['A'] === 'init table') {
        $currentTable = $value['B'];
        $tables[$currentTable] = [
          'options' => explode(',',$value['C']),
        ];
      } else if ($value['A']) {
        $tables[$currentTable]['fields'][$value['A']] = [
          $value['B'], 
          explode(',',$value['C']),
        ];
      }
    }

    return $tables;
  }
}
