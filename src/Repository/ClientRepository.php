<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Client>
* @implements PasswordUpgraderInterface<Client>
 *
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Client) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

//    /**
//     * @return Client[] Returns an array of Client objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Client
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function verifMDP($nom, $mdp) // Controle du nom et du mot de passe
    {
        echo $nom;
        echo $mdp;
        $qb = $this->createQueryBuilder('c');
        $qb->select('COUNT(c)');
        $qb->andWhere('c.nom = :nom AND c.password = :mdp')->setParameter('nom', $nom)->setParameter('mdp', $mdp);
        return $qb->getQuery()->getSingleScalarResult();
    }
    public function listeClients() // Liste tous les clients avec pagination
    {
        $queryBuilder = $this->createQueryBuilder('c');

        // On n'ajoute pas de critère ou tri particulier ici car on veut tous les statuts, la construction
        // de notre requête est donc finie

        // On récupère la Query à partir du QueryBuilder
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
    public function suppClient($id) // Suppression du client d'identifiant $id
    {
        $qb = $this->createQueryBuilder('c');
        $query = $qb->delete('App\Entity\Client', 'c')
        ->where('c.id = :id')
        ->setParameter('id', $id);
        
        return $qb->getQuery()->getResult();
    }
}
