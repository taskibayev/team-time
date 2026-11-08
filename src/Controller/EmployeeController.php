<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Service\DurationFormatter;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EmployeeController extends AbstractController
{
    #[Route('/employees/{id}', name: 'employee_show', methods: ['GET'])]
    public function show(Employee $employee, Request $request, ReportService $reports, DurationFormatter $formatter): Response
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

        $id = $employee->getId();
        if (null === $id) {
            throw $this->createNotFoundException();
        }

        $report = $reports->generate($from, $to, $now, [$id]);

        return $this->render('employee/show.html.twig', [
            'employee' => $employee,
            'report' => $report,
            'total' => $formatter->format($report['total']),
            'from' => $from,
            'to' => $to,
            'now' => $now,
            'preset' => $preset,
        ]);
    }
}
