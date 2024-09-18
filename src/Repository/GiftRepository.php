<?php

namespace App\Repository;

use App\Entity\Gift;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
/**
 * @extends ServiceEntityRepository<Gift>
 *
 * @method Gift|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gift|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gift[]    findAll()
 * @method Gift[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GiftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gift::class);
    }

    public function save(Gift $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Gift $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getGiftsBy()
    {
        $query = $this->getEntityManager()->createQuery('SELECT g FROM App\Entity\Gift g');
        $result = $query->getResult();
        return $result;
    }

    public function getGiftsByPreferences($preferences)
    {
        foreach ($preferences as $preference) {
            $userPrefs[] = $preference->getId();
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('App\Entity\Gift', 'g');

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT g.* FROM gift g 
            INNER JOIN gift_preference p ON g.id = p.gift_id
            WHERE p.preference_id in (:prefs)', $rsm)
            ->setParameter('prefs', $userPrefs)
            ;

        return $query->getResult();
    }

    public function getGiftsByPreferencesOfPerson($person)
    {
        foreach ($person->getPreferences() as $preference) {
            $userPrefs[] = $preference->getId();
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('App\Entity\Gift', 'g');

        //dump($person->getCategory());

        $sex = [
            1 => 'MALE',
            2 => 'FEMALE',
        ];

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT g.* FROM gift g 
            INNER JOIN gift_preference p ON g.id = p.gift_id
            WHERE p.preference_id in (:prefs)
            and (g.gender = :sex or g.gender = "")
            and g.category = :category
            and g.is_available = 1
            and g.is_active = 1
            and g.counter > 0
            ORDER BY g.gender DESC
            ', $rsm)
            ->setParameter('prefs', $userPrefs)
            ->setParameter('sex', $sex[$person->getSex()])
            ->setParameter('category', $person->getCategory()[0])
            ;

        return $query->getResult();
    }

    
    public function getGiftsByPreferencesAndCategory($preferences, $category)
    {
        foreach ($preferences as $preference) {
            $userPrefs[] = $preference->getId();
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('App\Entity\Gift', 'g');

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT g.* FROM gift g 
            INNER JOIN gift_preference p ON g.id = p.gift_id
            WHERE p.preference_id in (:prefs) and g.category = :category', $rsm)
            ->setParameter('prefs', $userPrefs)
            ->setParameter('category', $category)
            ;

        return $query->getResult();
    }

    public function getGiftsByForm( $category, $gender = null, $generation = null, $preferences = null)
    {
        
        $qb = $this->createQueryBuilder('g')
            ->where('g.category = :cat')
            ->andWhere('g.isAvailable = true')
            ->andWhere('g.isActive = true')
            ->setParameter('cat', $category)
            ;

            if (!empty($gender)) {
                $qb
                ->andWhere('g.gender = :gender')
                ->setParameter('gender', $gender)
                ;
            }

            if (!empty($generation)) {
                $qb
                ->andWhere('g.generation = :generation')
                ->setParameter('generation', $generation)
                ;
            }

            if (!$preferences->isEmpty()) {
                //dump($preferences);
                // foreach ($preferences as $preference) {
                //     $preference->getId();
                //     $qb
                // ->andWhere(':pref MEMBER OF g.preferences')
                // ->setParameter('pref', $preference->getId());
                // }
                $userPrefs = [];
                foreach ($preferences as $preference) {
                    $userPrefs[] = $preference->getId();
                }

                $qb
                ->andWhere(':pref MEMBER OF g.preferences')
                ->setParameter('pref', $userPrefs);
            }

        return $qb
                ->orderBy('g.id', 'ASC')
                ->setMaxResults(50)
                ->getQuery()
                ->getResult();
    }



    public function getGiftsByFormForFilterExport( $data )
    {

        $qb = $this->createQueryBuilder('g')
            ;

            if (!empty($data['gender'])) {
                $userPrefs = [];
                    foreach ($data['preference'] as $preference) {
                        $userPrefs[] = $preference->getId();
                    }

                    $qb
                    ->andWhere(':pref MEMBER OF g.preferences')
                    ->setParameter('pref', $userPrefs);
            }
            

            if (!empty($data['gender'])) {
                $qb
                ->andWhere('g.gender = :gender or g.gender = :neutral')
                ->setParameter('gender', $data['gender'] )
                ->setParameter('neutral', '' )
                ;
            }

            if (!empty($data['category'])) {
                $qb
                ->andWhere('g.category = :category')
                ->setParameter('category', $data['category'])
                ;
            }

            if (!empty($data['generation'])) {
                $qb
                ->andWhere('g.generation = :generation')
                ->setParameter('generation', $data['generation'])
                ;
            }

            $qb
            ->andWhere('g.isAvailable = 1')
            ->andWhere('g.isActive = 1')
            ;

            return $qb
                ->orderBy('g.gender', 'DESC')
                ->setMaxResults(60)
                ->getQuery()
                ->getResult();
    }

    public function getGiftsByForm2( $data )
    {

        $qb = $this->createQueryBuilder('g')
            ->andWhere('g.gender = :gender or g.gender = :neutral')
            ->setParameter('gender', $data['gender'] )
            ->setParameter('neutral', '' )
            ;

            $userPrefs = [];
            foreach ($data['preference'] as $preference) {
                $userPrefs[] = $preference->getId();
            }

            $qb
            ->andWhere(':pref MEMBER OF g.preferences')
            ->setParameter('pref', $userPrefs);

            if (!empty($data['category'])) {
                $qb
                ->andWhere('g.category = :category')
                ->setParameter('category', $data['category'])
                ;
            }

            if (!empty($data['generation'])) {
                $qb
                ->andWhere('g.generation = :generation')
                ->setParameter('generation', $data['generation'])
                ;
            }

            if (!$data['ignore_counter'] == true) {
                $qb
                ->andWhere('g.counter > 0')
                ;    
            }

            $qb
            ->andWhere('g.isAvailable = 1')
            ->andWhere('g.isActive = 1')
            ;

            return $qb
                ->orderBy('g.gender', 'DESC')
                ->setMaxResults(60)
                ->getQuery()
                ->getResult();
    }

}
