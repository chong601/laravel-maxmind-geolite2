<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class LoadMaxmindDataToDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Holds the list of data to load
     */

    private $rows;

    private $class;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($rows, $class)
    {
        $this->rows = $rows;
        $this->class = $class;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // USE TRANSACTIONS.
        // We don't want "gotchas" when table locking rearing it's head on selectthis
        $datarow = $this->rows;
        $class = $this->class;
        DB::transaction(function () use ($datarow, $class)  {
            $class::insert($datarow);
        });
    }
}
