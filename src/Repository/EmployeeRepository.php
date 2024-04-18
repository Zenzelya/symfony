<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * @extends ServiceEntityRepository<Employee>
 *
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    private array $employeeTree;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
        $this->employeeTree = $this->findBy(['position' => 'Chief Executive Officer']);
    }

    public function createEmployeeTree($query): array
    {
        $managerIdsArray = $this->generateIdsArray($query);

        if (empty($managerIdsArray)) {
            return $this->employeeTree;
        }

        foreach ($managerIdsArray as $id) {
            $this->replaceSubordinates($this->employeeTree[0], $id);
        }

        return $this->employeeTree;
    }
    private function replaceSubordinates($employee, int $id)
    {
        if ($employee->getId() === $id) {
            $subordinates = $this->findEmployees($employee->getSubordinates());
            $employee->setSubordinates($subordinates);
        } else {
            $subordinates = $employee->getSubordinates();
            if ($subordinates !== null ) {
                foreach ($employee->getSubordinates() as $subordinate) {
                    if (is_object($subordinate) && $subordinate  ) {
                        $this->replaceSubordinates($subordinate, $id);
                    }
                }
            }
        }

        return $this;
    }
    public function generateIdsArray(?string $query)
    {
        if ($query === null || $query === '') {
            return [];
        }
        $managerIdsArray = explode(',', $query);
        $managerIdsArray = array_map('intval', $managerIdsArray);

        return array_unique($managerIdsArray);
    }
    /**
     * Find employees by manager IDs.
     *
     * @param array $managerIds
     * @return Employee[] Returns an array of Employee objects
     */
    public function findEmployees(array $managerIds): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id IN (:managerIds)')
            ->setParameter('managerIds', $managerIds)
            ->getQuery()
            ->getResult();
    }
    public function getEmployeePaginator(Employee $employee, int $offset, int $employeesPerPage = 20, $order = 'id', string $searchQuery = '', string $searchType = ''): Paginator
   {
       $queryBuilder = $this->createQueryBuilder('e');

       if (!empty($searchQuery) && !empty($searchType)) {
           $queryBuilder->andWhere('e.' . $searchType . ' LIKE :searchQuery')
               ->setParameter('searchQuery', '%' . $searchQuery . '%');
       }

       $query = $queryBuilder
           ->orderBy('e.' . $order, 'ASC')
           ->setMaxResults($employeesPerPage)
           ->setFirstResult($offset)
           ->getQuery();

       return new Paginator($query);
   }
}
