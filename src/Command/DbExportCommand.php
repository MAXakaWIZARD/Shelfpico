<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\DbVersion;
use App\Repo\DbVersionsRepo;
use App\Service\DbSync;
use App\Utils\FileSystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DbExportCommand extends AbstractCommand
{
    private const FRESH_DUMPS_COUNT = 2;

    /**
     * @var bool
     */
    private $dryRun;

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
        $this->setName('app:db-export');
        $this->setDescription('Export DB data');

        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'execute dry run - do not export anything'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->dryRun = (bool) $input->getOption('dry-run');

        $this->io->title('Database export' . ($this->dryRun ? ' (DRY RUN)' : ''));

        if ($this->isTestEnv() && !$this->dryRun) {
            $this->io->error('Backing up TEST database is forbidden!');
            return Command::FAILURE;
        }

        $newVersion = $this->dbSync->generateNewVersion();

        //bump version here so it will be contained in DB dump
        $versionObj = $this->bumpVersion($newVersion);

        if (
            $this->dumpData($newVersion)
            && $this->dumpStructure($newVersion)
        ) {
            $this->archiveOldDumps();
            $this->deleteOldDumps();

            return Command::SUCCESS;
        }

        $this->rollbackVersion($versionObj);

        return Command::FAILURE;
    }

    protected function dumpData(string $version): bool
    {
        return $this->dumpDb($version);
    }

    protected function dumpStructure(string $version): bool
    {
        return $this->dumpDb($version, true);
    }

    protected function dumpDb(string $version, bool $structureMode = false): bool
    {
        if ($structureMode) {
            $dumpPath = $this->dbSync->getStructureDumpFilePath($version);
            $cmd = $this->dbSync->getStructureExportCmd($dumpPath);
        } else {
            $dumpPath = $this->dbSync->getDataDumpFilePath($version);
            $cmd = $this->dbSync->getExportCmd($dumpPath);
        }

        $dbName = $this->getParameter('database_name');
        $this->io->note("Backing up DB `$dbName`" . ($structureMode ? 'structure' : '') . "...");

        $process = new Process($cmd, BASE_PATH);

        if ($this->dryRun) {
            $this->io->note('About to execute:');
            $this->io->note($process->getCommandLine());
            return true;
        }

        $process->run();

        if (
            $process->isSuccessful()
            && file_exists($dumpPath)
            && filesize($dumpPath) > 0
        ) {
            $procOutput = $process->getOutput();
            if ($procOutput) {
                $this->io->write($procOutput);
            }

            $prettySize = number_format(filesize($dumpPath), 0, '.', ' ');
            $this->io->success('Dump saved at ' . $dumpPath . ' (' . $prettySize . ' bytes)');

            return true;
        } else {
            $this->io->error('Failed to export DB');

            $procOutput = $process->getOutput();
            if ($procOutput) {
                $this->io->write($procOutput);
            }

            $procErrorOutput = $process->getErrorOutput();
            if ($procErrorOutput) {
                $this->io->caution($procErrorOutput);
            }
        }

        return false;
    }

    protected function bumpVersion(string $newVersion): DbVersion
    {
        $version = new DbVersion($newVersion);

        if (!$this->dryRun) {
            $this->em->persist($version);
            $this->em->flush();
        }

        $this->io->note("Database version bumped to `$newVersion`");

        return $version;
    }

    protected function rollbackVersion(DbVersion $version)
    {
        $this->em->remove($version);
        $this->em->flush();

        /** @var DbVersionsRepo $versionsRepo */
        $versionsRepo = $this->getRepo(DbVersion::class);
        $currentVersion = $versionsRepo->getCurrentVersion();

        $this->io->error("DB version rolled back to `$currentVersion`");
    }

    protected function archiveOldDumps()
    {
        if ($this->dryRun) {
            return;
        }

        $dumps = $this->dbSync->findDumps(true);

        if (count($dumps) <= self::FRESH_DUMPS_COUNT) {
            return;
        }

        $oldDumps = array_slice($dumps, self::FRESH_DUMPS_COUNT);
        $archivedCount = 0;
        $gzipBin = $this->getParameter('bin.gzip');
        foreach ($oldDumps as $dumpPath) {
            $cmd = [$gzipBin, '-9', $dumpPath];

            $process = new Process($cmd, BASE_PATH);
            $process->run();
            if ($process->isSuccessful()) {
                $archivedCount++;
            }
        }

        $this->io->note(sprintf(
            'Archived %d of %d old DB dumps',
            $archivedCount,
            count($oldDumps)
        ));
    }

    /**
     * Deletes old dumps, leaves only first dump of each month
     */
    protected function deleteOldDumps()
    {
        if ($this->dryRun) {
            return;
        }

        $dumps = $this->dbSync->findDumps(true, true, 'ASC');

        $deletedCount = 0;
        $currentYearMonth = date('Y-m');
        $prevYearMonth = '';
        $prevDate = '';
        $prevDelDate = '';
        foreach ($dumps as $dumpPath) {
            $date = DbSync::parseDate($dumpPath);
            $yearMonth = DbSync::parseYearMonth($dumpPath);

            if (
                $prevDate
                && $yearMonth != $currentYearMonth
                && $yearMonth == $prevYearMonth
                && ($date > $prevDate || $date == $prevDelDate)
            ) {
                $prevDelDate = $date;
                //$this->io->writeln('Deleting dump: ' . $dumpPath);
                FileSystem::dropFile($dumpPath);
                $deletedCount++;
            }

            $prevDate = $date;
            $prevYearMonth = $yearMonth;
        }

        $this->io->note(sprintf(
            'Deleted %d of %d old DB dumps',
            $deletedCount,
            count($dumps)
        ));
    }
}
