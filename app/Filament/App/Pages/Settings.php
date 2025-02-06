<?php

namespace App\Filament\App\Pages;

use App\Models\CoreApp\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Config';

    protected static string $view = 'filament.app.pages.settings';

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Section::make('Attendance')
                        ->description('Set the pattern of the esas attendance method here')
                        ->schema([
                            Toggle::make('attendance_image_geolocation')
                                ->label('Photo and geolocation attendance method')
                                ->onIcon('heroicon-o-photo')
                                ->offIcon('heroicon-s-photo')
                                ->helperText('Photo and Geolocation Attendance Method is an attendance system that utilizes two main factors-photo (image) and geolocation-to verify and record the presence of an individual (e.g. employee or participant) at a specific location and time. By using camera technology on mobile devices and GPS, this system can ensure that a person is present at the correct time and place, and provide additional evidence that can be used to ensure the integrity of attendance data.')
                                ->required(),
                            Toggle::make('attendance_qrcode')
                                ->label('QR Code attendance method')
                                ->onIcon('heroicon-o-qr-code')
                                ->offIcon('heroicon-o-qr-code')
                                ->helperText('QR Code Attendance Method is an attendance recording system that uses QR Code (Quick Response Code) as a tool to verify and record the attendance of a person (e.g. employee or attendee). In this system, the user scans the QR Code using a device such as a smart phone or other device that can read QR Codes, and the system automatically records the time and place of their attendance. Unique QR Codes are often used to provide secure identification and ensure that the attendance process happens quickly and efficiently.')
                                ->required(),
                            Toggle::make('attendance_fingerprint')
                                ->label('Fingerprint attendance method')
                                ->onIcon('heroicon-o-finger-print')
                                ->offIcon('heroicon-o-finger-print')
                                ->helperText("The Fingerprint Time Attendance Method is an attendance recording system that uses fingerprint data as a unique identification of each individual. In this system, a fingerprint scanning device is used to verify the user's identity by comparing the scanned fingerprint with the fingerprint data that has been registered in the system. After successful identification, the system automatically records attendance time, which is then used to verify attendance and manage attendance data.")
                                ->required()
                        ])
                ])
            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        $user = Auth::user();
        // Mengambil data Setting atau memberikan nilai default jika tidak ada
        $setting = Setting::where('company_id', $user->company_id)->first();

        // Cek jika data setting ada atau tidak
        $this->data = $setting ? [
            "company_id" => $setting->company_id ?? null,
            "attendance_image_geolocation" => $setting->attendance_image_geolocation ?? false,
            "attendance_qrcode" => $setting->attendance_qrcode ?? false,
            "attendance_fingerprint" => $setting->attendance_fingerprint ?? false,
        ] : [
            "company_id" => $user->company_id ?? null,
            "attendance_image_geolocation" => false,
            "attendance_qrcode" => false,
            "attendance_fingerprint" => false,
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Save Settings')
                ->color('primary')
                ->submit('submit'),
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();
        $user = Auth::user();
        Setting::updateOrCreate(
            ['company_id' => $user->company_id],
            $data
        );
        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
    }
}
