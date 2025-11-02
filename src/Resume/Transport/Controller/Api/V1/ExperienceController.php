<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\V1;

use App\General\Transport\Rest\Controller;
use App\General\Transport\Rest\Traits\Actions\Anon\CountAction;
use App\General\Transport\Rest\Traits\Actions\Anon\CreateAction;
use App\General\Transport\Rest\Traits\Actions\Anon\DeleteAction;
use App\General\Transport\Rest\Traits\Actions\Anon\FindAction;
use App\General\Transport\Rest\Traits\Actions\Anon\FindOneAction;
use App\General\Transport\Rest\Traits\Actions\Anon\IdsAction;
use App\General\Transport\Rest\Traits\Actions\Anon\PatchAction;
use App\General\Transport\Rest\Traits\Actions\Anon\UpdateAction;
use App\Resume\Application\Resource\ExperienceResource;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/experience')]
class ExperienceController extends Controller
{
    use CountAction;
    use CreateAction;
    use DeleteAction;
    use FindAction;
    use FindOneAction;
    use IdsAction;
    use PatchAction;
    use UpdateAction;

    public function __construct(ExperienceResource $resource)
    {
        parent::__construct($resource);
    }
}
