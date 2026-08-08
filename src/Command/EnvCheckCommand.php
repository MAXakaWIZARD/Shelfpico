<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\EnvChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EnvCheckCommand extends Command
{
    private EnvChecker $envChecker;

    public function __construct(EnvChecker $envChecker)
    {
        parent::__construct();
        $this->envChecker = $envChecker;
    }

    protected function configure()
    {
        $this->setName('app:env-check');
        $this->setDescription('Display current environment variables configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Environment check');

        $envVars = $this->envChecker->getEnvVars();
        $rows = [];
        foreach ($envVars as $key => $value) {
            $rows[] = [$key, $value];
        }

        $io->table(['Variable', 'Value'], $rows);

        $envFile = $_ENV['APP_ENV'] === 'test' ? '.env.test.local' : '.env.local';
        $io->note("Current environment: {$_ENV['APP_ENV']} (loaded from .env and $envFile)");

        return Command::SUCCESS;
    }
}
