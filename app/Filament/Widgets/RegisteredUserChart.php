<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RegisteredUserChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Registered User Chart';
    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
        ];
    }

    protected function getData(): array
    {
        $query = DB::table('users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as aggregate'));

        switch ($this->filter) {
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'today':
            default:
                $query->whereDate('created_at', now());
                break;
        }

        $data = $query->groupBy('date')
            ->get()
            ->map(function ($item) {
                return (object) ['date' => $item->date, 'aggregate' => $item->aggregate];
            });

        return [
            'datasets' => [
                [
                    'label' => 'Registered Users',
                    'data' => $data->map(fn ($value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn ($value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
