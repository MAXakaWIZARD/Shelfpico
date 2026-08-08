<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Service\Attribute\Required;

class AbstractController extends BaseController
{
    protected EntityManagerInterface $em;

    #[Required]
    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }

    protected function abort(int $code, string $message = '', array $headers = [])
    {
        throw new HttpException($code, $message, null, $headers);
    }

    protected function getRepo(string $name): ObjectRepository
    {
        return $this->em->getRepository($name);
    }

    protected function getConsoleCmd(array $command): array
    {
        return array_merge(
            [
                $this->getParameter('bin.php'),
                'bin/console',
            ],
            $command,
            [
               '--env=' .  $this->getParameter('kernel.environment'),
            ]
        );
    }

    protected function execConsoleCommand(array $command, ?float $timeout = null): Process
    {
        return $this->execShellCommand(
            $this->getConsoleCmd($command),
            '',
            $timeout
        );
    }

    protected function execShellCommand(
        array $command,
        string $workingDir = '',
        ?float $timeout = null
    ): Process {
        set_time_limit(0);

        if (!$workingDir) {
            $workingDir = BASE_PATH;
        }

        $process = new Process($command, $workingDir, null, null, $timeout);
        $process->run();

        return $process;
    }

    protected function getFormErrorsAsString(FormInterface $form): string
    {
        return (string) $form->getErrors(true, false);
    }

    protected function findOr404(string $className, int $id)
    {
        $entity = $this->em->find($className, $id);
        if (!is_object($entity)) {
            $this->abort(Response::HTTP_NOT_FOUND, "Specified $className does not exist");
        }

        return $entity;
    }
}
