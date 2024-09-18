<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @extends ServiceEntityRepository<Person>
 *
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function save(Person $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Person $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function searchBy2($sex, $age, $preferences): array
    {

        foreach ($preferences as $preference) {
            $userPrefs[] = $preference->getId();
        }

        return $this->createQueryBuilder('p')
            ->andWhere('p.sex = :sex')
            ->andWhere('p.preferences in (:prefs)')
            ->setParameter('sex', $sex)
            ->setParameter('prefs', $userPrefs)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchBy($sex, $age = null, $preferences = null, $country = null, $category = null)
    {
        foreach ($preferences as $preference) {
            $userPrefs[] = $preference->getId();
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('App\Entity\Person', 'p');
        
        $custom = '';

        if ($category != null) {
            $custom .= ' and p.category like :category';
        }

        if ($country != null) {
            $custom .= ' and p.country = :country';
        }

        if ($age != null) {
            $custom .= ' and p.age = :age';
        }

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT p.* FROM person p 
           INNER JOIN person_preference pp ON p.id = pp.person_id
           WHERE pp.preference_id in (:prefs) and p.sex = :sex' . $custom,
            $rsm
        )
            ->setParameter('prefs', $userPrefs)
            ->setParameter('sex', $sex)
        ;

        if ($category != null) {
            $query->setParameter('category', '%'.$category.'%');
        }

        if ($country != null) {
            $query->setParameter('country', $country);
        }

        if ($age != null) {
            $query->setParameter('age', $age);
        }

        return $query->getResult();
    }

    public function searchByCategoryAndCountry($category, $country)
    {
        return $this->createQueryBuilder('p')
           ->andWhere('p.category like :val1')
           ->andWhere('p.country = :val2')
           ->setParameter('val1', "%".$category."%")
           ->setParameter('val2', $country)
           ->getQuery()
           ->getOneOrNullResult()
       ;
    }

}