<?php

namespace Kadevjo\Fibonacci\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class ModelBuilderCommand extends Command
{
  protected $name = 'fibonacci:build-model';

  protected function getOptions()
  {
    return [
      ['name', null, InputOption::VALUE_OPTIONAL, 'Custom CSV file name', 'model.csv'],
    ];
  }

  public function handle(Filesystem $filesystem)
  {
    if ($this->option('name')!='') {
      $fileName = strpos($this->option('name'),'.csv') ? $this->option('name') : $this->option('name').'.csv';    


      if (file_exists(app_path("Fibonacci/$fileName"))) {
        $this->info("existe");
      } else {
        $this->error('File doesn\'t exist!');
      }      
    } else {
      $this->error('Filename empty!');
    }    
  }
}
