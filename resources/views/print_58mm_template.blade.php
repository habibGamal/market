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
            width: 48mm;
            font-size: 7pt;
        }

        .receipt-container {
            width: 90%;
            margin: 0 auto;
            /* font-weight: 600; */
        }

        .header {
            text-align: center;
            margin-bottom: 1.5mm;
        }

        .header img {
            display: block;
            margin: 0 auto;
            width: 25px;
            height: 25px;
        }

        .header h1 {
            font-size: 9pt;
            font-weight: bold;
            margin: 1.5mm 0;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 1.5mm 0;
        }

        .info-item {
            margin-bottom: 1mm;
            font-size: 8pt;
            display: flex;
            justify-content: space-between;
        }

        .info-label {
            font-weight: bold;
        }

        .item-container {
            margin-bottom: 2mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 1mm;
        }

        .item-container .item-row {
            display: flex;
            flex-direction: column;
            margin-bottom: 0.5mm;
        }


        .item-container .nested-item-container {
            display: flex;
            gap: 10mm;
        }

        .item-label {
            font-weight: bold;
            font-size: 8pt;
        }

        .item-value {
            font-size: 8pt;
            word-break: break-word;
        }

        .total {
            font-weight: bold;
            margin-top: 1.5mm;
            font-size: 9pt;
            display: flex;
            justify-content: space-between;
        }

        .footer {
            text-align: center;
            margin-top: 1.5mm;
            font-size: 6pt;
            padding-bottom: 0mm;
        }

        .centered {
            text-align: center;
        }

        .print-btn {
            display: block;
            margin: 10px auto;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                width: 48mm;
                margin: 0;
                padding: 0;
            }

            .cutting-line {
                border-top: 1px dashed var(--border-color);
                margin-top: 5mm;
            }
        }
    </style>
</head>

<body>
    <button onclick="window.print()" class="no-print print-btn">
        طباعة
    </button>

    <div class="receipt-container">
        <div class="header">
            <h1>سندباد</h1>
            <div style="font-size: 7pt;">{{ now()->format('Y/m/d H:i') }}</div>
        </div>

        <div class="divider"></div>

        <!-- Information Section -->
        <div>
            @foreach ($template->getInfos() as $key => $value)
                <div class="info-item">
                    <span class="info-label">{{ $key }}:</span>
                    <span class="info-value">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        <!-- Items Section -->
        @if ($template->getItemsWithHeaders() && count($template->getItemsWithHeaders()) > 0)
            <div class="divider"></div>

            <!-- Items List with Individual Headers -->
            @foreach ($template->getItemsWithHeaders() as $itemWithHeader)
                <div class="item-container">
                    @foreach ($itemWithHeader['item'] as $index => $value)
                        @if (is_array($value))
                            <div class="item-row">
                                <div class="nested-item-container">
                                    @foreach ($value as $nestedIndex => $nestedValue)
                                        <div class="item-row">
                                            <span class="item-label">{{ isset($itemWithHeader['headers'][$index][$nestedIndex]) ? $itemWithHeader['headers'][$index][$nestedIndex] . ':' : '' }}</span>
                                            <span class="item-value">{!! $nestedValue !!}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="item-row">
                                <span class="item-label">{{ isset($itemWithHeader['headers'][$index]) ? $itemWithHeader['headers'][$index] . ':' : '' }}</span>
                                <span class="item-value">{!! $value !!}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        @elseif ($template->getItems())
            <div class="divider"></div>

            @php
                $itemHeaders = $template->getItemHeaders() ?? [];
            @endphp

            <!-- Legacy Items List -->
            @foreach ($template->getItems() as $item)
                <div class="item-container">
                    @php
                        $itemArray = is_array($item) ? $item : [];
                    @endphp

                    @foreach ($itemArray as $index => $value)
                        @if (is_array($value))
                            <div class="item-row">
                                <div class="nested-item-container">
                                    @php
                                        $nestedItemArray = $value;
                                    @endphp

                                    @foreach ($nestedItemArray as $nestedIndex => $nestedValue)
                                        <div class="item-row">
                                            <span
                                                class="item-label">{{ isset($itemHeaders[$index][$nestedIndex]) ? $itemHeaders[$index][$nestedIndex] . ':' : '' }}</span>
                                            <span class="item-value">{!! $nestedValue !!}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="item-row">
                                <span
                                    class="item-label">{{ isset($itemHeaders[$index]) ? $itemHeaders[$index] . ':' : '' }}</span>
                                <span class="item-value">{!! $value !!}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        @endif

        <!-- Total Section -->
        @if ($template->getTotal())
            <div class="divider"></div>
            <div class="total">
                <span>المجموع الكلي:</span>
                <span>{{ number_format(ceil($template->getTotal()), 0) }}</span>
            </div>
        @endif

        <div class="divider"></div>
        <!-- Footer Info Section -->
        @if (!empty($template->getFooterInfos()))
            <div>
                @foreach ($template->getFooterInfos() as $key => $value)
                    <div class="info-item">
                        <span class="info-label">{{ $key }}:</span>
                        <span class="info-value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
            <div class="divider"></div>
        @endif
        <!-- Footer Section -->
        <div class="footer">
            @if ($template->getFooter())
                <div class="centered">
                    {!! $template->getFooter() !!}
                </div>
            @endif
        </div>
        <div class="cutting-line"></div>
    </div>
</body>

</html>
