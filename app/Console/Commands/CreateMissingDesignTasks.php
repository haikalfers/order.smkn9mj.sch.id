<?php

namespace App\Console\Commands;

use App\Models\DesignTask;
use App\Models\Order;
use Illuminate\Console\Command;

class CreateMissingDesignTasks extends Command
{
    protected $signature = 'tasks:create-missing-design {--order-id=}';
    protected $description = 'Create missing design tasks for orders in design_process status';

    public function handle()
    {
        $query = Order::where('status', 'design_process');
        
        if ($this->option('order-id')) {
            $query->where('id', $this->option('order-id'));
        }

        $orders = $query->get();

        foreach ($orders as $order) {
            if (!DesignTask::where('order_id', $order->id)->exists()) {
                DesignTask::create([
                    'order_id' => $order->id,
                    'status'   => 'waiting',
                ]);
                $this->info("Created design task for order {$order->order_number}");
            } else {
                $this->line("Design task already exists for order {$order->order_number}");
            }
        }

        $this->info('Done!');
    }
}
