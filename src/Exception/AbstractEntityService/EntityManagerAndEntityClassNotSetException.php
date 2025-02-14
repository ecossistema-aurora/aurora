<?php

declare(strict_types=1);

namespace App\Exception\AbstractEntityService;

use App\Exception\ResourceNotFoundException;

class EntityManagerAndEntityClassNotSetException extends ResourceNotFoundException
{
    protected const string RESOURCE = 'Entity Manager and Entity Class not set in AbstractEntityService.';
}
