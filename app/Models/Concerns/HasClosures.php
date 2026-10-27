<?php

namespace App\Models\Concerns;

use App\Models\Closure;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

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
     * Every closed day with its reason, expanded from the stored ranges: ['Y-m-d' => ?reason].
     */
    public function closedDays(): array
    {
        $days = [];

        foreach ($this->closures as $closure) {
            $cursor = $closure->start_date->copy();

            for ($i = 0; $i < 1000 && $cursor->lte($closure->end_date); $i++) {
                $days[$cursor->toDateString()] = $closure->reason;
                $cursor->addDay();
            }
        }

        ksort($days);

        return $days;
    }

    /**
     * Replace this record's closures with exactly the chosen days. Days that were already closed keep
     * their reason; newly added days get $newReason. Consecutive days with the same reason are stored
     * as one range.
     */
    public function syncClosureDays(array $days, ?string $newReason = null): void
    {
        $existing = $this->closedDays();
        $newReason = $newReason !== null && trim($newReason) !== '' ? trim($newReason) : null;

        $chosen = collect($days)
            ->map(fn ($day) => Carbon::parse($day)->toDateString())
            ->unique()
            ->sort()
            ->values();

        $ranges = [];

        foreach ($chosen as $day) {
            $reason = array_key_exists($day, $existing) ? $existing[$day] : $newReason;
            $last = count($ranges) - 1;

            if ($last >= 0
                && $ranges[$last]['reason'] === $reason
                && Carbon::parse($ranges[$last]['end_date'])->addDay()->toDateString() === $day) {
                $ranges[$last]['end_date'] = $day;
            } else {
                $ranges[] = ['start_date' => $day, 'end_date' => $day, 'reason' => $reason];
            }
        }

        DB::transaction(function () use ($ranges) {
            $this->closures()->delete();

            foreach ($ranges as $range) {
                $this->closures()->create($range);
            }
        });

        $this->unsetRelation('closures');
    }
}
