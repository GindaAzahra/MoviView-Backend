<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReviewsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected Collection $reviews;
    protected Collection $movieDetails;

    public function __construct(Collection $reviews, Collection $movieDetails)
    {
        $this->reviews      = $reviews;
        $this->movieDetails = $movieDetails;
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->reviews as $review) {
            $movieLabel = $this->movieDetails->get($review->id_movie, $review->id_movie);

            $rows->push([
                $review->id_review,
                $review->id_user,
                optional($review->user)->name,
                $movieLabel,
                $review->rating,
                $review->review,
                $review->created_at,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Review ID',
            'User ID',
            'User Name',
            'Movie',
            'Rating',
            'Review',
            'Created At',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row style
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '4F81BD'], // blue-ish header
            ],
        ]);

        // Apply thin borders to all used cells (header + data)
        $highestRow    = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range         = "A1:{$highestColumn}{$highestRow}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }
}
