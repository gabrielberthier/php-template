<?php

declare(strict_types=1);

namespace Brash\Framework\Cli\Command\Demo;

use Brash\Framework\Cli\Command\BaseController;

class TestController extends BaseController
{
    public function handle(): void
    {
        $name = $this->hasParam('user') ? $this->getParam('user') : 'World';

        $this->render(<<<HTML
            <div class="py-2">
                <div class="px-1 bg-green-600">MiniTerm</div>
                <em class="ml-1">
                  Hello, {$name}
                </em>
            </div>
        HTML);
    }
}
