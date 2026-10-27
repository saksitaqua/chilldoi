<?php

namespace App\Models\Concerns;

use App\Models\Closure;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasClosures
{
    public function closures(): MorphMany
    {
        return $this->morphMany(Closure::class, 'closable')->orderBy('start_date');
    }

    public function isClosedBetween(string $checkIn, string $checkOut): bool
    {
        return $this->closures()->overlappingStay($checkIn, $checkOut)->exists();
    }

    public function closureOn(CarbonInterface $day): ?Closure
    {
        return $this->closures->first(
            fn (Closure $closure) => $day->between($closure->start_date, $closure->end_date)
        );
    }

    /**
     * Replace this record's closures with the submitted rows: existing rows are updated by id,
     * new rows are created, and rows the admin removed in the form are deleted.
     */
    public function syncClosures(array $rows): void
    {
        $keep = [];

        foreach ($rows as $row) {
            $start = $row['start_date'] ?? null;

            if (! $start) {
                continue;
            }

            $payload = [
                'start_date' => $start,
                'end_date' => ($row['end_date'] ?? null) ?: $start,
                'reason' => ($row['reason'] ?? null) ?: null,
            ];

            $existing = ! empty($row['id']) ? $this->closures()->find($row['id']) : null;

            if ($existing) {
                $existing->update($payload);
                $keep[] = $existing->id;
            } else {
                $keep[] = $this->closures()->create($payload)->id;
            }
        }

        $this->closures()->whereNotIn('id', $keep)->delete();
    }
}
