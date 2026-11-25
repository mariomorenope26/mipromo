<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public static function issueForOrder(Order $order, string $documentType = 'boleta', ?string $legalName = null): Invoice
    {
        if ($order->invoice) {
            return $order->invoice;
        }

        return DB::transaction(function () use ($order, $documentType, $legalName): Invoice {
            $type = $documentType === 'factura' ? 'factura' : 'boleta';
            $series = $type === 'factura' ? 'F001' : 'B001';
            $nextCorrelative = ((int) Invoice::where('series', $series)->max('correlative')) + 1;

            $total = round((float) $order->total, 2);
            $subtotalGravado = round($total / 1.18, 2);
            $igv = round($total - $subtotalGravado, 2);

            $docNumber = preg_replace('/\D/', '', (string) $order->customer_dni_ruc) ?: '00000000';
            $docType = $type === 'factura' || strlen($docNumber) === 11 ? 'RUC' : 'DNI';

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'document_type' => $type,
                'series' => $series,
                'correlative' => $nextCorrelative,
                'customer_doc_type' => $docType,
                'customer_doc_number' => $docNumber,
                'customer_legal_name' => $legalName ?: $order->customer_name,
                'subtotal_gravado' => $subtotalGravado,
                'igv' => $igv,
                'total' => $total,
                'sunat_status' => 'accepted',
                'sunat_hash' => hash('sha256', "{$series}-{$nextCorrelative}|{$order->order_number}|{$total}"),
                'issued_at' => now(),
            ]);

            AuditLogger::log('invoice.issued', "Comprobante {$invoice->formattedNumber()} emitido para pedido {$order->order_number}", $invoice);

            return $invoice;
        });
    }

    public static function issueCreditNote(Order $order, string $reason = 'Devolución o anulación'): Invoice
    {
        $existing = $order->invoices()->where('document_type', 'nota_credito')->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($order, $reason): Invoice {
            $series = 'NC01';
            $nextCorrelative = ((int) Invoice::where('series', $series)->max('correlative')) + 1;
            $total = round((float) $order->total, 2);
            $subtotalGravado = round($total / 1.18, 2);
            $igv = round($total - $subtotalGravado, 2);
            $docNumber = preg_replace('/\D/', '', (string) $order->customer_dni_ruc) ?: '00000000';

            $note = Invoice::create([
                'order_id' => $order->id,
                'document_type' => 'nota_credito',
                'series' => $series,
                'correlative' => $nextCorrelative,
                'customer_doc_type' => strlen($docNumber) === 11 ? 'RUC' : 'DNI',
                'customer_doc_number' => $docNumber,
                'customer_legal_name' => $order->customer_name,
                'subtotal_gravado' => $subtotalGravado,
                'igv' => $igv,
                'total' => $total,
                'sunat_status' => 'accepted',
                'sunat_hash' => hash('sha256', "{$series}-{$nextCorrelative}|{$order->order_number}|{$reason}"),
                'issued_at' => now(),
            ]);

            AuditLogger::log('invoice.credit_note_issued', "Nota de Crédito {$note->formattedNumber()} emitida: {$reason}", $note);

            return $note;
        });
    }
}
