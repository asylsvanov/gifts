<?php

namespace App\Controller;

use App\Entity\Flow;
use App\Form\FlowType;
use App\Form\FlowFilterType;
use App\Entity\Photo;
use App\Entity\Gift;
use App\Form\GiftType;
use App\Repository\PersonRepository;
use App\Entity\Person;
use App\Form\PersonType;
use App\Repository\FlowRepository;
use App\Repository\GiftRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\FlowEditType;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use PhpOffice\PhpWord\TemplateProcessor;


#[Route('/admin/flow')]
class FlowController extends AbstractController
{

    private $translator;
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    #[Route('/received', name: 'app_flow_received', methods: ['GET'])]
    public function received(FlowRepository $flowRepository, Request $request, GiftRepository $giftRepository): Response
    {

        $form = $this->createForm(FlowFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $qb = $flowRepository->createQueryBuilder(alias: 'f')
            ->where('f.isReceived = :isReceived')
            ->setParameter('isReceived', true);


            if (!empty($data['personFrom'])) {
                $qb->andWhere("f.personFrom = :from")
                ->setParameter("from", $data['personFrom'])
                ;
            }

            if (!empty($data['personTo'])) {
                $qb->andWhere("f.personTo = :to")
                ->setParameter("to", $data['personTo'])
                ;
            }

            if (!empty($data['gift'])) {
                $qb->andWhere("f.gift = :gift")
                ->setParameter("gift", $data['gift'])
                ;
            }

            if (!empty($data['country'])) {
                $qb
                ->join('f.personFrom', 't1', 'WITH', null, 't1.id')
                ->join('f.personTo', 't2', 'WITH', null, 't2.id')
                ->andWhere("t1.country = :country or t2.country = :country")
                ->setParameter('country', $data['country'])
            ;
            }

            $flows = $qb
            ->orderBy('f.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();

            if (!empty($flows) && $request->get('actionName') == 'export') {
                return new BinaryFileResponse($this->getDocxFileFromTemplate('template_only_archive', $flows));
            }

        } 
        else {
            $qb = $flowRepository->createQueryBuilder('f')
            ->where('f.isReceived = :isReceived')
            ->setParameter('isReceived', true);

            $flows = $qb
            ->orderBy('f.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();
        }

        return $this->render('flow/received.html.twig', [
            'flows' => $flows,
            'form' => $form
        ]);
    }

    #[Route('/presented', name: 'app_flow_presented', methods: ['GET'])]   
    public function presented(FlowRepository $flowRepository, Request $request, GiftRepository $giftRepository): Response
    {

        $form = $this->createForm(FlowFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $qb = $flowRepository->createQueryBuilder(alias: 'f')
            ->where('f.isReceived = :isReceived')
            ->setParameter('isReceived', false);

            if (!empty($data['personFrom'])) {
                $qb->andWhere("f.personFrom = :from")
                ->setParameter("from", $data['personFrom'])
                ;
            }

            if (!empty($data['personTo'])) {
                $qb->andWhere("f.personTo = :to")
                ->setParameter("to", $data['personTo'])
                ;
            }

            if (!empty($data['gift'])) {
                $qb->andWhere("f.gift = :gift")
                ->setParameter("gift", $data['gift'])
                ;
            }

            if (!empty($data['country'])) {
                $qb
                ->join('f.personFrom', 't1', 'WITH', null, 't1.id')
                ->join('f.personTo', 't2', 'WITH', null, 't2.id')
                ->andWhere("t1.country = :country or t2.country = :country")
                ->setParameter('country', $data['country'])
            ;
            }

            $flows = $qb
            ->orderBy('f.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();

            if (!empty($flows) && $request->get('actionName') == 'export') {
                return new BinaryFileResponse($this->getDocxFileFromTemplate('template_only_archive', $flows));
            }

        } 
        else {
            $qb = $flowRepository->createQueryBuilder('f')
            ->where('f.isReceived = :isReceived')
            ->setParameter('isReceived', false);

            $flows = $qb
            ->orderBy('f.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();
        }

        return $this->render('flow/presented.html.twig', [
            'flows' => $flows,
            'form' => $form
        ]);
    }

    #[Route('/new', name: 'app_flow_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FlowRepository $flowRepository, PersonRepository $personRepository, GiftRepository $giftRepository): Response
    {
        $flow = new Flow();
        $form = $this->createForm(FlowType::class, $flow);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($request->request->get('received') === 'true') {
                $flow->setIsReceived(true);  
            } else {
                $flow->setIsReceived(false); 
            }

            if ($flow->getPersonFrom() == null) {
                $newPerson1 = $flow->getNewPersonFrom();
                $personRepository->save($newPerson1, true);
                $flow->setPersonFrom($newPerson1);
            }

            if ($flow->getPersonTo() == null) {
                $newPerson2 = $flow->getNewPersonTo();
                $personRepository->save($newPerson2, true);
                $flow->setPersonTo($newPerson2);
            }

            if ($flow->getGift() == null) {
                $newGift = $flow->getNewGift();
                $giftRepository->save($newGift, true);
                $flow->setGift($newGift);
            }

            $photos = $request->files->get('photos');
            if ($photos) {
                foreach ($photos as $photo) {
                    $photoEntity = new Photo();
                    $photoEntity->setGift($newGift); 
                    $photoEntity->setImageFile($photo); 
                    $newGift->addPhoto($photoEntity);
                }
            }

            if($flow->getPersonFrom() != null && $flow->getPersonTo() != null && $flow->getGift() != null){
                $giftCounter = $flow->getGift()->getCounter();
                if ($giftCounter > 0)
                    $flow->getGift()->setCounter($giftCounter - 1);
                $flowRepository->save($flow, true);

                if ($flow->getIsReceived()) {
                    return $this->redirectToRoute('app_flow_received', [], Response::HTTP_SEE_OTHER);
                } else {
                    return $this->redirectToRoute('app_flow_presented', [], Response::HTTP_SEE_OTHER);
                }
            }
        }

        return $this->render('flow/new.html.twig', [
            'flow' => $flow,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_flow_show', methods: ['GET'])]
    public function show(Flow $flow): Response
    {
        return $this->render('flow/show.html.twig', [
            'flow' => $flow,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_flow_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Flow $flow, FlowRepository $flowRepository): Response
    {
        $form = $this->createForm(FlowEditType::class, $flow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flowRepository->save($flow, true);

            if ($flow->getIsReceived()) {
                return $this->redirectToRoute('app_flow_received', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_flow_presented', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('flow/edit.html.twig', [
            'flow' => $flow,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_flow_delete', methods: ['POST'])]
    public function delete(Request $request, Flow $flow, FlowRepository $flowRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$flow->getId(), $request->request->get('_token'))) {
            $flowRepository->remove($flow, true);
        }

        return $this->redirectToRoute('app_flow_presented', [], Response::HTTP_SEE_OTHER);
    }

    private function getDocxFileFromTemplate($flow, $template = 'template_only_archive')
    {
            $source = __DIR__ . "/../resources/".$template.".docx";
            $templateProcessor = new TemplateProcessor($source);

            $tz = 'Asia/Almaty';
            $timestamp = time();
            $dt = new \DateTime("now", new \DateTimeZone($tz));
            $dt->setTimestamp($timestamp);

        if ($flow != null) {
            $templateProcessor->cloneRow('rcvd', count($flow));
            $i = 1;
            
            foreach ($flow as $row) {
                $templateProcessor->setValue('rcvd#' . $i, $i);

                if ($row->getPersonFrom()) {
                    $gvdFrom = $row->getPersonFrom()->getFirstName() . ' ' . $row->getPersonFrom()->getLastName();
                } else {
                    $gvdFrom = $row->getImportPersonFrom();
                }

                $templateProcessor->setValue('rcvd.from#' . $i, $gvdFrom);

                if ($row->getPersonTo()) {
                    $gvdTo = $row->getPersonTo()->getFirstName() . ' ' . $row->getPersonTo()->getLastName();
                } else {
                    $gvdTo = $row->getImportPersonTo();
                }

                $templateProcessor->setValue('rcvd.to#' . $i, $gvdTo);

                $templateProcessor->setValue('rcvd.date#' . $i, $row->getReceivedAt()->format('d.m.Y'));
                $templateProcessor->setValue('rcvd.desc#' . $i, htmlspecialchars($row->getGift()->getTitle(), ENT_XML1, 'UTF-8'));

                if (!$row->getGift()->getPhotos()->isEmpty() && $row->getGift()->getPhotos()->first()->getImageName() != '') {
                    $path = __DIR__ . "/../../public/photos/" . $row->getGift()->getPhotos()->first()->getImageName();
                    $templateProcessor->setImageValue('rcvd.image#' . $i, array('path' => $path, 'width' => 200, 'height' => 200));
                } else
                    $templateProcessor->setValue('rcvd.image#' . $i, '[image not found]');

                $i++;
            }
        } else {
            $templateProcessor->setValue('rcvd', '');
            $templateProcessor->setValue('rcvd.from', '');
            // $templateProcessor->setValue('rcvd.to', '');
            $templateProcessor->setValue('rcvd.date', '');
            $templateProcessor->setValue('rcvd.desc', '');
            $templateProcessor->setValue('rcvd.image', '');
        }


        return $templateProcessor->save();
    }
}
