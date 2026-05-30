@extends('pdf.layout')

@section('content')
    <div class="title">Rapport Mensuel des Congés - {{ $monthName }} {{ $year }}</div>

    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Type</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Jours</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leaves as $leave)
                <tr>
                    <td>{{ $leave->employee->full_name }}</td>
                    <td>{{ $leave->type->name }}</td>
                    <td>{{ $leave->start_date->format('d/m/Y') }}</td>
                    <td>{{ $leave->end_date->format('d/m/Y') }}</td>
                    <td>{{ $leave->days_count }}</td>
                    <td>{{ $leave->status->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right;">
        <p>Visa RH</p>
        <div style="height: 80px; width: 200px; border: 1px solid #eee; display: inline-block;"></div>
    </div>
@endsection
