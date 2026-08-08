<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TagsController extends AbstractController
{
    /**
     * @var Tag
     */
    protected $tagEntity;

    #[Route("/tags", name: "tags")]
    public function indexAction(): Response
    {
        return $this->render('tags/index.twig');
    }

    #[Route("/tags/{id}", name: "tags.edit", requirements: ["id" => "[0-9]+"])]
    #[Route("/tags/add", name: "tags.add")]
    public function editAction(?int $id): Response
    {
        if ($this->tagEntity) {
            $tag = $this->tagEntity;
        } elseif ($id) {
            $tag = $this->findOr404(Tag::class, $id);
        } else {
            $tag = new Tag();
        }

        $form = $this->createForm(TagForm::class, $tag);

        return $this->render('tags/edit.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/tags/save", name: "tags.save", methods: ["POST"])]
    public function saveAction(Request $request): Response
    {
        $id = intval($request->request->get('id'));
        $isEdit = $id > 0;
        $tag = $isEdit ? $this->findOr404(Tag::class, $id) : new Tag();

        $form = $this->createForm(TagForm::class, $tag);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $tag = $form->getData();

            if (!$isEdit) {
                $this->em->persist($tag);
            }

            $this->em->flush();

            $this->addFlash('success', $isEdit ? 'Saved' : 'Created');

            if ($isEdit) {
                return $this->redirectToRoute('tags.edit', ['id' => $tag->getId()]);
            } else {
                return $this->redirectToRoute('tags.add', ['prevCreatedId' => $tag->getId()]);
            }
        } else {
            $this->addFlash('error', $this->getFormErrorsAsString($form));
            $this->tagEntity = $tag;

            return $this->editAction($tag->getId());
        }
    }
}
