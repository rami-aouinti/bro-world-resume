<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\V1;

use Bro\WorldCoreBundle\Transport\Rest\Controller;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions\Anon\CountAction;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions\Anon\CreateAction;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions\Anon\DeleteAction;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions\Anon\FindAction;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions\Anon\FindOneAction;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions\Anon\IdsAction;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions\Anon\PatchAction;
use Bro\WorldCoreBundle\Transport\Rest\Traits\Actions\Anon\UpdateAction;
use App\Resume\Application\Resource\ExperienceResource;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class ExperienceController
 */
#[AsController]
#[Route(path: '/v1/experience')]
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
