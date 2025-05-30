<?php

namespace App\Filament\App\Resources\UserAttendanceResource\Pages;

use App\Filament\App\Forms\Attendance\FormAttendance;
use App\Filament\App\Forms\FormConfig;
use App\Filament\App\Resources\UserAttendanceResource;
use App\Models\CoreApp\Departement;
use App\Repositories\Interfaces\AdministrationApp\AttendanceInterface;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms\Components\DatePicker;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageUserAttendances extends ManageRecords
{
    protected static string $resource = UserAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(auth()->user()->hasAnyRole(['super_admin']) ? true : false),
            Actions\Action::make('presence')
                ->visible(auth()->user()->hasAnyRole(['super_admin']) ? true : false)
                ->outlined()
                ->form([
                    Section::make(FormAttendance::presence())
                        ->columns(FormConfig::columns(1, 2, 3, 3))
                ])
                ->action(function (array $data) {
                    $process = app(AttendanceInterface::class);
                    if ($data['type'] === 'in') {
                        return $process->presence_in($data);
                    } else {
                        return $process->presence_out($data);
                    }
                }),
            Actions\Action::make('report')
                ->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false)
                ->outlined()
                ->form([
                    Section::make()->schema([
                        DatePicker::make('start')->required(),
                        DatePicker::make('end')->required(),
                        Select::make('departement')
                            ->multiple()
                            ->options(Departement::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])->columns(2)
                ])
                ->action(function (array $data) {
                    $process = app(AttendanceInterface::class);
                    $data = $process->report($data['start'], $data['end'], $data['departement']);
                    // Buat Spreadsheet baru
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    // Set Header
                    $headers = array_keys((array) $data[0]); // Ambil key dari array pertama
                    $columnIndex = 'A';

                    foreach ($headers as $header) {
                        $sheet->setCellValue($columnIndex . '1', strtoupper($header));
                        $columnIndex++;
                    }

                    // Isi Data
                    $rowNumber = 2;
                    foreach ($data as $row) {
                        $columnIndex = 'A';
                        foreach ((array) $row as $value) {
                            $sheet->setCellValue($columnIndex . $rowNumber, $value);
                            $columnIndex++;
                        }
                        $rowNumber++;
                    }

                    // Simpan sebagai file Excel dan kirim ke browser
                    $writer = new Xlsx($spreadsheet);
                    $fileName = "attendance_report.xlsx";

                    return new StreamedResponse(function () use ($writer) {
                        $writer->save('php://output');
                    }, 200, [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'Content-Disposition' => "attachment; filename={$fileName}",
                        'Cache-Control' => 'max-age=0',
                    ]);
                }),
        ];
    }
}
