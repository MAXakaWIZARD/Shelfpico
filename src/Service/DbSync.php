<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Repo\ProductsRepo;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use SplFileInfo;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

class DbSync
{
    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(ParameterBagInterface $parameters, EntityManagerInterface $em)
    {
        $this->params = $parameters;
        $this->em = $em;
    }

    public function getExportCmd(
        string $dumpPath,
        bool $onlyStructure = false
    ): array {
        $processUser = IS_WINDOWS ? 'win' : posix_getpwuid(posix_geteuid())['name'];
        $logPath = $this->params->get('kernel.logs_dir') . '/mysqldump_' . $processUser . '.log';

        $cmd = [
            $this->params->get('bin.mysqldump'),
            '--user=' . $this->params->get('database_user'),
            '--password=' . $this->params->get('database_password'),
            $this->params->get('database_name'),
            '--skip-comments',
            '--skip-add-drop-table',
            '--net_buffer_length=4096',
            '--complete-insert',
            '--log-error=' . $logPath,
            '--verbose',
            //'--debug-check',
            //'--debug-info',
        ];

        if ($onlyStructure) {
            $cmd[] = '--no-data';
        } else {
            $cmd[] = '--no-create-info';
        }

        $cmd[] = '-r';
        $cmd[] = $dumpPath;

        return $cmd;
    }

    public function getStructureExportCmd(string $dumpPath): array
    {
        return $this->getExportCmd($dumpPath, true);
    }

    public function getImportCmd(string $dumpPath): string
    {
        return sprintf(
            '"%s" --user=%s --password="%s" %s < "%s"',
            $this->params->get('bin.mysql'),
            $this->params->get('database_user'),
            $this->params->get('database_password'),
            $this->params->get('database_name'),
            $dumpPath
        );
    }

    private function getDumpFilePath(
        bool $isBackup = false,
        bool $isStructure = false,
        string $version = ''
    ): string {
        $dir = $isBackup ? $this->params->get('paths.db_backups') : $this->params->get('paths.db_dumps');
        $version = $version ?: $this->generateNewVersion();
        $suffix = $isStructure ? '_structure' : '';

        return $dir . '/' . $this->params->get('database_name') . '_' . $version . $suffix . '.sql';
    }

    public function generateNewVersion(): string
    {
        $suffix = '_' . (IS_WINDOWS ? 'Win' : 'Mac');

        return date('Y-m-d_H_i_s') . $suffix;
    }

    public function getStructureDumpFilePath(string $version = ''): string
    {
        return $this->getDumpFilePath(false, true, $version);
    }

    public function getDataDumpFilePath(string $version = ''): string
    {
        return $this->getDumpFilePath(false, false, $version);
    }

    public function getBackupDumpFilePath(): string
    {
        return $this->getDumpFilePath(true);
    }

    /**
     * Returns DB dumps list, most recent come first
     */
    public function findDumps(
        bool $includeStructure = false,
        bool $onlyArchives = false,
        string $sortDir = 'DESC'
    ): array {
        $dumps = [];

        $path = $this->params->get('paths.db_dumps');
        if (!is_dir($path)) {
            throw new InvalidArgumentException("DB dumps dir does not exist: '$path'");
        }

        $finder = new Finder();
        $finder
            ->files()
            ->in($path)
            ->name('*.sql' . ($onlyArchives ? '.gz' : ''));

        if (!$includeStructure) {
            $finder->notName('*_structure.sql');
            $finder->notName('*_structure.sql.gz');
        }

        $finder
            ->depth(0)
            ->sort(function (SplFileInfo $a, SplFileInfo $b) use ($sortDir) {
                return $sortDir === 'DESC'
                    ? $b->getRealPath() <=> $a->getRealPath()
                    : $a->getRealPath() <=> $b->getRealPath();
            });
        foreach ($finder as $item) {
            /** @var SplFileInfo $item */
            $dumps[] = $item->getRealPath();
        }

        return $dumps;
    }

    public function findLatestDump(): string
    {
        $dumps = $this->findDumps();
        if (count($dumps) == 0) {
            throw new Exception('Unable to find any DB dumps');
        }

        return $dumps[0];
    }

    public function getLatestDbVersion(): string
    {
        $dumpPath = $this->findLatestDump();
        if (!$dumpPath) {
            return '';
        }

        return $this->parseVersion($dumpPath);
    }

    /**
     * Parse DB version from dump file path
     */
    public static function parseVersion(string $dumpPath): string
    {
        $dumpPath = str_replace('_structure', '', $dumpPath);
        $matches = [];
        if (preg_match('/^.*[a-z]+_([0-9]{4}-.*)\.sql.*$/', $dumpPath, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Parse DB year and month from dump file path
     */
    public static function parseDate(string $dumpPath): string
    {
        $version = self::parseVersion($dumpPath);
        $matches = [];
        if (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})/', $version, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Parse DB year and month from dump file path
     */
    public static function parseYearMonth(string $dumpPath): string
    {
        $version = self::parseDate($dumpPath);
        $matches = [];
        if (preg_match('/([0-9]{4}-[0-9]{2})/', $version, $matches)) {
            return $matches[1];
        }

        return '';
    }

    public function exportStocks()
    {
        /** @var ProductsRepo $repo */
        $repo = $this->em->getRepository(Product::class);
        $products = $repo->findBy([], ['title' => 'ASC']);

        $file = fopen($this->params->get('paths.stocks_export') . '/stocks.txt', 'w');
        $output = new StreamOutput($file);

        $table = new Table($output);
        $table->setHeaders(['SKU', 'Product', 'Qty', 'Buy', 'Lst', 'Sal']);

        $counter = 0;
        foreach ($products as $product) {
            /** @var Product $product */

            $counter++;

            if ($counter !== 1) {
                $table->addRow(new TableSeparator());
            }

            $table->addRow([
                $product->getSku(),
                $product->getTitle(),
                (int) $product->getQuantity(),
                (int) $product->getBuyPriceUah(),
                (int) $product->getLastBuyPriceUah(),
                (int) $product->getSalePriceUah(),
            ]);
        }

        $table->render();
    }
}
