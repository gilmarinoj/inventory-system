<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Expense::create(['description' => 'Pago de Internet', 'amount' => 50.00]);
        Expense::create(['description' => 'Compra de inventario', 'amount' => 1250.50]);
        Expense::create(['description' => 'Electricidad (Hoy)', 'amount' => 85.00, 'created_at' => now()]);
        Expense::create(['description' => 'Suministros de oficina', 'amount' => 20.00, 'created_at' => now()]);
    }
}
