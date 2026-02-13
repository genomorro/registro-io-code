<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use App\Report\PatientTodayReport;
use App\Report\UserActivityReport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/report')]
class ReportController extends AbstractController
{
    #[Route(path: '/', name: 'app_report_index')]
    public function index(): Response
    {
	/* $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN'); */
        return $this->render('report/index.html.twig');
    }

    #[Route('/patient/today', name: 'app_report_patient_today', methods: ['GET'])]
    public function patientToday(
        AppointmentRepository $appointmentRepository,
        TranslatorInterface $translator
    ): Response {
        /* $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN'); */

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

    #[Route('/user/activity', name: 'app_report_user_activity', methods: ['GET'])]
    public function userActivity(
        UserRepository $userRepository,
        TranslatorInterface $translator
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $today = new \DateTime('today midnight');

        $data = $userRepository->findUserActivityReportData($today);

        $report = new UserActivityReport([
            "data" => $data,
            "translator" => $translator
        ]);

        return $this->render('report/user_activity.html.twig', [
            'report' => $report->run()->render(true),
        ]);
    }
}
