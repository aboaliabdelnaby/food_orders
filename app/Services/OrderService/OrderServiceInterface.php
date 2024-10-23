<?php

namespace App\Services\OrderService;

interface OrderServiceInterface
{
    public function order(array $data): array;
}
