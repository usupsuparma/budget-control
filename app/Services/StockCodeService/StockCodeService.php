<?php

namespace App\Services\StockCodeService;

interface StockCodeService
{
    public function getDataTable();
    public function store(array $data);
    public function findById($id);
    public function update($id, array $data);
    public function delete($id);
}
