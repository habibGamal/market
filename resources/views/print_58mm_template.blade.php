<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>{{ $template->getTitle() }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

        :root {
            --text-color: #000;
            --border-color: #ccc;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            text-align: right;
            color: var(--text-color);
            line-height: 1.3;
            margin: 0;
            padding: 0;
            width: 58mm;
            font-size: 8pt;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                width: 58mm;
                margin: 0;
                padding: 0;
            }

            .receipt-container {
                width: 100%;
                padding: 2mm;
            }

            .receipt-container img {
                width: 30px;
                height: 30px;
            }

            .receipt-container h1 {
                font-size: 10pt;
                font-weight: bold;
                margin: 2mm 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 8pt;
            }

            td, th {
                padding: 1mm;
                text-align: right;
            }

            .border-bottom {
                border-bottom: 1px dashed var(--border-color);
            }

            .centered {
                text-align: center;
            }

            .info-item {
                margin-bottom: 1mm;
            }

            .info-label {
                font-weight: bold;
            }

            .total {
                font-weight: bold;
                margin-top: 2mm;
            }

            .header {
                text-align: center;
                margin-bottom: 3mm;
            }

            .divider {
                border-top: 1px dashed var(--border-color);
                margin: 2mm 0;
            }

            .footer {
                text-align: center;
                margin-top: 3mm;
                font-size: 7pt;
            }
        }
    </style>
</head>

<body>
    <button onclick="window.print()" class="no-print" style="display: block; margin: 10px auto; padding: 5px 10px;">
        طباعة
    </button>

    <div class="receipt-container">
        <div class="header">
            <img src="{{ $template->getLogoUrl() }}" alt="Logo" style="display: block; margin: 0 auto; width: 30px; height: 30px;">
            <h1 class="centered">{{ $template->getTitle() }}</h1>
            <div style="font-size: 7pt; text-align: center;">{{ now()->format('Y/m/d H:i') }}</div>
            <div class="divider"></div>
        </div>

        <div>
            @foreach ($template->getInfos() as $key => $value)
                <div class="info-item">
                    <span class="info-label">{{ $key }}:</span>
                    <span class="info-value">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        @if ($template->getItemHeaders() && $template->getItems())
            <div class="divider"></div>
            <table>
                <thead>
                    <tr>
                        @foreach ($template->getItemHeaders() as $header)
                            <th class="border-bottom">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($template->getItems() as $item)
                        <tr>
                            @foreach ($item as $value)
                                <td class="border-bottom">{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($template->getTotal())
            <div class="divider"></div>
            <div class="total">
                <span>المجموع الكلي:</span>
                <span>{{ $template->getTotal() }}</span>
            </div>
        @endif

        <div class="divider"></div>
        <div class="footer">
            شكراً لتعاملكم معنا
        </div>
    </div>
</body>

</html>
