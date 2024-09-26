<?php

namespace App\Controller;

use App\Entity\Person;
use App\Form\PersonFilterType;
use App\Form\PersonType;
use App\Repository\FlowRepository;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/person')]
class PersonController extends AbstractController
{
    #[Route('/', name: 'app_person_index', methods: ['GET'])]
    public function index(PersonRepository $personRepository, Request $request): Response
    {
        $form = $this->createForm(PersonFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $qb = $personRepository->createQueryBuilder('p');

            if (!$data['preferences']->isEmpty()) {
                $userPrefs = [];
                foreach ($data['preferences'] as $preference) {
                    $userPrefs[] = $preference->getId();
                }

                $qb
                    ->join('p.preferences', 'u', 'WITH', null, 'u.id')
                    ->andWhere('u.id in (:prefs)')
                    ->setParameter('prefs', $userPrefs)
                ;
            }

            if (!empty($data['name'])) {
                $qb->andWhere("p.firstName like :name or p.lastName like :name or p.surname like :name  ")
                ->setParameter("name", "%".$data['name']."%")
                ;
            }

            if (!empty($data['sex'])) {
                $qb->andWhere("p.sex = :sex")
                ->setParameter("sex", $data['sex'])
                ;
            }

            if (!empty($data['country'])) {
                $qb->andWhere("p.country = :country")
                ->setParameter("country", $data['country'])
                ;
            }

            // if (!empty($data['language'])) {

            //         $qb->andWhere(":language member of p.language")
            //         ->setParameter("language", $data['language'])
            //     ;
                
            // }

            // if (!empty($data['category'])) {
            //     $qb->andWhere("p.category in :category")
            //     ->setParameter("category", $data['category'])
            //     ;
            // }

            $persons = $qb->getQuery()->getResult();
        } 
        else {
            $persons = $personRepository->findAll();
        }

        return $this->render('person/index.html.twig', [
            'form' => $form,
            'people' => $persons,
        ]);
    }

    #[Route('/new', name: 'app_person_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PersonRepository $personRepository): Response
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            
            $personRepository->save($person, true);

            return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('person/new.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_person_show', methods: ['GET'])]
    public function show(Person $person, FlowRepository $flowRepository): Response
    {


        return $this->render('person/show.html.twig', [
            'person' => $person,
            'gived' => $flowRepository->getGiftsGived($person),
            'received' => $flowRepository->getGiftsReceived($person)
        ]);
    }

    #[Route('/{id}/edit', name: 'app_person_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, PersonRepository $personRepository): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $personRepository->save($person, true);
            

            return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_person_delete', methods: ['POST'])]
    public function delete(Request $request, Person $person, PersonRepository $personRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->request->get('_token'))) {
            $personRepository->remove($person, true);
        }

        return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
    }
}
