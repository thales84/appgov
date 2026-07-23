<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accusé de Dépôt - {{ $application->reference }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            margin: 30px;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #0B3B75;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #0B3B75;
        }
        .subtitle {
            font-size: 12px;
            color: #64748B;
        }
        .reference-box {
            background-color: #F4F7FB;
            border: 1px solid #155EEF;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .reference-box table {
            width: 100%;
        }
        .ref-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748B;
            font-weight: bold;
        }
        .ref-value {
            font-size: 18px;
            font-weight: bold;
            color: #0B3B75;
            font-family: monospace;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0B3B75;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th, .details-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #F1F5F9;
        }
        .details-table th {
            background-color: #F8FAFC;
            color: #475569;
            font-weight: bold;
            width: 35%;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #E2E8F0;
            padding-top: 15px;
            font-size: 11px;
            color: #64748B;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">RÉPUBLIQUE FRANÇAISE</div>
                    <div class="subtitle">Liberté - Égalité - Fraternité</div>
                </td>
                <td style="text-align: right;">
                    <div style="font-weight: bold; color: #0B3B75;">AppGov France</div>
                    <div class="subtitle">Portail des Démarches Administratives</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="reference-box">
        <table>
            <tr>
                <td>
                    <div class="ref-label">Référence de suivi publique</div>
                    <div class="ref-value">{{ $application->reference }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="ref-label">Date du dépôt</div>
                    <div style="font-size: 14px; font-weight: bold; color: #172033;">
                        {{ $application->submitted_at ? $application->submitted_at->timezone('Europe/Paris')->format('d/m/Y H:i (CET)') : '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Détails de la démarche</div>
    <table class="details-table">
        <tr>
            <th>Procédure</th>
            <td>{{ $application->procedureVersion->title_fr }}</td>
        </tr>
        <tr>
            <th>Organisme responsable</th>
            <td>{{ $application->procedureVersion->service->organization->name_fr ?? 'Administration Publique' }}</td>
        </tr>
        <tr>
            <th>Demandeur</th>
            <td>{{ $application->citizen->name }} ({{ $application->citizen->email }})</td>
        </tr>
        <tr>
            <th>Statut du dossier</th>
            <td>Déposé / En attente d'instruction</td>
        </tr>
    </table>

    <div class="section-title">Pièces téléversées</div>
    <table class="details-table">
        <thead>
            <tr>
                <th>Intitulé de la pièce</th>
                <th>Fichier d'origine</th>
                <th>Empreinte SHA-256 (partielle)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($application->submittedDocuments as $doc)
                <tr>
                    <td>{{ $doc->requirement->name_fr ?? 'Pièce jointe' }}</td>
                    <td>{{ $doc->original_filename }}</td>
                    <td style="font-family: monospace; font-size: 10px;">{{ substr($doc->file_hash, 0, 16) }}...</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #94A3B8;">Aucune pièce téléversée</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Cet accusé de réception est un document informatique généré automatiquement par la plateforme AppGov.
        Conservez votre référence de suivi (<strong>{{ $application->reference }}</strong>) pour consulter l'avancement de votre dossier sur appgov.fr.
    </div>
</body>
</html>
