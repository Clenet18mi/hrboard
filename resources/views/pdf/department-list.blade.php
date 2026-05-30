@extends('pdf.layout')

@section('content')
    <div class="title">Liste des employés - {{ $department->name }}</div>

    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Poste</th>
                <th>Contrat</th>
                <th>Date d'embauche</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($department->employees as $emp)
                <tr>
                    <td>{{ $emp->full_name }}</td>
                    <td>{{ $emp->jobTitle }}</td>
                    <td>{{ strtoupper($emp->contractType->value) }}</td>
                    <td>{{ $emp->hireDate->format('d/m/Y') }}</td>
                    <td>{{ $emp->status->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
