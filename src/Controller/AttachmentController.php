<?php

namespace App\Controller;

use App\Entity\Attachment;
use App\Form\AttachmentType;
use App\Repository\AttachmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/attachment')]
class AttachmentController extends AbstractController
{
    #[Route('/', name: 'app_attachment_index', methods: ['GET'])]
    public function index(AttachmentRepository $attachmentRepository): Response
    {
        return $this->render('attachment/index.html.twig', [
            'attachments' => $attachmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_attachment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AttachmentRepository $attachmentRepository): Response
    {
        $attachment = new Attachment();
        $form = $this->createForm(AttachmentType::class, $attachment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attachmentRepository->save($attachment, true);

            return $this->redirectToRoute('app_attachment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('attachment/new.html.twig', [
            'attachment' => $attachment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_attachment_show', methods: ['GET'])]
    public function show(Attachment $attachment): Response
    {
        return $this->render('attachment/show.html.twig', [
            'attachment' => $attachment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_attachment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Attachment $attachment, AttachmentRepository $attachmentRepository): Response
    {
        $form = $this->createForm(AttachmentType::class, $attachment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attachmentRepository->save($attachment, true);

            return $this->redirectToRoute('app_attachment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('attachment/edit.html.twig', [
            'attachment' => $attachment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_attachment_delete', methods: ['POST'])]
    public function delete(Request $request, Attachment $attachment, AttachmentRepository $attachmentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$attachment->getId(), $request->request->get('_token'))) {
            $attachmentRepository->remove($attachment, true);
        }

        return $this->redirectToRoute('app_attachment_index', [], Response::HTTP_SEE_OTHER);
    }
}
