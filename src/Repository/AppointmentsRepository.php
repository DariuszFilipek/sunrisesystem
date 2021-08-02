<?php

namespace App\Repository;

use App\Entity\Appointments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Appointments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointments[]    findAll()
 * @method Appointments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointments::class);
    }
    
    public function getReservedAppointments(){
        
        $queryBuilder = $this->createQueryBuilder('ap');
        $query = $queryBuilder
            ->where('ap.ap_personal_id IS NOT NULL')
            ->orderBy('ap.ap_date', 'ASC')
            ->orderBy('ap.ap_hour', 'ASC')
            ->getQuery();
        $result = $query -> getResult();
        
        return $result;
        
    }
    
   
}
