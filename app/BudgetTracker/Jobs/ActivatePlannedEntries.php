<?php

namespace App\BudgetTracker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\BudgetTracker\Models\Entry;
use Illuminate\Support\Facades\Log;

class ActivatePlannedEntries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->handle();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach($this->findPlannedEntries() as $entry) {
            Log::info($entry->uuid." updated planned = 0");
            $entry->planned = 0;
            $entry->updated_at = date('Y-m-d H:i:s', time());
            $entry->save();
            
            Log::info("Activated entry: ".json_encode($entry->toArray()));
        }
    }

    /**
     * find planned entries
     * @return \Illuminate\Database\Eloquent\Collection 
     */
    private function findPlannedEntries() : \Illuminate\Database\Eloquent\Collection
    {   
        return Entry::where('planned',1)->where('date_time', '<=', date('Y-m-d H:i:s',time()))->get();
    }
}
