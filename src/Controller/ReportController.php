<?php

namespace App\Controller;

use App\Report\ActivityPerHourReport;
use App\Repository\AppointmentRepository;
use App\Repository\AttendanceRepository;
use App\Repository\StakeholderRepository;
use App\Repository\UserRepository;
use App\Repository\VisitorRepository;
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

    #[Route('/activity/hour', name: 'app_report_activity_hour', methods: ['GET'])]
    public function activityPerHour(
        AttendanceRepository $attendanceRepository,
        VisitorRepository $visitorRepository,
        StakeholderRepository $stakeholderRepository,
        TranslatorInterface $translator
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $todayStart = new \DateTime('today midnight');
        $todayEnd = new \DateTime('tomorrow midnight');

        $todayAttendance = $attendanceRepository->findTodayCheckTimes();
        $todayVisitor = $visitorRepository->findTodayCheckTimes();
        $todayStakeholder = $stakeholderRepository->findTodayCheckTimes();

        $todayDataProcessed = $this->processTodayData($todayAttendance, $todayVisitor, $todayStakeholder, $todayStart, $todayEnd);

        $allAttendance = $attendanceRepository->findAllCheckTimes();
        $allVisitor = $visitorRepository->findAllCheckTimes();
        $allStakeholder = $stakeholderRepository->findAllCheckTimes();

        $historicalDataProcessed = $this->processHistoricalData($allAttendance, $allVisitor, $allStakeholder);

        $report = new ActivityPerHourReport([
            "todayData" => $todayDataProcessed,
            "historicalData" => $historicalDataProcessed,
            "translator" => $translator
        ]);

        return $this->render('report/activity_per_hour.html.twig', [
            'report' => $report->run()->render(true),
        ]);
    }

    private function processTodayData(array $attendance, array $visitor, array $stakeholder, \DateTime $todayStart, \DateTime $todayEnd): array
    {
        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $data[$i] = [
                'hour' => sprintf('%02d:00', $i),
                'attendance' => 0,
                'visitor' => 0,
                'stakeholder' => 0,
            ];
        }

        $this->fillActivityToday($data, $attendance, 'attendance', $todayStart, $todayEnd);
        $this->fillActivityToday($data, $visitor, 'visitor', $todayStart, $todayEnd);
        $this->fillActivityToday($data, $stakeholder, 'stakeholder', $todayStart, $todayEnd);

        return array_values($data);
    }

    private function fillActivityToday(array &$data, array $records, string $key, \DateTime $todayStart, \DateTime $todayEnd): void
    {
        foreach ($records as $r) {
            $start = $r['checkInAt'];
            $end = $r['checkOutAt'];

            if ($start >= $todayStart && $start < $todayEnd) {
                $h = (int)$start->format('H');
                $data[$h][$key]++;
            }

            if ($end && $end >= $todayStart && $end < $todayEnd) {
                $h = (int)$end->format('H');
                $data[$h][$key]++;
            }
        }
    }

    private function processHistoricalData(array $attendance, array $visitor, array $stakeholder): array
    {
        $allDays = [];
        $activityByDayAndHour = []; // [date_string => [hour => [attr => count]]]

        $this->collectHistorical($activityByDayAndHour, $attendance, 'attendance', $allDays);
        $this->collectHistorical($activityByDayAndHour, $visitor, 'visitor', $allDays);
        $this->collectHistorical($activityByDayAndHour, $stakeholder, 'stakeholder', $allDays);

        $numDays = count($allDays);
        if ($numDays === 0) $numDays = 1;

        $result = [];
        for ($i = 0; $i < 24; $i++) {
            $row = [
                'hour' => sprintf('%02d:00', $i),
                'attendance' => 0,
                'visitor' => 0,
                'stakeholder' => 0,
            ];
            foreach ($activityByDayAndHour as $day => $hours) {
                if (isset($hours[$i])) {
                    $row['attendance'] += $hours[$i]['attendance'] ?? 0;
                    $row['visitor'] += $hours[$i]['visitor'] ?? 0;
                    $row['stakeholder'] += $hours[$i]['stakeholder'] ?? 0;
                }
            }
            $row['attendance'] /= $numDays;
            $row['visitor'] /= $numDays;
            $row['stakeholder'] /= $numDays;
            $result[] = $row;
        }
        return $result;
    }

    private function collectHistorical(array &$activity, array $records, string $key, array &$allDays): void
    {
        foreach ($records as $r) {
            $start = $r['checkInAt'];
            $end = $r['checkOutAt'];

            $dayIn = $start->format('Y-m-d');
            $allDays[$dayIn] = true;
            $hIn = (int)$start->format('H');

            if (!isset($activity[$dayIn])) {
                $activity[$dayIn] = [];
            }
            if (!isset($activity[$dayIn][$hIn])) {
                $activity[$dayIn][$hIn] = ['attendance' => 0, 'visitor' => 0, 'stakeholder' => 0];
            }
            $activity[$dayIn][$hIn][$key]++;

            if ($end) {
                $dayOut = $end->format('Y-m-d');
                $allDays[$dayOut] = true;
                $hOut = (int)$end->format('H');

                if (!isset($activity[$dayOut])) {
                    $activity[$dayOut] = [];
                }
                if (!isset($activity[$dayOut][$hOut])) {
                    $activity[$dayOut][$hOut] = ['attendance' => 0, 'visitor' => 0, 'stakeholder' => 0];
                }
                $activity[$dayOut][$hOut][$key]++;
            }
        }
    }
}
