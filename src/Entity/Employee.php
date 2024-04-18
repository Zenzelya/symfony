<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $hireDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $salary = null;

    #[ORM\Column(nullable: true)]
    private ?int $manager = null;

    #[ORM\Column(nullable: true)]
    private ?array $subordinates = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getHireDate(): ?\DateTimeInterface
    {
        return $this->hireDate;
    }

    public function setHireDate(\DateTimeInterface $hireDate): static
    {
        $this->hireDate = $hireDate;

        return $this;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }

    public function setSalary(string $salary): static
    {
        $this->salary = $salary;

        return $this;
    }

    public function getManager(): ?int
    {
        return $this->manager;
    }

    public function setManager(?int $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function getSubordinates(): ?array
    {
        return $this->subordinates;
    }

    public function setSubordinates(?array $subordinates): static
    {
        $this->subordinates = $subordinates;

        return $this;
    }

    /**
     * Add a subordinate to the subordinates array.
     *
     * @param int $subordinateId The id of the subordinate to add
     * @return $this
     */
    public function addSubordinate(int $subordinateId): static
    {
        // Initialize $this->subordinates as an empty array if it doesn't exist
        if (!isset($this->subordinates)) {
            $this->subordinates = [];
        }

        // Check if $subordinateId is not already in the array and add it if it's not
        if (!in_array($subordinateId, $this->subordinates)) {
            $this->subordinates[] = $subordinateId;
        }

        return $this;
    }
    /**
     * Remove a subordinate from the subordinates array.
     *
     * @param int $subordinateId The id of the subordinate to remove
     * @return $this
     */
    public function removeSubordinateItem(int $subordinateId): static
    {
        $key = array_search($subordinateId, $this->subordinates);
        if ($key !== false) {
            unset($this->subordinates[$key]);
        }

        return $this;
    }
}
