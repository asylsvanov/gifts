<?php

namespace App\Controller;

use Symfony\Component\Form\FormError;
use App\Repository\FlowRepository;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Entity\Gift;
use App\Form\ExportType;
use App\Form\GiftFilterType;
use App\Form\GiftType;
use App\Repository\GiftRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route('/admin/gift')]
class GiftController extends AbstractController
{


    private $translator;
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }


    #[Route('/', name: 'app_gift_index', methods: ['GET'])]
    public function index(GiftRepository $giftRepository, Request $request, FlowRepository $flowRepository): Response
    {
        $form = $this->createForm(GiftFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $qb = $giftRepository->createQueryBuilder('g');

            $qb
                ->andWhere('g.isAvailable = 1')
                ->andWhere('g.isActive = 1')
            ;

            if (!$data['preference']->isEmpty()) {

                $userPrefs = [];
                foreach ($data['preference'] as $preference) {
                    $userPrefs[] = $preference->getId();
                }

                $qb
                    ->join('g.preferences', 'u', 'WITH', null, 'u.id')
                    ->andWhere('u.id in (:prefs)')
                    ->setParameter('prefs', $userPrefs)
                ;
            }

            if (!empty($data['gender'])) {
                $qb->andWhere("g.gender = :gender or g.gender = :any_gender")
                ->setParameter("gender", $data['gender'])
                ->setParameter("any_gender", '');
            }
                
            if (!empty($data['category']))
                $qb->andWhere("g.category = :category")->setParameter("category", $data['category']);

            if (!empty($data['generation']))
                $qb->andWhere("g.generation = :generation")->setParameter("generation", $data['generation']);            

            $gifts = $qb->getQuery()->getResult();

            if (!empty($gifts) && $request->get('actionName') == 'export') {
                return new BinaryFileResponse($this->getDocxFileFromTemplate('template_only_gifts', $gifts));
            }

        } 
        else {
            $gifts = $giftRepository->findBy(
                ['isAvailable' => true]
            );
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('gift/index.html.twig', [
                'gifts' => $gifts,
                'form' => $form
            ]);
        } else {
            return $this->render('gift/index.html.twig', [
                'form' => $form,
                'gifts' => $giftRepository->findBy(
                    ['createdBy' => $this->getUser()->getId()]
                ),
            ]);
        }
    }

    #[Route('/archive', name: 'app_gift_index_archive', methods: ['GET'])]
    public function received(GiftRepository $giftRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('gift/archive.html.twig', [
                'gifts' => $giftRepository->findBy(
                    ['isAvailable' => false]
                ),
            ]);
        } else {
            return $this->render('gift/archive.html.twig', [
                'gifts' => $giftRepository->findBy(
                    ['createdBy' => $this->getUser()->getId()]
                ),
            ]);
        }
    }

     #[Route('/', name: 'app_gift_search', methods: ['POST'])]
    public function search(GiftRepository $giftRepository, Request $request): Response
    {

        $search = $request->request->get('query', '');

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('gift/index.html.twig', [
                'gifts' => $giftRepository->findBy(
                    [
                        'title' => "%".$search."%"
                    ]
                ),
            ]);
        } else {
            return $this->render('gift/index.html.twig', [
                'gifts' => $giftRepository->findBy(
                    [
                        'createdBy' => $this->getUser()->getUserIdentifier(),
                        'title' => "like '%".$search."%'"
                    ]
                ),
            ]);
        }
    }

    #[Route('/new', name: 'app_gift_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GiftRepository $giftRepository): Response
    {
        $gift = new Gift();
        $form = $this->createForm(GiftType::class, $gift);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $giftRepository->save($gift, true);

            return $this->redirectToRoute('app_gift_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gift/new.html.twig', [
            'gift' => $gift,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gift_show', methods: ['GET'])]
    public function show(Gift $gift, FlowRepository $flowRepository): Response
    {
        return $this->render('gift/show.html.twig', [
            'gift' => $gift,
            'flows' => $flowRepository->findBy([
                'gift' => $gift
            ]
            ),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gift_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Gift $gift, GiftRepository $giftRepository): Response
    {
        $form = $this->createForm(GiftType::class, $gift);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $giftRepository->save($gift, true);

            return $this->redirectToRoute('app_gift_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gift/edit.html.twig', [
            'gift' => $gift,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gift_delete', methods: ['POST'])]
    public function delete(Request $request, Gift $gift, GiftRepository $giftRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$gift->getId(), $request->request->get('_token'))) {
            $giftRepository->remove($gift, true);
        }

        return $this->redirectToRoute('app_gift_index', [], Response::HTTP_SEE_OTHER);
    }

    
    private function getDocxFileFromTemplate($template = 'template', $variants, $received = null, $gived = null, $person = null)
    {
            $source = __DIR__ . "/../resources/".$template.".docx";
            $templateProcessor = new TemplateProcessor($source);

            $tz = 'Asia/Almaty';
            $timestamp = time();
            $dt = new \DateTime("now", new \DateTimeZone($tz));
            $dt->setTimestamp($timestamp);

            if ($person != null) {

                if (!$person->getAttachments()->isEmpty()) {
                    $path = __DIR__ . "/../../public/attachments/" . $person->getAttachments()->first()->getImageName();
                    $templateProcessor->setImageValue('person.image', array('path' => $path, 'width' => 150, 'height' => 150));
                } else
                    $templateProcessor->setValue('person.image', '[image not found]');

                $templateProcessor->setValue('person.country', $this->translator->trans($person->getCountry()));
                $templateProcessor->setValue('person.position', $this->translator->trans($person->getCategory()[0]));
                $templateProcessor->setValue('person.title', $person->getFirstName().' '.$person->getSurname().' '.$person->getLastName());
                $templateProcessor->setValue('person.birth', $person->getBirthAt()->format('d.m.Y'));

                $summary = $person->getSummary();

                $summary = preg_replace("/\&nbsp\;/", ' ', $summary);
                $summary = preg_replace("/\&laquo\;/", '"', $summary);
                $summary = preg_replace("/\&raquo\;/", '"', $summary);

                $perLine = preg_split("/((\r?\n)|(\r\n?))/", $summary);

                //dump($perLine);

                foreach ($perLine as $k => $line) {
                    if (empty($line))
                        unset($perLine[$k]);
                }

                $templateProcessor->cloneBlock('SUMBLOCK', count($perLine), true, true);

                $i = 1;
                foreach($perLine as $line){
                    // do stuff with $line
                    $content = nl2br($line);
                    $content = preg_replace('#(?:<br\s*/?>\s*?){2,}#', ' ', $content);
                    $content = trim(strip_tags($content));

                    $templateProcessor->setValue('VAR#' . $i++, htmlspecialchars($content, ENT_XML1, 'UTF-8'));
                }
            }

            $templateProcessor->cloneBlock('VARIANTBLOCK', count($variants), true, true);
            $i = 1;

            foreach ($variants as $gift) {
                $templateProcessor->setValue('variant.title#' . $i, 'Вариант №' . $i);

                if (!$gift->getPhotos()->isEmpty()) {
                    $path = __DIR__ . "/../../public/photos/" . $gift->getPhotos()->first()->getImageName();
                    $templateProcessor->setImageValue('image#' . $i, array('path' => $path, 'width' => 500, 'height' => 300));
                } else
                    $templateProcessor->setValue('image#' . $i, '[image not found]');

                $templateProcessor->setValue('title#' . $i, htmlspecialchars($gift->getTitle(), ENT_XML1, 'UTF-8'));
                $templateProcessor->setValue('originCountry#' . $i, htmlspecialchars($this->translator->trans($gift->getOriginCountry()), ENT_XML1, 'UTF-8'));
                $templateProcessor->setValue('material#' . $i, htmlspecialchars($gift->getMaterial(), ENT_XML1, 'UTF-8'));
                $templateProcessor->setValue('size#' . $i, htmlspecialchars($gift->getSize(), ENT_XML1, 'UTF-8'));
                $templateProcessor->setValue('counter#' . $i, htmlspecialchars($gift->getCounter(), ENT_XML1, 'UTF-8'));
                
                if (!empty($gift->getAuthor()))
                    $templateProcessor->setValue('author#' . $i, 'Автор: ' . htmlspecialchars($gift->getAuthor(), ENT_XML1, 'UTF-8'));
                else
                    $templateProcessor->setValue('author#' . $i, null);

                if (!empty($gift->getPrice()) || $gift->getPrice() > 0)
                    $templateProcessor->setValue('price#' . $i, 'Стоимость: '.number_format($gift->getPrice(), 0, ".", " ") . " тг.");
                else 
                    $templateProcessor->setValue('price#' . $i, null);

                if (!empty($gift->getSummary()))
                    $templateProcessor->setValue('summary#' . $i, 'Примечание: ' . htmlspecialchars($gift->getSummary(), ENT_XML1, 'UTF-8'));
                else 
                    $templateProcessor->setValue('summary#' . $i, null);

                $i++;
            }

            

        if ($received != null) {
            $templateProcessor->cloneRow('rcvd', count($received));
            $i = 1;
            foreach ($received as $flow) {
                $templateProcessor->setValue('rcvd#' . $i, $i);
                $templateProcessor->setValue('rcvd.from#' . $i, $flow->getPersonFrom()->getFirstName().' '.$flow->getPersonFrom()->getLastName());
                $templateProcessor->setValue('rcvd.date#' . $i, $flow->getReceivedAt()->format('d.m.Y'));
                $templateProcessor->setValue('rcvd.desc#' . $i, htmlspecialchars($flow->getGift()->getTitle(), ENT_XML1, 'UTF-8'));

                if (!$flow->getGift()->getPhotos()->isEmpty() && $flow->getGift()->getPhotos()->first()->getImageName() != '') {
                    $path = __DIR__ . "/../../public/photos/" . $flow->getGift()->getPhotos()->first()->getImageName();
                    $templateProcessor->setImageValue('rcvd.image#' . $i, array('path' => $path, 'width' => 200, 'height' => 200));
                } else
                    $templateProcessor->setValue('rcvd.image#' . $i, '[image not found]');
                $i++;
            }
        } else {
            $templateProcessor->setValue('rcvd', '');
            $templateProcessor->setValue('rcvd.from', '');
            $templateProcessor->setValue('rcvd.date', '');
            $templateProcessor->setValue('rcvd.desc', '');
            $templateProcessor->setValue('rcvd.image', '');
        }

        if ($gived != null) {
            $templateProcessor->cloneRow('gvd', count($gived));
            $i = 1;
            
            foreach ($gived as $flow) {
                $templateProcessor->setValue('gvd#' . $i, $i);

                if ($flow->getPersonTo()) {
                    $gvdTo = $flow->getPersonTo()->getFirstName() . ' ' . $flow->getPersonTo()->getLastName();
                } else {
                    $gvdTo = $flow->getImportPersonTo();
                }

                $templateProcessor->setValue('gvd.to#' . $i, $gvdTo);
                $templateProcessor->setValue('gvd.date#' . $i, $flow->getReceivedAt()->format('d.m.Y'));
                $templateProcessor->setValue('gvd.desc#' . $i, htmlspecialchars($flow->getGift()->getTitle(), ENT_XML1, 'UTF-8'));

                if (!$flow->getGift()->getPhotos()->isEmpty() && $flow->getGift()->getPhotos()->first()->getImageName() != '') {
                    $path = __DIR__ . "/../../public/photos/" . $flow->getGift()->getPhotos()->first()->getImageName();
                    $templateProcessor->setImageValue('gvd.image#' . $i, array('path' => $path, 'width' => 200, 'height' => 200));
                } else
                    $templateProcessor->setValue('gvd.image#' . $i, '[image not found]');

                $i++;
            }
        } else {
            $templateProcessor->setValue('gvd', '');
            $templateProcessor->setValue('gvd.to', '');
            $templateProcessor->setValue('gvd.date', '');
            $templateProcessor->setValue('gvd.desc', '');
            $templateProcessor->setValue('gvd.image', '');
        }


        return $templateProcessor->save();
    }
}
