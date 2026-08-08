<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\DbVersion;
use App\Repo\DbVersionsRepo;
use App\Service\DbSync;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DbImportCommand extends AbstractCommand
{
    /**
     * @var bool
     */
    protected $dryRun;

    /**
     * @var DbSync
     */
    private $dbSync;

    public function __construct(
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        DbSync $dbSync
    ) {
        $this->params = $params;
        $this->em = $em;
        $this->validator = $validator;
        $this->dbSync = $dbSync;

        parent::__construct($params, $em, $validator);
    }

    protected function configure()
    {
        $this->setName('app:db-import');
        $this->setDescription('Import DB data');

        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'execute dry run - do not import anything'
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'do not ask any confirmations'
        );

        $this->addOption(
            'skip-purge',
            null,
            InputOption::VALUE_NONE,
            'do not purge db'
        );

        $this->addOption(
            'skip-backup',
            null,
            InputOption::VALUE_NONE,
            'do not create backup'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->dryRun = (bool)$input->getOption('dry-run');
        $force = (bool) $input->getOption('force');
        $skipPurge = (bool) $input->getOption('skip-purge');
        $skipBackup = (bool) $input->getOption('skip-backup');

        $titleSuffix = $this->dryRun ? ' (DRY RUN)' : ($force ? ' (FORCE)' : '');
        $this->io->title('Database import' . $titleSuffix);

        $dumpPath = $this->dbSync->findLatestDump();
        if (!$dumpPath) {
            $this->io->error('Unable to find any database dump');
            return Command::FAILURE;
        }

        $this->io->note('About to import file: ' . $dumpPath);

        /** @var DbVersionsRepo $versionsRepo */
        $versionsRepo = $this->getRepo(DbVersion::class);
        $currentVersion = $versionsRepo->getCurrentVersion();

        $newVersion = $this->dbSync->parseVersion($dumpPath);

        $this->displayVersionInfo($currentVersion, $newVersion);

        $dbName = $this->getParameter('database_name');
        $this->io->caution("Database `$dbName` will be purged.");

        $cmds = [];

        if (!$this->isTestEnv() && !$skipBackup) {
            $backupPath = $this->dbSync->getBackupDumpFilePath();
            $this->io->note('DB backup will be saved to ' . $backupPath . '.gz');

            $cmds[] = $this->dbSync->getExportCmd($backupPath);
            $cmds[] = [
                $this->getParameter('bin.gzip'),
                '-9',
                $backupPath
            ];
        }

        $confirmed = $this->dryRun || $force || $this->io->confirm('<question>Proceed y/N ?</question>', false);
        if (!$confirmed) {
            return Command::SUCCESS;
        }

        if (!$skipPurge) {
            $cmds[] = [
                $this->getParameter('bin.php'),
                'bin/console',
                'doctrine:schema:drop',
                '--force',
                '--env=' . $this->getParameter('kernel.environment'),
            ];
        }

        $cmds[] = [
            $this->getParameter('bin.php'),
            'bin/console',
            'doctrine:schema:update',
            '--force',
            '--env=' . $this->getParameter('kernel.environment'),
        ];
        $cmds[] = $this->dbSync->getImportCmd($dumpPath);

        $execResult = $this->execCommands($cmds);
        if ($execResult !== Command::SUCCESS) {
            return $execResult;
        }

        $this->io->note("Database version updated to `$newVersion`");
        $this->io->success('Database import successfully finished!');

        return Command::SUCCESS;
    }

    private function execCommands(array $cmds): int
    {
        foreach ($cmds as $cmd) {
            $process = is_array($cmd)
                ? new Process($cmd, BASE_PATH)
                : Process::fromShellCommandline($cmd, BASE_PATH);

            $this->io->section($process->getCommandLine());
            if ($this->dryRun) {
                continue;
            }

            $process->run();

            $procOutput = $process->getOutput();
            if ($procOutput) {
                $this->io->write($procOutput);
            }

            $procErrorOutput = $process->getErrorOutput();
            if ($procErrorOutput) {
                $this->io->caution($procErrorOutput);
            }

            if (!$process->isSuccessful()) {
                $this->io->error(sprintf(
                    'Execution failed: [%d] (%s)',
                    $process->getExitCode(),
                    $process->getExitCodeText()
                ));
                return 1;
            }
        }

        return 0;
    }

    private function displayVersionInfo(string $currentVersion, string $newVersion)
    {
        $this->io->note("DB version `$currentVersion` => `$newVersion`");
        if ($currentVersion > $newVersion) {
            $this->io->caution("New version is older than current DB version!");
        } elseif ($currentVersion === $newVersion) {
            $this->io->caution("Current DB version is up to date!");
        }
    }
}
