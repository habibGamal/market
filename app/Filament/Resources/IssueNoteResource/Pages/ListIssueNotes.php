<?php

namespace App\Filament\Resources\IssueNoteResource\Pages;

use App\Filament\Resources\IssueNoteResource;
use App\Models\ReturnPurchaseInvoice;
use App\Models\Waste;
use App\Services\IssueNoteServices;
use App\Models\IssueNote;
use App\Enums\IssueNoteType;
use App\Enums\InvoiceStatus;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListIssueNotes extends ListRecords
{
    protected static string $resource = IssueNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('createFromReturnPurchase')
                ->label('انشاء من مرتجع مشتريات')
                ->modalHeading('انشاء اذن صرف من مرتجع مشتريات')
                ->icon('heroicon-o-arrow-uturn-left')
                ->form([
                    Select::make('return_purchase_invoice_id')
                        ->label('مرتجع المشتريات')
                        ->options(
                            ReturnPurchaseInvoice::query()
                                ->with('supplier:id,name')
                                ->whereNull('issue_note_id')
                                ->where('status', InvoiceStatus::CLOSED)
                                ->get()
                                ->mapWithKeys(function ($invoice) {
                                    $supplierName = $invoice->supplier?->name ?? '';
                                    return [
                                        $invoice->id => $supplierName ? "{$supplierName} - #{$invoice->id}" : "#{$invoice->id}",
                                    ];
                                })
                        )
                        ->searchable()
                        ->required()
                ])
                ->action(function (array $data, IssueNoteServices $services): void {
                    $returnPurchaseInvoice = ReturnPurchaseInvoice::query()
                        ->where('id', $data['return_purchase_invoice_id'])
                        ->firstOrFail();

                    // Create new issue note
                    $issueNote = IssueNote::create([
                        'note_type' => IssueNoteType::RETURN_PURCHASES,
                        'status' => InvoiceStatus::DRAFT,
                        'total' => $returnPurchaseInvoice->total,
                        'officer_id' => auth()->id(),
                    ]);

                    // Fill issue note with return purchase invoice items
                    $services->fromReturnPurchaseInvoice($issueNote, $returnPurchaseInvoice);

                    redirect(IssueNoteResource::getUrl('edit', ['record' => $issueNote->id]));
                }),
            Actions\Action::make('createFromWaste')
                ->label('انشاء من الهالك')
                ->modalHeading('انشاء اذن صرف من الهالك')
                ->icon('heroicon-o-trash')
                ->form([
                    Select::make('waste_id')
                        ->label('الهالك')
                        ->options(
                            Waste::query()
                                ->whereNull('issue_note_id')
                                ->where('status', InvoiceStatus::CLOSED)
                                ->get()
                                ->pluck('id', 'id')
                                ->map(fn($id) => "#{$id}")
                        )
                        ->searchable()
                        ->required()
                ])
                ->action(function (array $data, IssueNoteServices $services): void {
                    $waste = Waste::query()
                        ->where('id', $data['waste_id'])
                        ->firstOrFail();
                    // Create new issue note
                    $issueNote = IssueNote::create([
                        'note_type' => IssueNoteType::WASTE,
                        'status' => InvoiceStatus::DRAFT,
                        'total' => $waste->total,
                        'officer_id' => auth()->id(),
                    ]);

                    // Fill issue note with waste items
                    $services->fromWaste($issueNote, $waste);
                }),
        ];
    }
}
