<?php

namespace App\Services\Service;

use App\Models\Service;

class ServiceManagerService
{
    function listAll()
    {
        return Service::all();
    }

    function findById($id)
    {
        return Service::find($id);
    }
}
