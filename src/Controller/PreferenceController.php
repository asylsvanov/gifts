<?php

namespace App\Controller;

use App\Entity\Preference;
use App\Form\PreferenceType;
use App\Repository\PreferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/preference')]
class PreferenceController extends AbstractController
{
    #[Route('/', name: 'app_preference_index', methods: ['GET'])]
    public function index(PreferenceRepository $preferenceRepository): Response
    {
        return $this->render('preference/index.html.twig', [
            'preferences' => $preferenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_preference_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PreferenceRepository $preferenceRepository): Response
    {
        $preference = new Preference();
        $form = $this->createForm(PreferenceType::class, $preference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $preferenceRepository->save($preference, true);

            return $this->redirectToRoute('app_preference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('preference/new.html.twig', [
            'preference' => $preference,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_preference_show', methods: ['GET'])]
    public function show(Preference $preference): Response
    {
        return $this->render('preference/show.html.twig', [
            'preference' => $preference,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_preference_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Preference $preference, PreferenceRepository $preferenceRepository): Response
    {
        $form = $this->createForm(PreferenceType::class, $preference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $preferenceRepository->save($preference, true);

            return $this->redirectToRoute('app_preference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('preference/edit.html.twig', [
            'preference' => $preference,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_preference_delete', methods: ['POST'])]
    public function delete(Request $request, Preference $preference, PreferenceRepository $preferenceRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$preference->getId(), $request->request->get('_token'))) {
            $preferenceRepository->remove($preference, true);
        }

        return $this->redirectToRoute('app_preference_index', [], Response::HTTP_SEE_OTHER);
    }
}
