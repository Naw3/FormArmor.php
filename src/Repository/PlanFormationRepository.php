<?php

namespace App\Repository;

use App\Entity\PlanFormation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlanFormation>
 *
 * @method PlanFormation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlanFormation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlanFormation[]    findAll()
 * @method PlanFormation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanFormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanFormation::class);
    }

    public function listePlans() // Liste de tous les plans de formation avec pagination
	{
		$queryBuilder = $this->createQueryBuilder('p');

		// On n'ajoute pas de critère ou tri particulier ici car on veut tous les plans de formation, la construction
		// de notre requête est donc finie

		// On récupère la Query à partir du QueryBuilder
		$query = $queryBuilder->getQuery();

		// On gère ensuite la pagination grace au service KNPaginator
		return $query->getResult();
	}
	public function suppPlan($id) // Suppression du plan de formation d'identifiant $id
	{
		$qb = $this->createQueryBuilder('p');
		$query = $qb->delete('App\Entity\PlanFormation', 'p')
		  ->where('p.id = :id')
		  ->setParameter('id', $id);
		
		return $qb->getQuery()->getResult();
	}
}
