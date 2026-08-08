<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    public function __construct(
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ) {
        $this->params = $params;
        $this->em = $em;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function getRepo(string $name): ObjectRepository
    {
        return $this->em->getRepository($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        set_time_limit(0);

        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        return Command::SUCCESS;
    }

    protected function isTestEnv(): bool
    {
        return $this->getParameter('kernel.environment') === 'test';
    }

    /**
     * @return mixed
     */
    protected function getParameter(string $name)
    {
        return $this->params->get($name);
    }

    protected function failWithError(string $message, int $returnCode = 1): int
    {
        $this->io->error($message);

        return $returnCode;
    }
}
