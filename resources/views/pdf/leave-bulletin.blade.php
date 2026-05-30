@extends('pdf.layout')

@section('content')
    <div class="title">Bulletin de Congé</div>

    <div class="section-title">Bénéficiaire</div>
    <table>
        <tr>
            <th width="30%">Employé</th>
            <td>{{ $leave->employee->full_name }}</td>
        </tr>
        <tr>
            <th>Poste</th>
            <td>{{ $leave->employee->jobTitle }}</td>
        </tr>
    </table>

    <div class="section-title">Détails de la demande</div>
    <table>
        <tr>
            <th width="30%">Type de congé</th>
            <td>{{ $leave->type->name }}</td>
        </tr>
        <tr>
            <th>Période</th>
            <td>Du {{ $leave->start_date->format('d/m/Y') }} au {{ $leave->end_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Nombre de jours</th>
            <td>{{ $leave->days_count }} jours</td>
        </tr>
        <tr>
            <th>Motif</th>
            <td>{{ $leave->reason ?? 'Non précisé' }}</td>
        </tr>
    </table>

    <div class="section-title">Validation RH</div>
    <table>
        <tr>
            <th width="30%">Statut</th>
            <td><strong>{{ strtoupper($leave->status->name) }}</strong></td>
        </tr>
        <tr>
            <th>Validé par</th>
            <td>{{ $leave->approver?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Commentaire RH</th>
            <td>{{ $leave->hr_comment ?? 'Aucun' }}</td>
        </tr>
    </table>

    <div style="margin-top: 40px;">
        <div style="float: left; width: 45%; text-align: center;">
            <p>Signature Employé</p>
            <div style="height: 60px; border: 1px solid #eee; margin-top: 10px;"></div>
        </div>
        <div style="float: right; width: 45%; text-align: center;">
            <p>Signature Direction RH</p>
            <div style="height: 60px; border: 1px solid #eee; margin-top: 10px;"></div>
        </div>
    </div>
@endsection
