<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
        public function searchBookByRef($ref){
            $req = $this->createQueryBuilder('b')
            ->where('b.ref LIKE :ref')
            ->setParameter('ref', $ref)
            ->getQuery()
            ->getResult()
            ;
            return $req ;
        }
        public function booksListByAuthors()
        {
            return $this->createQueryBuilder('b')
                ->leftJoin('b.author', 'a')
                ->orderBy('a.userName', 'ASC')
                ->getQuery()
                ->getResult();
        }

        public function findBooksBefore2023()
        {
            $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->where('b.publicationDate < :year2023')
            ->andWhere('a.nb_books > 1')
            ->setParameter('year2023', new \DateTime('2023-01-01'));
    
            return $queryBuilder->getQuery()->getResult();
        }

        public function updateBooksCategory()
        {
            $em = $this->getEntityManager();
            $qb = $em->createQueryBuilder();
            $qb->update('App\Entity\Book', 'b')
            ->set('b.category', ':newCategory')
            ->where('b.category = :oldCategory')
            ->setParameter('newCategory', 'Romance')
            ->setParameter('oldCategory', 'Science-Fiction');

            return $qb->getQuery()->execute();
        }

        public function countRomance($category)
        {
            $em = $this->getEntityManager();
            $query = $em->createQuery('SELECT COUNT(b) FROM App\Entity\Book b WHERE b.category = :category') 
            ->setParameter('category', $category);
            return $query->getSingleScalarResult();
        }

        public function findBooksPublishedBetween2014And2018()
        {
            $startDate = new \DateTime('2014-01-01');
            $endDate = new \DateTime('2018-12-31');

            $em = $this->getEntityManager();
            $query = $em->createQuery('SELECT b FROM App\Entity\Book b WHERE b.publicationDate BETWEEN :startDate AND :endDate')
                ->setParameters([
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ]);

            return $query->getResult();
        }



        public function triTitle()
        {
            $em = $this->getEntityManager();
            $query = $em->createQuery('SELECT b FROM App\Entity\Book b ORDER BY b.title ASC'); 
            return $query->getResult();
        }
        


}
