<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class EmployeeFixtures extends Fixture
{
    public function load(ObjectManager $ObjectManager)
    {
        $faker = Factory::create();
        // Иерархия позиций с низу вверх
        $positions = [
            'developer' => [
                'subordinates' => [],
                'salary' => ['min' => 40000, 'max' => 60000]
            ],
            'leadDeveloper' => [
                'subordinates' => [],
                'salary' => ['min' => 60000, 'max' => 80000]
            ],
            'projectManager' => [
                'subordinates' => [],
                'salary' => ['min' => 100000, 'max' => 120000]
            ],
            'chiefFinancialOfficer' => [
                'subordinates' => [],
                'salary' => ['min' => 100000, 'max' => 140000]
            ],
            'chiefExecutiveOfficer' => [
                'subordinates' => [],
                'salary' => ['min' => 100000, 'max' => 140000]
            ],
        ];

        $teamSize = 5;
        $people = 626;

        // Создаем Chief Executive Officer
        $ceo = new Employee();
        $ceo->setFullName($faker->name);
        $ceo->setPosition('Chief Executive Officer');
        $ceo->setHireDate($faker->dateTimeBetween('-5 years', 'now'));
        $ceo->setSalary($faker->numberBetween($positions['chiefExecutiveOfficer']['salary']['min'], $positions['chiefExecutiveOfficer']['salary']['max'])); // Примерная зарплата CEO

        $ObjectManager->persist($ceo);
        --$people;

        // Создаем Chief Financial Officers
        for ($i = 1; $i <= $teamSize; $i++) {
            $cfo = new Employee();
            $cfo->setFullName($faker->name);
            $cfo->setPosition('Chief Financial Officer');
            $cfo->setHireDate($faker->dateTimeBetween('-5 years', 'now'));
            $cfo->setSalary($faker->numberBetween($positions['chiefFinancialOfficer']['salary']['min'], $positions['chiefFinancialOfficer']['salary']['max'])); // Примерная зарплата CFO

            $ObjectManager->persist($cfo);
            $ObjectManager->flush();
            $cfo->setManager($ceo->getId());
            --$people;

            // Создаем PM для каждого Chief Financial Officer
            for ($j = 1; $j <= $teamSize; $j++) {
                $pm = new Employee();
                $pm->setFullName($faker->name);
                $pm->setPosition('Project Manager');
                $pm->setHireDate($faker->dateTimeBetween('-5 years', 'now'));
                $pm->setSalary($faker->numberBetween($positions['projectManager']['salary']['min'], $positions['projectManager']['salary']['max']));


                $ObjectManager->persist($pm);
                $ObjectManager->flush();
                $pm->setManager($cfo->getId());
                --$people;

                // Создаем 5 Lead Developer для каждого PM
                for ($k = 1; $k <= $teamSize; $k++) {
                    $leadDeveloper = new Employee();
                    $leadDeveloper->setFullName($faker->name);
                    $leadDeveloper->setPosition('Lead Developer');
                    $leadDeveloper->setHireDate($faker->dateTimeBetween('-5 years', 'now'));
                    $leadDeveloper->setSalary($faker->numberBetween($positions['leadDeveloper']['salary']['min'], $positions['leadDeveloper']['salary']['max'])); // Примерная зарплата Lead Developer

                    $ObjectManager->persist($leadDeveloper);
                    $ObjectManager->flush();
                    $leadDeveloper->setManager($pm->getId());

                    $positions['leadDeveloper']['subordinates'][] = $leadDeveloper;
                    --$people;
                }

                $positions['projectManager']['subordinates'][] = $pm;
            }

            $positions['chiefFinancialOfficer']['subordinates'][] = $cfo;
        }

        $ObjectManager->flush();
        // Создаем Developer
        for ($i = 1; $i <= $people; $i++) {
            $developer = new Employee();
            $developer->setFullName($faker->name);
            $developer->setPosition('Developer');
            $developer->setHireDate($faker->dateTimeBetween('-5 years', 'now'));
            $developer->setSalary($faker->numberBetween($positions['developer']['salary']['min'], $positions['developer']['salary']['max']));

            $randomLeadDeveloper = array_rand($positions['leadDeveloper']['subordinates']);
            $manager = $positions['leadDeveloper']['subordinates'][$randomLeadDeveloper];

            $ObjectManager->persist($developer);
            $developer->setManager($manager->getId());
        }

        $ObjectManager->flush();

        $employees = $ObjectManager->getRepository(Employee::class)->findAll();

        // Add subordinates to the first employee
        foreach ($employees as $employee) {
            $managerId = $employee->getManager();

            if ($managerId) {
                $manager = $ObjectManager->getRepository(Employee::class)->find($managerId);
                $manager->addSubordinate($employee->getId());
                $ObjectManager->persist($manager);
            }
        }

        $ObjectManager->flush();
    }
}