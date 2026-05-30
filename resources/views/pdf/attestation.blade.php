@extends('pdf.layout')

@section('content')
    <div style="margin-top: 50px;">
        <div class="title">Attestation de Travail</div>

        <p style="margin-top: 40px;">
            Je soussigné, Monsieur le Responsable des Ressources Humaines de la société <strong>HRBoard Corp.</strong>, 
            certifie par la présente que :
        </p>

        <p style="text-align: center; font-size: 16px; margin: 30px 0;">
            <strong>M. / Mme {{ $employee->full_name }}</strong>
        </p>

        <p>
            Est employé(e) au sein de notre entreprise en qualité de <strong>{{ $employee->jobTitle }}</strong> 
            sous contrat <strong>{{ strtoupper($employee->contractType->value) }}</strong> depuis le <strong>{{ $employee->hireDate->format('d/m/Y') }}</strong>.
        </p>

        <p>
            Cette attestation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.
        </p>

        <div style="margin-top: 60px; float: right; width: 200px; text-align: center;">
            <p>Fait à Paris, le {{ date('d/m/Y') }}</p>
            <p style="margin-top: 40px;">La Direction</p>
            <div style="height: 80px; border: 1px dashed #ccc; margin-top: 10px;">Cachet & Signature</div>
        </div>
    </div>
@endsection
