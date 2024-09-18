<?php

namespace App\DataFixtures;

use App\Entity\Preference;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppUsers extends Fixture
{

    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher) {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setFullName('Administrator of system');
        $user->setUsername('admin');
        $user->setEmail('admin@admin.admin');
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, 'adminadmin'));
        $manager->persist($user);
        
        $user = new User();
        $user->setFullName('User of system');
        $user->setUsername('user');
        $user->setEmail('user@user.user');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, 'useruser'));
        $manager->persist($user);

        // $prod = [
        //     'Спорт',
        //     'Книги',
        //     'Музыка',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',
        //     '',

        // ];

        // preferences
        $preference = new Preference();
        $preference->setTitle('Спорт');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Книги');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Музыка');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Часы');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Картины');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Гаджеты');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Сувениры');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Ювелирная продукция');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Интерьер');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Одежда');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Аксессуары');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Транспорт');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Алкоголь');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Охота');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Рыбалка');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Сумки');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Садоводство');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Косметика');
        $manager->persist($preference);

        $preference = new Preference();
        $preference->setTitle('Посуда');
        $manager->persist($preference);

        // gifts

        // persons

        // mediagallery

        //save
        $manager->flush();

    }
}
