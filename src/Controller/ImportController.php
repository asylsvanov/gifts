<?php

namespace App\Controller;

use App\Repository\FlowRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use App\Entity\Gift;
use App\Entity\Flow;
use App\Entity\Photo;
use App\Entity\Person;

class ImportController extends AbstractController
{
    #[Route('/admin/import', name: 'app_import')]
    public function index(): Response
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(TRUE);
        $source = __DIR__ . "/../resources/archive.xlsx";
        $spreadsheet = $reader->load($source);

        $worksheet = $spreadsheet->getActiveSheet();

        $data = [];

        $i = 1;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(TRUE);
            $row = [];
            foreach ($cellIterator as $cell) {
                if ($i == 2) {
                    //dump($cell);
                }
                $row[] = $cell->getValue();
            }
            $data[] = $row;
            $i++;
        }

        //dump($data);

        return $this->render('import/index.html.twig', [
            'controller_name' => 'ImportController',
            'data' => $data
        ]);
    }

    #[Route('/admin/import/flow', name: 'app_import_inspectflow')]
    public function inspectFlow(EntityManagerInterface $em, FlowRepository $flowRepository, PersonRepository $personRepository): Response
    {

        // $countries = [
        //     'Afghanistan' => 'AF',
        //     'Armenia' => 'AM',
        //     'Azerbaijan' => 'AZ',
        //     'Belarus' => 'BY',
        //     'Belgium' => 'BE',
        //     'Cambodia' => 'KH',
        //     'China' => 'CN',
        //     'Finland' => 'FI',
        //     'Germany' => 'DE',
        //     'Hungary' => 'HU',
        //     'India' => 'IN',
        //     'Iran' => 'IR',
        //     'Jordan' => 'JO',
        //     'Kazakhstan' => 'KZ',
        //     'Kyrgyzstan' => 'KG',
        //     'Mongolia' => 'MN',
        //     'Pakistan' => 'PK',
        //     'Philippines' => 'PH',
        //     'Qatar' => 'QA',
        //     'Russia' => 'RU',
        //     'Saudi-Arabia' => 'SA',
        //     'Serbia' => 'RS',
        //     'South-Korea' => 'KR',
        //     'Spain' => 'ES',
        //     'Uzbekistan' => 'UZ',
        //     'United-Arab-Emirates' => 'AE',
        //     'Tajikistan' => 'TJ',
        //     'United-States' => 'US',
        //     'Turkey' => 'TR'
        // ];

        // $flows = $flowRepository->getImportedToNames();
        //iterate flow where empty personTo
        // foreach ($flows as $flow) {
        //     $name = $flow->getImportPersonTo();
        //     $category = $flow->getImportPersonToCategory();
        //     $country = $flow->getImportPersonToCountry();

        //     $person = new Person();
        //     $person->setFirstName($name);
        //     $person->setLastName('[CHECK]');
        //     $person->setSex(1);
        //     $person->setCountry($countries[$country]);
        //     $person->setBirthAt(new \DateTimeImmutable('1900-01-01'));
        //     $person->setLanguage(['kk']);
        //     $person->setCategory([ 0 => $category ]);

        //     $em->persist($person);
        // }

        // $em->flush();    

        //get all flow where personTo is null
        $flows = $flowRepository->findBy(
            ['personTo' => null]
        );

        $i = 1;

        //then find person by importPersonTo
        // foreach ($flows as $flow) {
        //     $person = $personRepository->findOneBy([
        //         'firstName' => trim($flow->getImportPersonTo())
        //     ]);

        //     //set flows personTo 
        //     if ($person){
        //         $flow->setPersonTo($person);
        //         $em->persist($flow);
        //     }
        // }
        // $em->flush();

        return $this->render('import/flow.html.twig', [
            'flows' => $flows
        ]);
    }

    #[Route('/admin/import/images', name: 'app_import_images')]
    public function images(EntityManagerInterface $em): Response
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $source = __DIR__ . "/../resources/archive.xlsx";
        $spreadsheet = $reader->load($source);
        $images = [];

        $i = 0;

        foreach ($spreadsheet->getActiveSheet()->getDrawingCollection() as $drawing) {
            //save image 
            if ($drawing instanceof MemoryDrawing) {
                ob_start();
                call_user_func(
                    $drawing->getRenderingFunction(),
                    $drawing->getImageResource()
                );
                $imageContents = ob_get_contents();
                ob_end_clean();
                switch ($drawing->getMimeType()) {
                    case MemoryDrawing::MIMETYPE_PNG :
                        $extension = 'png';
                        break;
                    case MemoryDrawing::MIMETYPE_GIF:
                        $extension = 'gif';
                        break;
                    case MemoryDrawing::MIMETYPE_JPEG :
                        $extension = 'jpg';
                        break;
                }
            } else {
                if ($drawing->getPath()) {
                    // Check if the source is a URL or a file path
                    if ($drawing->getIsURL()) {
                        $imageContents = file_get_contents($drawing->getPath());
                        $filePath = tempnam(sys_get_temp_dir(), 'Drawing');
                        file_put_contents($filePath , $imageContents);
                        $mimeType = mime_content_type($filePath);
                        // You could use the below to find the extension from mime type.
                        // https://gist.github.com/alexcorvi/df8faecb59e86bee93411f6a7967df2c#gistcomment-2722664
                        $extension = File::mime2ext($mimeType);
                        unlink($filePath);            
                    }
                    else {
                        $zipReader = fopen($drawing->getPath(),'r');
                        $imageContents = '';
                        while (!feof($zipReader)) {
                            $imageContents .= fread($zipReader,1024);
                        }
                        fclose($zipReader);
                        $extension = $drawing->getExtension();            
                    }
                }
            }
            $myFileName = 'photos/00_Image_'.++$i.'.'.$extension;
            file_put_contents($myFileName,$imageContents);

            //get coordinates of image in spreadsheet [ 'H2' => PhotoEntity::class ]
            $images[ $drawing->getCoordinates()] = $myFileName ;
        }

        $reader = null;
        $spreadsheet = null;
        //dump($images);

        //iterate rows/cells
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $source = __DIR__ . "/../resources/archive.xlsx";
        $spreadsheet = $reader->load($source);
        $worksheet = $spreadsheet->getActiveSheet();

        // $data = [];
        $i = 1;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(TRUE);
            $row = [];
            if ($i != 1) {
                foreach ($cellIterator as $cell) {

                    // dump($cell->getDataType());
                        // set row image
                        if ($cell->getColumn() == 'H' && isset($images[$cell->getCoordinate()])) {
                            $row[] = $images[$cell->getCoordinate()];
                        }
                        elseif (false) {

                        } else
                            $row[] = $cell->getValue();
                }
                
                $data[] = $row;
            }
            $i++;
        }

        $reader = null;
        $spreadsheet = null;
        //dump($data);

        $categories = [
            1 => 'CATEGORY_PRESIDENT',
            2 => 'CATEGORY_PM',
            3 => 'CATEGORY_PROTOCOL',
        ];

        $countries = [
            'KZ' => 'Kazakhstan',
            1 => 'Kazakhstan',
            2 => 'United-Arab-Emirates',
            3 => 'Afghanistan',
            4 => 'Antigua-and-Barbuda',
            5 => 'England',
            7 => 'Armenia',
            8 => 'Netherlands-Antilles',
            9 => 'Angola',
            10 => 'Antarctica',
            12 => 'American-Samoa',
            15 => 'Aruba',
            18 => 'Bosnia-and-Herzegovina',
            19 => 'Barbados',
            20 => 'Bangladesh',
            21 => 'Belgium',
            22 => 'Burkina-Faso',
            23 => 'Bulgaria',
            24 => 'Bahrain',
            25 => 'Burundi',
            26 => 'Benin',
            27 => 'Saint-Barthelemy',
            28 => 'Bermuda',
            29 => 'Brunei',
            30 => 'Bolivia',
            32 => 'Bahamas',
            33 => 'Bhutan',
            35 => 'Botswana',
            36 => 'Belarus',
            37 => 'Belize',
            38 => 'Canada',
            39 => 'Cocos-Keeling-Islands',
            40 => 'Democratic-Republic-of-the-Congo',
            41 => 'Central-African-Republic',
            42 => 'Republic-of-the-Congo',
            43 => 'Switzerland',
            44 => 'Cote-dIvoire',
            45 => 'Cook-Islands',
            46 => 'Chile',
            47 => 'Cameroon',
            48 => 'China',
            49 => 'Colombia',
            50 => 'Costa-Rica',
            51 => 'Cuba',
            53 => 'Christmas-Island',
            54 => 'Cyprus',
            55 => 'Czech-Republic',
            56 => 'Germany',
            57 => 'Djibouti',
            58 => 'Denmark',
            59 => 'Dominica',
            60 => 'Dominican-Republic',
            61 => 'Algeria',
            62 => 'Ecuador',
            63 => 'Estonia',
            64 => 'Egypt',
            65 => 'Western-Sahara',
            66 => 'Eritrea',
            67 => 'Spain',
            68 => 'Ethiopia',
            69 => 'Finland',
            70 => 'Fiji',
            71 => 'Falkland-Islands',
            72 => 'Micronesia',
            73 => 'Faroes',
            74 => 'France',
            75 => 'Gabon',
            76 => 'United-Kingdom',
            77 => 'Grenada',
            78 => 'Georgia',
            81 => 'Ghana',
            82 => 'Gibraltar',
            83 => 'Greenland',
            84 => 'Gambia',
            85 => 'Guinea',
            88 => 'Greece',
            90 => 'Guatemala',
            91 => 'Guam',
            92 => 'Guinea-Bissau',
            93 => 'Guyana',
            94 => 'Hong-Kong',
            96 => 'Honduras',
            97 => 'Croatia',
            99 => 'Hungary',
            100 => 'Indonesia',
            101 => 'Ireland',
            102 => 'Israel',
            103 => 'Isle-of-Man',
            104 => 'India',
            106 => 'Iraq',
            107 => 'Iran',
            108 => 'Iceland',
            109 => 'Italy',
            110 => 'Jersey',
            111 => 'Jamaica',
            112 => 'Jordan',
            113 => 'Japan',
            114 => 'Kenya',
            115 => 'Kyrgyzstan',
            116 => 'Cambodia',
            117 => 'Kiribati',
            118 => 'Comoros',
            119 => 'Saint-Kitts-and-Nevis',
            120 => 'North-Korea',
            121 => 'South-Korea',
            122 => 'Kuwait',
            123 => 'Cayman-Islands',
            124 => 'Kazakhstan',
            125 => 'Laos',
            127 => 'Saint-Lucia',
            128 => 'Liechtenstein',
            129 => 'Sri-Lanka',
            130 => 'Liberia',
            131 => 'Lesotho',
            132 => 'Lithuania',
            133 => 'Luxembourg',
            134 => 'Latvia',
            135 => 'Libya',
            136 => 'Morocco',
            137 => 'Monaco',
            138 => 'Moldova',
            140 => 'Saint-Martin',
            141 => 'Madagascar',
            142 => 'Marshall-Islands',
            143 => 'Macedonia',
            144 => 'Mali',
            145 => 'Myanmar',
            146 => 'Mongolia',
            147 => 'Macau',
            149 => 'Martinique',
            151 => 'Montserrat',
            152 => 'Malta',
            153 => 'Mauritius',
            154 => 'Maldives',
            155 => 'Malawi',
            156 => 'Mexico',
            157 => 'Malaysia',
            158 => 'Mozambique',
            159 => 'Namibia',
            160 => 'New-Caledonia',
            161 => 'Niger',
            162 => 'Norfolk-Island',
            163 => 'Nigeria',
            164 => 'Nicaragua',
            165 => 'Netherlands',
            166 => 'Norway',
            167 => 'Nepal',
            168 => 'Nauru',
            170 => 'New-Zealand',
            171 => 'Oman',
            172 => 'Panama',
            173 => 'Peru',
            174 => 'French-Polynesia',
            175 => 'Papua-New-Guinea',
            176 => 'Philippines',
            177 => 'Pakistan',
            178 => 'Poland',
            180 => 'Pitcairn-Islands',
            181 => 'Puerto-Rico',
            182 => 'Palestine',
            183 => 'Portugal',
            184 => 'Palau',
            185 => 'Paraguay',
            188 => 'Romania',
            189 => 'Serbia',
            190 => 'Russia',
            191 => 'Rwanda',
            192 => 'Saudi-Arabia',
            193 => 'Solomon-Islands',
            194 => 'Seychelles',
            195 => 'Sudan',
            196 => 'Sweden',
            197 => 'Singapore',
            198 => 'Saint-Helena',
            199 => 'Slovenia',
            201 => 'Slovakia',
            202 => 'Sierra-Leone',
            203 => 'San-Marino',
            204 => 'Senegal',
            205 => 'Somalia',
            206 => 'Suriname',
            208 => 'El-Salvador',
            209 => 'Syria',
            210 => 'Swaziland',
            211 => 'Turks-and-Caicos-Islands',
            212 => 'Chad',
            213 => 'French-Southern-Territories',
            214 => 'Togo',
            215 => 'Thailand',
            216 => 'Tajikistan',
            217 => 'Tokelau',
            219 => 'Turkmenistan',
            220 => 'Tunisia',
            221 => 'Tonga',
            222 => 'Turkey',
            223 => 'Trinidad-and-Tobago',
            224 => 'Tuvalu',
            225 => 'Taiwan',
            226 => 'Tanzania',
            227 => 'Ukraine',
            228 => 'Uganda',
            230 => 'United-States',
            231 => 'Uruguay',
            232 => 'Uzbekistan',
            233 => 'Vatican-City',
            234 => 'Saint-Vincent-and-the-Grenadines',
            235 => 'Venezuela',
            236 => 'British-Virgin-Islands',
            237 => 'US-Virgin-Islands',
            238 => 'Vietnam',
            239 => 'Vanuatu',
            240 => 'Wallis-And-Futuna',
            241 => 'Samoa',
            242 => 'Yemen',
            243 => 'Mayotte',
            244 => 'South-Africa',
            245 => 'Zambia',
            246 => 'Zimbabwe',
            247 => 'Andorra',
            248 => 'Azerbaijan',
            249 => 'Qatar',
        ];

        foreach ($data as $row) {

            $gift = new Gift();
            $flow = new Flow();
            $photo = new Photo();

            // $row[0] Name Gift->title
            // $row[1] Description Gift->summary
            // $row[2] ReceiptDate Flow->receivedAt
            // $row[3] CountryFromId Flow->importPersonFromCountry
            // $row[4] CountryToId Flow->importPersonToCountry
            // $row[5] GiftedName Person->firstname,lastname,surname | Flow->importPersonTo
            // $row[6] GiftedPositionId Person->category | Flow->importPersonToCategory
            // $row[7] image Gift->photo (Photo->imageName)

            $photo->setImageName($row[7]);

            if (empty($row[0]))
                $row[0] = 'UNTITLED';

            $gift->setTitle($row[0]);
            $gift->setSize('');
            if (empty($row[1]))
                $row[1] = '';
            $gift->setSummary($row[1]);
            $gift->setIsAvailable(false);
            $gift->setMaterial('');
            $gift->setIsActive(false);
            $gift->addPhoto($photo);

            $flow->setPersonFrom(null);
            $flow->setPersonTo(null);
            $flow->setDescription($row[5]);
            $flow->setGift($gift);
            $flow->setReceivedAt(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2]));
            $flow->setImportPersonFromCountry($countries[$row[3]]);

            if ($row[3] == '')
                $row[3] = 'KZ';
            if ($row[4] == '')
                $row[4] = 'KZ';
            $flow->setImportPersonToCountry($countries[$row[4]]);
            $flow->setImportPersonTo($row[5]);
            $flow->setImportPersonToCategory($categories[$row[6]]);

            $em->persist($photo);
            $em->persist($gift);
            $em->persist($flow);
        }

        $em->flush();
        
        //create GiftEntity::class (imported gift status \ blocked \ disabled)
            //create FlowEntity::class
            // Person To and Person From - Anonymous type string \ Search existing \ Regexp Person Name \ Wizard create selected persons
            // make additional fields personFromImported and personToImported
        // make ImportedFlowEntity::class with plain data and if user changes it - it will move to FlowEntity::class

        return $this->render('import/index.html.twig', [
            'controller_name' => 'ImportController',
        ]);
    }


    
    #[Route('/admin/import/persons', name: 'app_import_images')]
    public function persons(EntityManagerInterface $em): Response
    {
        //iterate rows/cells
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $source = __DIR__ . "/../resources/archive.xlsx";
        $spreadsheet = $reader->load($source);
        $worksheet = $spreadsheet->getActiveSheet();

        // $data = [];
        $i = 1;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(TRUE);
            $row = [];
            if ($i != 1) {
                foreach ($cellIterator as $cell) {

                    // dump($cell->getDataType());
                        // set row image
                        if ($cell->getColumn() == 'H' && isset($images[$cell->getCoordinate()])) {
                            $row[] = '';
                        }
                        elseif (false) {

                        } else
                            $row[] = $cell->getValue();
                }
                
                $data[] = $row;
            }
            $i++;
        }

        $reader = null;
        $spreadsheet = null;
        // dump($data);

        $categories = [
            1 => 'CATEGORY_PRESIDENT',
            2 => 'CATEGORY_PM',
            3 => 'CATEGORY_PROTOCOL',
        ];

        $countries = [
            'KZ' => 'Kazakhstan',
            1 => 'Kazakhstan',
            2 => 'United-Arab-Emirates',
            3 => 'Afghanistan',
            4 => 'Antigua-and-Barbuda',
            5 => 'England',
            7 => 'Armenia',
            8 => 'Netherlands-Antilles',
            9 => 'Angola',
            10 => 'Antarctica',
            12 => 'American-Samoa',
            15 => 'Aruba',
            18 => 'Bosnia-and-Herzegovina',
            19 => 'Barbados',
            20 => 'Bangladesh',
            21 => 'Belgium',
            22 => 'Burkina-Faso',
            23 => 'Bulgaria',
            24 => 'Bahrain',
            25 => 'Burundi',
            26 => 'Benin',
            27 => 'Saint-Barthelemy',
            28 => 'Bermuda',
            29 => 'Brunei',
            30 => 'Bolivia',
            32 => 'Bahamas',
            33 => 'Bhutan',
            35 => 'Botswana',
            36 => 'Belarus',
            37 => 'Belize',
            38 => 'Canada',
            39 => 'Cocos-Keeling-Islands',
            40 => 'Democratic-Republic-of-the-Congo',
            41 => 'Central-African-Republic',
            42 => 'Republic-of-the-Congo',
            43 => 'Switzerland',
            44 => 'Cote-dIvoire',
            45 => 'Cook-Islands',
            46 => 'Chile',
            47 => 'Cameroon',
            48 => 'China',
            49 => 'Colombia',
            50 => 'Costa-Rica',
            51 => 'Cuba',
            53 => 'Christmas-Island',
            54 => 'Cyprus',
            55 => 'Czech-Republic',
            56 => 'Germany',
            57 => 'Djibouti',
            58 => 'Denmark',
            59 => 'Dominica',
            60 => 'Dominican-Republic',
            61 => 'Algeria',
            62 => 'Ecuador',
            63 => 'Estonia',
            64 => 'Egypt',
            65 => 'Western-Sahara',
            66 => 'Eritrea',
            67 => 'Spain',
            68 => 'Ethiopia',
            69 => 'Finland',
            70 => 'Fiji',
            71 => 'Falkland-Islands',
            72 => 'Micronesia',
            73 => 'Faroes',
            74 => 'France',
            75 => 'Gabon',
            76 => 'United-Kingdom',
            77 => 'Grenada',
            78 => 'Georgia',
            81 => 'Ghana',
            82 => 'Gibraltar',
            83 => 'Greenland',
            84 => 'Gambia',
            85 => 'Guinea',
            88 => 'Greece',
            90 => 'Guatemala',
            91 => 'Guam',
            92 => 'Guinea-Bissau',
            93 => 'Guyana',
            94 => 'Hong-Kong',
            96 => 'Honduras',
            97 => 'Croatia',
            99 => 'Hungary',
            100 => 'Indonesia',
            101 => 'Ireland',
            102 => 'Israel',
            103 => 'Isle-of-Man',
            104 => 'India',
            106 => 'Iraq',
            107 => 'Iran',
            108 => 'Iceland',
            109 => 'Italy',
            110 => 'Jersey',
            111 => 'Jamaica',
            112 => 'Jordan',
            113 => 'Japan',
            114 => 'Kenya',
            115 => 'Kyrgyzstan',
            116 => 'Cambodia',
            117 => 'Kiribati',
            118 => 'Comoros',
            119 => 'Saint-Kitts-and-Nevis',
            120 => 'North-Korea',
            121 => 'South-Korea',
            122 => 'Kuwait',
            123 => 'Cayman-Islands',
            124 => 'Kazakhstan',
            125 => 'Laos',
            127 => 'Saint-Lucia',
            128 => 'Liechtenstein',
            129 => 'Sri-Lanka',
            130 => 'Liberia',
            131 => 'Lesotho',
            132 => 'Lithuania',
            133 => 'Luxembourg',
            134 => 'Latvia',
            135 => 'Libya',
            136 => 'Morocco',
            137 => 'Monaco',
            138 => 'Moldova',
            140 => 'Saint-Martin',
            141 => 'Madagascar',
            142 => 'Marshall-Islands',
            143 => 'Macedonia',
            144 => 'Mali',
            145 => 'Myanmar',
            146 => 'Mongolia',
            147 => 'Macau',
            149 => 'Martinique',
            151 => 'Montserrat',
            152 => 'Malta',
            153 => 'Mauritius',
            154 => 'Maldives',
            155 => 'Malawi',
            156 => 'Mexico',
            157 => 'Malaysia',
            158 => 'Mozambique',
            159 => 'Namibia',
            160 => 'New-Caledonia',
            161 => 'Niger',
            162 => 'Norfolk-Island',
            163 => 'Nigeria',
            164 => 'Nicaragua',
            165 => 'Netherlands',
            166 => 'Norway',
            167 => 'Nepal',
            168 => 'Nauru',
            170 => 'New-Zealand',
            171 => 'Oman',
            172 => 'Panama',
            173 => 'Peru',
            174 => 'French-Polynesia',
            175 => 'Papua-New-Guinea',
            176 => 'Philippines',
            177 => 'Pakistan',
            178 => 'Poland',
            180 => 'Pitcairn-Islands',
            181 => 'Puerto-Rico',
            182 => 'Palestine',
            183 => 'Portugal',
            184 => 'Palau',
            185 => 'Paraguay',
            188 => 'Romania',
            189 => 'Serbia',
            190 => 'Russia',
            191 => 'Rwanda',
            192 => 'Saudi-Arabia',
            193 => 'Solomon-Islands',
            194 => 'Seychelles',
            195 => 'Sudan',
            196 => 'Sweden',
            197 => 'Singapore',
            198 => 'Saint-Helena',
            199 => 'Slovenia',
            201 => 'Slovakia',
            202 => 'Sierra-Leone',
            203 => 'San-Marino',
            204 => 'Senegal',
            205 => 'Somalia',
            206 => 'Suriname',
            208 => 'El-Salvador',
            209 => 'Syria',
            210 => 'Swaziland',
            211 => 'Turks-and-Caicos-Islands',
            212 => 'Chad',
            213 => 'French-Southern-Territories',
            214 => 'Togo',
            215 => 'Thailand',
            216 => 'Tajikistan',
            217 => 'Tokelau',
            219 => 'Turkmenistan',
            220 => 'Tunisia',
            221 => 'Tonga',
            222 => 'Turkey',
            223 => 'Trinidad-and-Tobago',
            224 => 'Tuvalu',
            225 => 'Taiwan',
            226 => 'Tanzania',
            227 => 'Ukraine',
            228 => 'Uganda',
            230 => 'United-States',
            231 => 'Uruguay',
            232 => 'Uzbekistan',
            233 => 'Vatican-City',
            234 => 'Saint-Vincent-and-the-Grenadines',
            235 => 'Venezuela',
            236 => 'British-Virgin-Islands',
            237 => 'US-Virgin-Islands',
            238 => 'Vietnam',
            239 => 'Vanuatu',
            240 => 'Wallis-And-Futuna',
            241 => 'Samoa',
            242 => 'Yemen',
            243 => 'Mayotte',
            244 => 'South-Africa',
            245 => 'Zambia',
            246 => 'Zimbabwe',
            247 => 'Andorra',
            248 => 'Azerbaijan',
            249 => 'Qatar',
        ];

        foreach ($data as $k => $row) {
            unset($data[$k][0]);
            unset($data[$k][1]);
            unset($data[$k][2]);
            unset($data[$k][3]);
            unset($data[$k][7]);
        }

        $data = array_map("unserialize", array_unique(array_map("serialize", $data)));

        //dump($data);

        foreach ($data as $row) {
            $person = new Person();
            $person->setFirstName();
            $person->setLastName();
            $person->setCountry('');
        }
        
        // foreach ($data as $row) {

        //     $gift = new Gift();
        //     $flow = new Flow();
        //     $photo = new Photo();
        //     $person = new Person();

        //     // $row[4] CountryToId Flow->importPersonToCountry
        //     // $row[5] GiftedName Person->firstname,lastname,surname | Flow->importPersonTo
        //     // $row[6] GiftedPositionId Person->category | Flow->importPersonToCategory

        //     $photo->setImageName($row[7]);

        //     if (empty($row[0]))
        //         $row[0] = 'UNTITLED';

        //     $gift->setTitle($row[0]);
        //     $gift->setSize('');
        //     if (empty($row[1]))
        //         $row[1] = '';
        //     $gift->setSummary($row[1]);
        //     $gift->setIsAvailable(false);
        //     $gift->setMaterial('');
        //     $gift->setIsActive(false);
        //     $gift->addPhoto($photo);

        //     $flow->setPersonFrom(null);
        //     $flow->setPersonTo(null);
        //     $flow->setDescription($row[5]);
        //     $flow->setGift($gift);
        //     $flow->setReceivedAt(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2]));
        //     $flow->setImportPersonFromCountry($countries[$row[3]]);

        //     if ($row[3] == '')
        //         $row[3] = 'KZ';
        //     if ($row[4] == '')
        //         $row[4] = 'KZ';
        //     $flow->setImportPersonToCountry($countries[$row[4]]);
        //     $flow->setImportPersonTo($row[5]);
        //     $flow->setImportPersonToCategory($categories[$row[6]]);

        //     $em->persist($photo);
        //     $em->persist($gift);
        //     $em->persist($flow);
        // }

        // $em->flush();
        
        return $this->render('import/index.html.twig', [
            'controller_name' => 'ImportController',
        ]);
    }



}
