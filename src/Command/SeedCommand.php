<?php

namespace App\Command;

use App\Entity\Employee;
use App\Entity\TimeRecord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed', description: 'Add fictional sample data once')]
final class SeedCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $i, OutputInterface $o): int
    {
        if ($this->em->getRepository(Employee::class)->count([]) > 0) {
            $o->writeln('Existing data found; seed skipped.');

            return self::SUCCESS;
        }

        $a = (new Employee())->setFirstName('Maya')->setLastName('Stone')->setPosition('engineer')->setDepartment('engineering');
        $b = (new Employee())->setFirstName('Leo')->setLastName('Hart')->setPosition('designer')->setDepartment('product');
        $this->em->persist($a);
        $this->em->persist($b);
        $now = new \DateTimeImmutable('now');
        $r = new TimeRecord($a, 'Feature development', $now->modify('-3 hours'));
        $r->stop($now->modify('-1 hour'));
        $this->em->persist($r);
        $this->em->flush();
        $o->writeln('Sample data added.');

        return self::SUCCESS;
    }
}
