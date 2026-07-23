<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Quittance de Paiement - {{ $payment->payment_reference }}</title>
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
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #0B3B75;
        }
        .subtitle {
            font-size: 12px;
            color: #64748B;
        }
        .box {
            background-color: #F4F7FB;
            border: 1px solid #155EEF;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
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
        }
        .total-row td {
            font-size: 15px;
            font-weight: bold;
            color: #0B3B75;
            border-top: 2px solid #0B3B75;
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
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="title">RÉPUBLIQUE FRANÇAISE</div>
                    <div class="subtitle">Liberté - Égalité - Fraternité</div>
                </td>
                <td style="text-align: right;">
                    <div style="font-weight: bold; color: #0B3B75;">AppGov - Trésor Public</div>
                    <div class="subtitle">Quittance officielle de paiement</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="ref-label">Référence de quittance</div>
                    <div class="ref-value">{{ $payment->payment_reference }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="ref-label">Date du paiement</div>
                    <div style="font-size: 14px; font-weight: bold;">
                        {{ $payment->reconciled_at ? $payment->reconciled_at->timezone('Europe/Paris')->format('d/m/Y H:i (CET)') : '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title" style="margin-bottom: 10px; font-weight: bold; color: #0B3B75;">Détail des règlements</div>
    <table class="details-table">
        <thead>
            <tr>
                <th>Désignation</th>
                <th style="text-align: right;">Montant ({{ $payment->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment->invoice->lines as $line)
                <tr>
                    <td>{{ $line->label_fr }}</td>
                    <td style="text-align: right; font-family: monospace;">{{ number_format($line->amount_minor / 100, 2, ',', ' ') }} €</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>Montant Total Réglé</td>
                <td style="text-align: right; font-family: monospace;">{{ number_format($payment->amount_minor / 100, 2, ',', ' ') }} {{ $payment->currency }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Quittance de règlement délivrée numériquement sur la plateforme AppGov France.
        Référence facture : {{ $payment->invoice->invoice_number }} | Dossier n° : {{ $payment->invoice->application->reference ?? '-' }}
    </div>
</body>
</html>
