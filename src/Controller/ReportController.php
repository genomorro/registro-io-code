<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Reports\PatientTodayReport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/report')]
class ReportController extends AbstractController
{
    #[Route('/patient/today', name: 'app_report_patient_today', methods: ['GET'])]
    public function patientToday(
        AppointmentRepository $appointmentRepository,
        TranslatorInterface $translator
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $today = new \DateTime('today midnight');
        $tomorrow = new \DateTime('tomorrow midnight');

        $data = $appointmentRepository->findPatientsWithAppointmentsAndAttendance($today, $tomorrow);

        $report = new PatientTodayReport([
            "data" => $data,
            "translator" => $translator
        ]);

        return $this->render('report/patient_today.html.twig', [
            'report' => $report->run()->render(true),
        ]);
    }
}
