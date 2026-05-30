<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function employeeSheet(Employee $employee)
    {
        $pdf = Pdf::loadView('pdf.employee-sheet', compact('employee'));
        return $pdf->download("fiche-employe-{$employee->lastName}.pdf");
    }

    public function attestation(Employee $employee)
    {
        $pdf = Pdf::loadView('pdf.attestation', compact('employee'));
        return $pdf->download("attestation-travail-{$employee->lastName}.pdf");
    }

    public function leaveBulletin(Leave $leave)
    {
        $pdf = Pdf::loadView('pdf.leave-bulletin', compact('leave'));
        return $pdf->download("bulletin-conge-{$leave->id}.pdf");
    }

    public function departmentList(Department $department)
    {
        $department->load('employees');
        $pdf = Pdf::loadView('pdf.department-list', compact('department'));
        return $pdf->download("liste-employes-{$department->name}.pdf");
    }

    public function monthlyLeavesReport()
    {
        $month = now()->month;
        $year = now()->year;
        $monthName = now()->translatedFormat('F');

        $leaves = Leave::with('employee')
            ->whereMonth('start_date', $month)
            ->whereYear('start_date', $year)
            ->get();

        $pdf = Pdf::loadView('pdf.monthly-leaves', compact('leaves', 'monthName', 'year'));
        return $pdf->download("rapport-conges-{$monthName}-{$year}.pdf");
    }
}
