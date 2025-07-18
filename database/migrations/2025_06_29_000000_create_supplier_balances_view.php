<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement(<<<SQL
        CREATE OR REPLACE VIEW supplier_balances AS
        SELECT
            s.id AS supplier_id,
            COALESCE((
                SELECT SUM(receipt_notes.total)
                FROM purchase_invoices
                JOIN receipt_notes ON receipt_notes.id = purchase_invoices.receipt_note_id
                WHERE purchase_invoices.supplier_id = s.id
            ), 0) AS receipt_total,
            COALESCE((
                SELECT SUM(accountant_issue_notes.paid)
                FROM purchase_invoices
                JOIN receipt_notes ON receipt_notes.id = purchase_invoices.receipt_note_id
                JOIN accountant_issue_notes ON
                    accountant_issue_notes.for_model_id = receipt_notes.id
                    AND accountant_issue_notes.for_model_type = 'App\\Models\\ReceiptNote'
                WHERE purchase_invoices.supplier_id = s.id
            ), 0) AS receipt_paid,
            COALESCE((
                SELECT SUM(issue_notes.total)
                FROM return_purchase_invoices
                JOIN issue_notes ON issue_notes.id = return_purchase_invoices.issue_note_id
                WHERE return_purchase_invoices.supplier_id = s.id
            ), 0) AS issue_total,
            COALESCE((
                SELECT SUM(accountant_receipt_notes.paid)
                FROM return_purchase_invoices
                JOIN issue_notes ON issue_notes.id = return_purchase_invoices.issue_note_id
                JOIN accountant_receipt_notes ON
                    accountant_receipt_notes.from_model_id = issue_notes.id
                    AND accountant_receipt_notes.from_model_type = 'App\\Models\\IssueNote'
                WHERE return_purchase_invoices.supplier_id = s.id
            ), 0) AS issue_paid,
            (
                COALESCE((
                    SELECT SUM(receipt_notes.total)
                    FROM purchase_invoices
                    JOIN receipt_notes ON receipt_notes.id = purchase_invoices.receipt_note_id
                    WHERE purchase_invoices.supplier_id = s.id
                ), 0)
                - COALESCE((
                    SELECT SUM(accountant_issue_notes.paid)
                    FROM purchase_invoices
                    JOIN receipt_notes ON receipt_notes.id = purchase_invoices.receipt_note_id
                    JOIN accountant_issue_notes ON
                        accountant_issue_notes.for_model_id = receipt_notes.id
                        AND accountant_issue_notes.for_model_type = 'App\\Models\\ReceiptNote'
                    WHERE purchase_invoices.supplier_id = s.id
                ), 0)
                - COALESCE((
                    SELECT SUM(issue_notes.total)
                    FROM return_purchase_invoices
                    JOIN issue_notes ON issue_notes.id = return_purchase_invoices.issue_note_id
                    WHERE return_purchase_invoices.supplier_id = s.id
                ), 0)
                + COALESCE((
                    SELECT SUM(accountant_receipt_notes.paid)
                    FROM return_purchase_invoices
                    JOIN issue_notes ON issue_notes.id = return_purchase_invoices.issue_note_id
                    JOIN accountant_receipt_notes ON
                        accountant_receipt_notes.from_model_id = issue_notes.id
                        AND accountant_receipt_notes.from_model_type = 'App\\Models\\IssueNote'
                    WHERE return_purchase_invoices.supplier_id = s.id
                ), 0)
            ) AS balance
        FROM suppliers s
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS supplier_balances');
    }
};
