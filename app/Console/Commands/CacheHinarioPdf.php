<?php

namespace App\Console\Commands;

use App\Models\Hinario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputOption;

class CacheHinarioPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hinario:cachepdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and cache a hinario pdf.';

    protected function configure()
    {
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Cache all Hinario models.');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Cache a specific Hinario model by ID.');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('all') && !$this->option('id')) {
            $this->error('Either the --all option or the --id option is required.');
            return Command::FAILURE;
        }

        ini_set('max_execution_time', 0);

        if ($this->option('all')) {
            // Cache all Hinario models.
            foreach (Hinario::all() as $hinario) {
                $hinario->return_pdf_content = 0;
                echo $hinario->getPdf() . "\n";
            }
        } else {
            // Cache a specific Hinario model by ID.
            $hinario = Hinario::findOrFail($this->option('id'));
            $hinario->return_pdf_content = 0;
            echo $hinario->getPdf();
        }

    }
}
