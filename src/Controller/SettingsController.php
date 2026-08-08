<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Setting;
use App\Form\SettingForm;
use App\Repo\EntityRepo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    /**
     * @var Setting
     */
    protected $entity;

    #[Route("/settings", name: "settings")]
    public function indexAction(): Response
    {
        /** @var EntityRepo $repo */
        $repo = $this->getRepo(Setting::class);

        return $this->render('settings/index.twig', [
            'settings' => $repo->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route("/settings/{id}", name: "settings.edit", requirements: ["id" => "[0-9]+"])]
    #[Route("/settings/add", name: "settings.add")]
    public function editAction(?int $id): Response
    {
        if ($this->entity) {
            $entity = $this->entity;
        } elseif ($id) {
            $entity = $this->findOr404(Setting::class, $id);
        } else {
            $entity = new Setting();
        }

        $form = $this->createForm(SettingForm::class, $entity);

        return $this->render('settings/edit.twig', [
            'setting' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/settings/save", name: "settings.save", methods: ["POST"])]
    public function saveAction(Request $request): Response
    {
        $id = intval($request->request->get('id'));
        $isEdit = $id > 0;
        $entity = $isEdit ? $this->findOr404(Setting::class, $id) : new Setting();

        $form = $this->createForm(SettingForm::class, $entity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            if (!$isEdit) {
                $this->em->persist($entity);
            }

            $this->em->flush();

            $this->addFlash('success', $isEdit ? 'Saved' : 'Created');

            if ($isEdit) {
                return $this->redirectToRoute('settings.edit', ['id' => $entity->getId()]);
            } else {
                return $this->redirectToRoute('settings.add', ['prevCreatedId' => $entity->getId()]);
            }
        } else {
            $this->addFlash('error', $this->getFormErrorsAsString($form));
            $this->entity = $entity;

            return $this->editAction($entity->getId());
        }
    }
}
