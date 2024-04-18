<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeCreateType;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;


class EmployeeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EmployeeRepository $employeeRepository, Request $request): Response
    {
        $managerIds = $request->query->get('managers');
        $managerIdsArray = $employeeRepository->generateIdsArray($managerIds);
        $url = $this->generateUrl('app_index') . '?managers=' . implode(',', $managerIdsArray);
        $employeesTree = $employeeRepository->createEmployeeTree($managerIds);

        return $this->render('employee/index.html.twig', [
            'employees' => $employeesTree,
            'url' => $url,
            'title' => 'Employees Tree',
            'step' => 0
        ]);
    }

    #[Route('/table/', name: 'app_employees_table')]
    public function employee(Employee $employee, EmployeeRepository $employeeRepository, Request $request ): Response
    {
        $employeesPerPage = max(0, $request->query->getInt('qty', 20));;
        $offset = max(0, $request->query->getInt('offset', 0));
        $order = $request->query->getString('order', 'id');
        $searchQuery = $request->query->get('search_query', '');
        $searchType = $request->query->get('search_type', '');

        $employees = $employeeRepository->getEmployeePaginator($employee, $offset, $employeesPerPage, $order, $searchQuery, $searchType);

        return $this->render('employee/index.html.twig', [
            'employees' => $employees,
            'previous' => $offset - $employeesPerPage,
            'title' => 'Employees Table',
            'current' => $offset,
            'qty' => $employeesPerPage,
            'next' => min(count($employees), $offset + $employeesPerPage),
            'step' => 1,
        ]);
    }

    #[Route('/employee/new', name: 'employee_new')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($employee);
            $entityManager->flush();

            return $this->redirectToRoute('app_employee_edit', ['id' => $employee->getId()]);

        }

        return $this->render('employee/index.html.twig', [
            'form' => $form->createView(),
            'step' => 3,
            'title' => 'Create New Employee'
        ]);
    }

    #[Route('/employee/{id}/edit', name: 'app_employee_edit')]
    public function edit(Request $request, Employee $employee, $id, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            $entityManager->flush();

            return $this->redirectToRoute('app_employee_edit', ['id' => $employee->getId()]);
        }

        return $this->render('employee/index.html.twig', [
            'employee' => $employee,
            'form' => $form->createView(),
            'title' => 'Employee Edit',
            'step' => 2,
            'id' => $id
        ]);
    }
}
