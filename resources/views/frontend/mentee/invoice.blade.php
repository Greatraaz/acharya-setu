@php
    $backUrl = route('mentee.plans');
    $printUrl = route('mentee.invoices.print', $invoice);
    $downloadUrl = $downloadUrl ?? route('mentee.invoices.download', $invoice);
@endphp
@include('invoices.print', [
    'invoice' => $invoice,
    'print' => $print ?? false,
    'backUrl' => $backUrl,
    'printUrl' => $printUrl,
    'downloadUrl' => $downloadUrl,
])
