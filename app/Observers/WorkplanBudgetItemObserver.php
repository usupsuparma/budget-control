<?php

namespace App\Observers;

use App\Models\WorkplanBudgetItem;

class WorkplanBudgetItemObserver
{
    /**
     * Handle the WorkplanBudgetItem "saving" event.
     *
     * @param  \App\Models\WorkplanBudgetItem  $workplanBudgetItem
     * @return void
     */
    public function saving(WorkplanBudgetItem $workplanBudgetItem)
    {
        $totalQuantity = $workplanBudgetItem->getTotalActivityQuantity();

        if (isset($workplanBudgetItem->price_final) && $workplanBudgetItem->price_final > 0) {
            $workplanBudgetItem->total = $workplanBudgetItem->price_final * $totalQuantity;
        } else {
            // If there's no final price (i.e., before verification), set total to null.
            $workplanBudgetItem->total = 0;
        }
    }
}
