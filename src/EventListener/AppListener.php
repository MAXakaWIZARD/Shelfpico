<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class AppListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
        EntityManagerInterface $em,
        ParameterBagInterface $parameterBag
    ) {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
    }

    protected function defineConstants()
    {
        if (defined('BASE_PATH')) {
            return;
        }

        define('IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        define('BASE_PATH', realpath($this->parameterBag->get('kernel.project_dir')));

        define('PRODUCT_IMAGES_PATH', $this->parameterBag->get('paths.product_images'));
        define('PRODUCT_IMAGES_SUBDIR', $this->parameterBag->get('product_images_subdir'));

        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        try {
            $settingsRepo = $this->em->getRepository(Setting::class);

            $settings = $settingsRepo->findAll();
            foreach ($settings as $setting) {
                define('SETTING_' . $setting->getName(), $setting->getTypedValue());
            }
        } catch (Exception) {
            //nothing here
        }
    }

    public function onRequest()
    {
        $this->defineConstants();
    }

    public function onCommand()
    {
        $this->defineConstants();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
            ConsoleEvents::COMMAND => ['onCommand'],
        ];
    }
}
