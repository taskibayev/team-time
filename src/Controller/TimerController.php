<?php

namespace App\Controller;

use App\Catalog\DescriptionCatalog;
use App\Entity\Employee;
use App\Entity\TimeRecord;
use App\Form\EmployeeType;
use App\Service\TimerService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/timers')]
final class TimerController extends AbstractController
{
    #[Route('', name: 'timers', methods: ['GET'])]
    public function index(EntityManagerInterface $em, DescriptionCatalog $descriptions, FormFactoryInterface $formFactory): Response
    {
        $employees = $em->getRepository(Employee::class)->findBy([], ['lastName' => 'ASC']);
        $active = [];
        foreach ($em->getRepository(TimeRecord::class)->findBy(['endedAt' => null]) as $record) {
            $active[$record->getEmployee()->getId()] = $record;
        }

        $employeeForm = $this->createForm(EmployeeType::class, new Employee(), [
            'action' => $this->generateUrl('timer_employee_add'),
        ]);
        /** @var array<int, FormView> $editForms */
        $editForms = [];
        foreach ($employees as $employee) {
            $id = $employee->getId();
            if (null !== $id) {
                $editForms[$id] = $formFactory->createNamed('employee_edit_' . $id, EmployeeType::class, $employee, [
                    'action' => $this->generateUrl('timer_employee_edit', ['id' => $id]),
                ])->createView();
            }
        }

        return $this->render('timer/index.html.twig', [
            'employees' => $employees,
            'active' => $active,
            'suggestions' => $descriptions->all(),
            'employeeForm' => $employeeForm,
            'editForms' => $editForms,
        ]);
    }

    #[Route('/employees/add', name: 'timer_employee_add', methods: ['POST'])]
    public function addEmployee(Request $request, EntityManagerInterface $em): Response
    {
        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($employee);
                $em->flush();
                $this->addFlash('success', 'Employee added.');
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'An employee with this first and last name already exists.');
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('timers');
    }

    #[Route('/employees/{id}/edit', name: 'timer_employee_edit', methods: ['POST'])]
    public function editEmployee(Employee $employee, Request $request, EntityManagerInterface $em, FormFactoryInterface $formFactory): Response
    {
        $form = $formFactory->createNamed('employee_edit_' . $employee->getId(), EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Employee updated.');
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'An employee with this first and last name already exists.');
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('timers');
    }

    #[Route('/{id}/start', name: 'timer_start', methods: ['POST'])]
    public function start(Employee $employee, Request $request, TimerService $service): Response
    {
        if (!$this->isCsrfTokenValid('start' . $employee->getId(), (string) $request->request->get('_token'))) {
            throw new HttpException(403);
        }

        try {
            $service->start($employee, (string) $request->request->get('description'));
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('timers');
    }

    #[Route('/record/{id}/stop', name: 'timer_stop', methods: ['POST'])]
    public function stop(TimeRecord $record, Request $request, TimerService $service): Response
    {
        if (!$this->isCsrfTokenValid('stop' . $record->getId(), (string) $request->request->get('_token'))) {
            throw new HttpException(403);
        }

        try {
            $service->stop($record);
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('timers');
    }
}
