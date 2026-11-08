<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name: 'time_record')]
class TimeRecord
{
    #[ORM\Id,ORM\GeneratedValue,ORM\Column] private ?int $id = null;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')] private Employee $employee;
    #[ORM\Column(length: 255)] private string $description;
    #[ORM\Column(type: 'datetimetz_immutable')] private \DateTimeImmutable $startedAt;
    #[ORM\Column(type: 'datetimetz_immutable', nullable: true)] private ?\DateTimeImmutable $endedAt = null;
    public function __construct(Employee $e, string $d, \DateTimeImmutable $s)
    {
        $this->employee = $e;
        $this->description = trim($d);
        $this->startedAt = $s;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function isRunning(): bool
    {
        return null === $this->endedAt;
    }

    public function stop(\DateTimeImmutable $at): void
    {
        if (!$this->isRunning()) {
            throw new \DomainException('Timer is already stopped.');
        }

        if ($at < $this->startedAt) {
            throw new \DomainException('Stop time cannot precede start time.');
        }

        $this->endedAt = $at;
    }
}
