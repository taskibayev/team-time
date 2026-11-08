<?php

namespace App\Controller;

use App\Catalog\DepartmentCatalog;
use App\Catalog\PositionCatalog;
use App\Entity\Employee;
use App\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReportController extends AbstractController
{
    #[Route('/reports', name: 'reports')]
    public function index(Request $request, EntityManagerInterface $em, ReportService $service, PositionCatalog $positionCatalog, DepartmentCatalog $departmentCatalog): Response
    {
        $timezone = new \DateTimeZone('Asia/Aqtau');
        $now = new \DateTimeImmutable('now', $timezone);
        $preset = $request->query->getString('preset', 'today');
        $from = $now->setTime(0, 0);
        $to = $from->modify('+1 day');
        if ('last7' === $preset) {
            $from = $from->modify('-6 days');
        } elseif ('month' === $preset) {
            $from = $from->modify('-1 month');
        } elseif ('custom' === $preset) {
            $from = new \DateTimeImmutable($request->query->getString('from', $from->format('Y-m-d')), $timezone);
            $to = (new \DateTimeImmutable($request->query->getString('to', $to->format('Y-m-d')), $timezone))->modify('+1 day');
        }

        $employeeIds = [];
        foreach ($request->query->all('employees') as $value) {
            if (is_scalar($value) && ($id = (int) $value) > 0) {
                $employeeIds[] = $id;
            }
        }

        $positions = $this->validCatalogValues($request->query->all('positions'), $positionCatalog);
        $departments = $this->validCatalogValues($request->query->all('departments'), $departmentCatalog);
        $report = $service->generate($from, $to, $now, $employeeIds, $positions, $departments);

        return $this->render('report/index.html.twig', [
            'employees' => $em->getRepository(Employee::class)->findBy([], ['lastName' => 'ASC']),
            'positions' => $positionCatalog->all(),
            'departments' => $departmentCatalog->all(),
            'selectedPositions' => $positions,
            'selectedDepartments' => $departments,
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'now' => $now,
            'ids' => $employeeIds,
            'preset' => $preset,
        ]);
    }

    /**
     * @param array<mixed> $values
     *
     * @return list<string>
     */
    private function validCatalogValues(array $values, \App\Catalog\CatalogInterface $catalog): array
    {
        $valid = [];
        foreach ($values as $value) {
            if (is_string($value) && $catalog->has($value)) {
                $valid[] = $value;
            }
        }

        return array_values(array_unique($valid));
    }
}
