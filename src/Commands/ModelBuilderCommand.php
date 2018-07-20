<?php

namespace Kadevjo\Fibonacci\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Kadevjo\Fibonacci\Installation\CreateMigration;

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
            $this->info('Completed.');
          } 
        }


      } else {
        $this->error('File doesn\'t exist!');
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
      } else {
        $tables[$currentTable]['fields'][$value['B']] = explode(',',$value['C']);
      }
    }

    return $tables;
  }
}
