<?php

namespace App\Filament\Pages;

use App\Models\CompanyProfile;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;

class AboutUs extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationLabel = 'Tentang Kami';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.about-us';

    public ?array $data = [];

    public function mount(): void
    {
        $profiles = CompanyProfile::pluck('value', 'key')->toArray();

        $this->form->fill([
            'sejarah' => $profiles['sejarah'] ?? '',
            'visi' => $profiles['visi'] ?? '',
            'misi' => $profiles['misi'] ?? '',
            'nilai' => $profiles['nilai'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Konten Halaman Tentang Kami')
                    ->schema([
                        RichEditor::make('sejarah')
                            ->label('Sejarah Perusahaan')
                            ->disableToolbarButtons(['link', 'blockquote', 'codeBlock', 'table', 'attachFiles'])
                            ->columnSpanFull(),

                        RichEditor::make('visi')
                            ->label('Visi')
                            ->disableToolbarButtons(['link', 'blockquote', 'codeBlock', 'table', 'attachFiles'])
                            ->columnSpanFull(),

                        RichEditor::make('misi')
                            ->label('Misi')
                            ->disableToolbarButtons(['link', 'blockquote', 'codeBlock', 'table', 'attachFiles'])
                            ->columnSpanFull(),

                        Textarea::make('nilai')
                            ->label('Nilai Perusahaan')
                            ->helperText('Pisahkan setiap nilai dengan baris baru.')
                            ->rows(6)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            CompanyProfile::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Notification::make()
            ->title('Konten halaman Tentang Kami berhasil disimpan.')
            ->success()
            ->send();
    }
}
