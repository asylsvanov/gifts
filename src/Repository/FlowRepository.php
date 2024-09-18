<?php

namespace App\Repository;

use App\Entity\Flow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Flow>
 *
 * @method Flow|null find($id, $lockMode = null, $lockVersion = null)
 * @method Flow|null findOneBy(array $criteria, array $orderBy = null)
 * @method Flow[]    findAll()
 * @method Flow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flow::class);
    }

    public function save(Flow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Flow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   public function getGiftsReceived($person): array
   {
       return $this->createQueryBuilder('f')
           ->andWhere('f.personTo = :val')
           ->setParameter('val', $person)
           ->orderBy('f.id', 'ASC')
           ->setMaxResults(1000)
           ->getQuery()
           ->getResult()
       ;
   }

   public function getGiftsGived($person): array
   {
       return $this->createQueryBuilder('f')
           ->andWhere('f.personFrom = :val')
           ->setParameter('val', $person)
           ->orderBy('f.id', 'ASC')
           ->setMaxResults(1000)
           ->getQuery()
           ->getResult()
       ;
   }

   public function getImportedToNames(): array
   {
       return $this->createQueryBuilder('f')
           ->where('f.personTo is null')
           ->groupBy('f.importPersonTo')
           ->getQuery()
           ->getResult()
       ;
   }

}
