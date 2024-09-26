<?php

namespace App\Controller;

use App\Form\ExportType;
use App\Form\FlowType;
use App\Form\MatchCustomType;
use App\Form\MatchPersonType;
use App\Repository\FlowRepository;
use App\Repository\PersonRepository;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextBox;
use PhpOffice\PhpWord\Element\TextRun;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GiftRepository;  
use App\Form\MatchCountryType;
use App\Entity\Person;
use Symfony\Contracts\Translation\TranslatorInterface;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\AbstractContainer;
use App\Form\ExportPersonalType;

class ExportController extends AbstractController
{

    private $translator;
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    #[Route('/admin/export/custom', name: 'app_export_match_custom', methods: ['GET', 'POST'])]
    public function matchCustom(Request $request, PersonRepository $personRepository): Response
    {

        $form = $this->createForm(MatchCustomType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $persons = $personRepository->searchBy($data['sex'], $data['age'], $data['preference'], $data['country'], $data['category']);
            return $this->render('export/custom_index.html.twig', [
                'persons' => $persons,
            ]);
        }

        return $this->render('export/custom.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/admin/export/custom2', name: 'app_export_match_custom2', methods: ['GET', 'POST'])]
    public function customExport(GiftRepository $giftRepository, Request $request, PersonRepository $personRepository): Response
    {
        $form = $this->createForm(MatchCustomType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($request->get('actionName') == 'export') {

                if (!empty($data['country']) && !empty($data['category'])) {
                    $person = $personRepository->searchByCategoryAndCountry($data['category'],$data['country']);
                    if ($person)
                        return $this->redirectToRoute('app_export_match_person_by_id', [
                            'id' => $person->getId()
                        ]);
                }

                $variants = $giftRepository->getGiftsByForm($data['category'], $data['gender'], $data['generation'], $data['preference']);

                if (!empty($variants)) {
                    return new BinaryFileResponse($this->getDocxFileFromTemplate('template_only_gifts', $variants));
                }
                else {
                    $error = new FormError("Empty result!");
                    $form->addError($error);
                }
            } 
            elseif ($request->get('actionName') == 'person') {
                $persons = $personRepository->searchBy($data['gender'], $data['generation'], $data['preference'], $data['country'], $data['category']);
                return $this->render('export/custom.html.twig', [
                    'form' => $form,
                    'persons' => $persons,
                ]);
            }
        }

        return $this->render('export/custom.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/export/new', name: 'app_export_new', methods: ['GET', 'POST'])]
    public function exportNew(GiftRepository $giftRepository, Request $request, PersonRepository $personRepository, FlowRepository $flowRepository): Response
    {
        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $variants = $giftRepository->getGiftsByForm2($data);

                $person = $data['person'];

                if (!empty($variants) && empty($person)) {
                    return new BinaryFileResponse($this->getDocxFileFromTemplate('template_only_gifts', $variants));
                }
                elseif (!empty($variants) && !empty($person)) {
                    $received = $flowRepository->getGiftsReceived($person);
                    $gived = $flowRepository->getGiftsGived($person);
                    return new BinaryFileResponse($this->getDocxFileFromTemplate('template2', $variants, $received, $gived, $person));
                }
                else {
                    $error = new FormError("Empty result!");
                    $form->addError($error);
                }
            
        }

        return $this->render('export/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * match by country
     */
    #[Route('/admin/export/custom3', name: 'app_export_match_custom3', methods: ['GET', 'POST'])]
    public function customExport3(GiftRepository $giftRepository, Request $request): Response
    {
        $form = $this->createForm(MatchCustomType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $variants = $giftRepository->getGiftsByPreferences($data['preference']);
            return new BinaryFileResponse($this->getDocxFileFromTemplate('template_only_gifts', $variants));
        }

        return $this->render('export/custom.html.twig', [
            'form' => $form,
        ]);
    }
    

    #[Route('/admin/export/personal', name: 'app_export_personal', methods: ['GET', 'POST'])]
    public function exportPersonal(GiftRepository $giftRepository, Request $request, PersonRepository $personRepository, FlowRepository $flowRepository): Response
    {
        $form = $this->createForm(ExportPersonalType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $person = $data['person'];
                return $this->redirectToRoute('app_export_match_person_by_id', [
                    'id' => $person->getId()
                ]);
        }

        return $this->render('export/personal.html.twig', [
            'form' => $form,
        ]);

    }
    

    #[Route('/admin/export/person_byid/{id}', name: 'app_export_match_person_by_id', methods: ['GET'])]
    public function personById(Person $person, GiftRepository $giftRepository, FlowRepository $flowRepository ): Response
    {

            $variants = $giftRepository->getGiftsByPreferencesOfPerson($person);
            $received = $flowRepository->getGiftsReceived($person);
            $gived = $flowRepository->getGiftsGived($person);
            return new BinaryFileResponse($this->getDocxFileFromTemplate('template2', $variants, $received, $gived, $person));
            // $this->getDocxFileFromTemplate('template2', $variants, $received, $gived, $person);
            // return $this->render('import/index.html.twig', [
            // ]);
    }


    #[Route('/admin/export/country', name: 'app_export_match_country', methods: ['GET', 'POST'])]
    public function matchByCountry(Request $request, GiftRepository $giftRepository): Response
    {
        $form = $this->createForm(MatchCountryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $giftsReceived = $giftRepository->getGiftsBy();
            return new BinaryFileResponse($this->getDocxFileFromTemplate('template', $giftsReceived));
        }

        return $this->render('export/person.html.twig', [
            'form' => $form,
        ]);
    }


    private function getDocxFileFromTemplate($variants, $template = 'template', $received = null, $gived = null, $person = null)
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

//             $text = <<<EOF
//             tetasdfsd: asdfasdf
//             fasdfasfasd: afasfasf
//             gasgasg: asfasfasfd
//             fasadfsafd: asgasgsdg
// EOF;
//                 $templateProcessor->setValue('summary#' . $i, '');

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