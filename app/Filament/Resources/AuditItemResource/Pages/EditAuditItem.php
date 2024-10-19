<?php

namespace App\Filament\Resources\AuditItemResource\Pages;

use App\Enums\Applicability;
use App\Enums\Effectiveness;
use App\Enums\WorkflowStatus;
use App\Filament\Resources\AuditItemResource;
use App\Filament\Resources\DataRequestResource;
use App\Models\AuditItem;
use App\Models\DataRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;

class EditAuditItem extends EditRecord
{
    public static ?string $title = 'Assess Audit Item';

    //set title to Assess Audit Item
    protected static string $resource = AuditItemResource::class;

    function getRedirectUrl(): string
    {
        return route('filament.app.resources.audits.view', $this->record->audit_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Audit')
                ->icon('heroicon-m-arrow-left')
                ->url(route('filament.app.resources.audits.view', $this->record->audit_id)),
            Action::make("request_evidence")
                ->label('Request Evidence')
                ->icon('heroicon-m-document')
                ->action(function ($data) {
                    $dataRequest = new DataRequest();
                    $dataRequest->audit_item_id = $this->record->id;
                    $dataRequest->audit_id = $this->record->audit->id;
                    $dataRequest->status = WorkflowStatus::NOTSTARTED;
                    $dataRequest->created_by_id = auth()->id();
                    $dataRequest->assigned_to_id = $data['user_id'];
                    $dataRequest->details = $data['details'];
                    $dataRequest->save();

                    DataRequestResource::createResponses($dataRequest);

                })
                ->after(function () {
                    Notification::make()
                        ->title('Evidence Requested')
                        ->body('The evidence request has been submitted.')
                        ->success()
                        ->send();
                })
                ->form([
                    Forms\Components\Select::make('user_id')
                        ->label('Assigned To')
                        ->options(User::pluck('name', 'id')->toArray())
                        ->searchable(),
                    Forms\Components\Textarea::make('details')
                        ->label('Request Details')
                        ->required(),
                ])
                ->modalHeading('Request Evidence')
                ->modalSubmitActionLabel('Submit')
                ->modalCancelActionLabel('Cancel'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Item Information')
                    ->schema([
                        Placeholder::make('control_code')
                            ->label('Code')
                            ->content(fn(AuditItem $record): ?string => $record->auditable->code),
                        Placeholder::make('control_title')
                            ->label('Title')
                            ->content(fn(AuditItem $record): ?string => $record->auditable->title),
                        Placeholder::make('control_desc')
                            ->label('Description')
                            ->content(fn(AuditItem $record): HtmlString => new HtmlString(optional($record->auditable)->description ?? ''))
                            ->columnSpanFull(),
                        Placeholder::make('control_discussion')
                            ->label('Discussion')
                            ->content(fn(AuditItem $record): HtmlString => new HtmlString(optional($record->auditable)->discussion ?? ''))
                            ->columnSpanFull(),

                    ])->columns(2)->collapsible(true),

                Forms\Components\Section::make('Evaluation')
                    ->schema([
                        ToggleButtons::make('status')
                            ->label('Status')
                            ->options(WorkflowStatus::class)
                            ->default('Not Started')
                            ->grouped(),
                        ToggleButtons::make('effectiveness')
                            ->label('Effectiveness')
                            ->options(Effectiveness::class)
                            ->default('Not Effective')
                            ->grouped(),
                        ToggleButtons::make('applicability')
                            ->label('Applicability')
                            ->options(Applicability::class)
                            ->default('Applicable')
                            ->grouped(),
                        RichEditor::make('auditor_notes')
                            ->columnSpanFull()
                            ->label('Auditor Notes'),
                    ]),

                Forms\Components\Section::make('Audit Evidence')
                    ->schema([
                        //Todo: This can be replaced with a Repeater component when nested relationships are
                        // supported in Filament - potentially in v4.x. Or, maybe do a footer widget.
                        Placeholder::make('control.implementations')
                            ->hidden($this->record->audit->audit_type == 'implementations')
                            ->label('Documented Implementations')
                            ->view('tables.implementations-table', ['implementations' => $this->record->auditable->implementations])
                            ->columnSpanFull()
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Implementations that are related to this control.'),
                        Placeholder::make('data_requests')
                            ->label('Data Requests')
                            ->view('tables.data-requests-table', ['requests' => $this->record->dataRequests])
                            ->columnSpanFull()
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Data Requests that have been issued.'),
                    ])
                    ->collapsible(true)
            ]);
    }
}
