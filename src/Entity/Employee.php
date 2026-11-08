<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'employee')]
#[ORM\UniqueConstraint(name: 'uniq_employee_normalized_name', columns: ['normalized_first_name', 'normalized_last_name'])]
#[ORM\HasLifecycleCallbacks]
class Employee
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 100)] #[Assert\NotBlank, Assert\Length(max: 100)] private string $firstName = '';
    #[ORM\Column(length: 100)] #[Assert\NotBlank, Assert\Length(max: 100)] private string $lastName = '';
    #[ORM\Column(length: 100)] private string $normalizedFirstName = '';
    #[ORM\Column(length: 100)] private string $normalizedLastName = '';
    #[ORM\Column(length: 50)] #[Assert\NotBlank] private string $position = '';
    #[ORM\Column(length: 50)] #[Assert\NotBlank] private string $department = '';
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $v): self
    {
        $this->firstName = self::clean($v);

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $v): self
    {
        $this->lastName = self::clean($v);

        return $this;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $v): self
    {
        $this->position = $v;

        return $this;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function setDepartment(string $v): self
    {
        $this->department = $v;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    #[ORM\PrePersist,ORM\PreUpdate]
    public function normalize(): void
    {
        $this->setFirstName($this->firstName);
        $this->setLastName($this->lastName);
        $this->normalizedFirstName = mb_strtolower($this->firstName);
        $this->normalizedLastName = mb_strtolower($this->lastName);
    }

    private static function clean(string $v): string
    {
        return preg_replace('/\s+/u', ' ', trim($v)) ?? trim($v);
    }
}
