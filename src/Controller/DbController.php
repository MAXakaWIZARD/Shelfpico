<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DbVersion;
use App\Repo\DbVersionsRepo;
use App\Service\DbSync;
use App\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DbController extends AbstractController
{
    /**
     * @var DbSync
     */
    private $dbSync;

    public function __construct(
        DbSync $dbSync
    ) {
        $this->dbSync = $dbSync;
    }

    #[Route("/db/backup", name: "db.backup")]
    public function dbBackupAction(Request $request): Response
    {
        $isDryRun = (int)$request->query->get('force') == 0;

        $cmd = ['app:db-export'];
        if ($isDryRun) {
            $cmd[] = '-d';
        }

        $process = $this->execConsoleCommand($cmd);

        return $this->render('db/backup.twig', [
            'pageTitle' => 'Backup',
            'process' => $process,
            'dryRun' => $isDryRun,
        ]);
    }

    #[Route("/db/import", name: "db.import")]
    public function dbImportAction(Request $request): Response
    {
        $isDryRun = (int)$request->query->get('force') == 0;

        $cmd = ['app:db-import'];
        $cmd[] = $isDryRun ? '-d' : '-f';

        $process = $this->execConsoleCommand($cmd);

        /** @var DbVersionsRepo $versionsRepo */
        $versionsRepo = $this->getRepo(DbVersion::class);

        return $this->render('db/import.twig', [
            'pageTitle' => 'DB import',
            'process' => $process,
            'dryRun' => $isDryRun,
            'currentVersion' => $versionsRepo->getCurrentVersion(),
            'latestVersion' => $this->dbSync->getLatestDbVersion(),
        ]);
    }

    #[Route("/db/schema-update", name: "db.schema-update")]
    public function dbSchemaUpdateAction(Request $request): Response
    {
        $process = $this->execConsoleCommand(['doctrine:schema:validate']);
        $output = Utils::getHtmlFriendlyProcessOutput($process);

        $isDryRun = (int)$request->query->get('force') == 0;

        $cmd = ['doctrine:schema:update'];
        $cmd[] = $isDryRun ? '--dump-sql' : '--force';

        $process = $this->execConsoleCommand($cmd);

        return $this->render('db/schema-update.twig', [
            'pageTitle' => 'DB schema update',
            'output' => $output . Utils::getHtmlFriendlyProcessOutput($process),
            'process' => $process,
            'dryRun' => $isDryRun,
        ]);
    }
}
