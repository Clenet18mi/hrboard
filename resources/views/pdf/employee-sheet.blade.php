@extends('pdf.layout')

@section('content')
    <div class="title">Fiche Employé</div>

    <div class="section-title">Informations Personnelles</div>
    <table>
        <tr>
            <th width="30%">Nom complet</th>
            <td>{{ $employee->full_name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $employee->user->email }}</td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td>{{ $employee->phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Date de naissance</th>
            <td>{{ $employee->birthDate?->format('d/m/Y') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Genre</th>
            <td>{{ ucfirst($employee->gender) }}</td>
        </tr>
    </table>

    <div class="section-title">Contrat & Poste</div>
    <table>
        <tr>
            <th width="30%">Intitulé du poste</th>
            <td>{{ $employee->jobTitle }}</td>
        </tr>
        <tr>
            <th>Département</th>
            <td>{{ $employee->department?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Type de contrat</th>
            <td>{{ strtoupper($employee->contractType->value) }}</td>
        </tr>
        <tr>
            <th>Date d'embauche</th>
            <td>{{ $employee->hireDate->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Salaire brut</th>
            <td>{{ $employee->grossSalary ? number_format($employee->grossSalary, 2) . ' €' : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Statut</th>
            <td>{{ strtoupper($employee->status->value) }}</td>
        </tr>
    </table>
@endsection
