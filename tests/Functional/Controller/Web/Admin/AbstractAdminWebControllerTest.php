<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\Enum\FlashMessageTypeEnum;
use App\Tests\AbstractAdminWebTestCase;


class AbstractAdminWebControllerTest extends AbstractAdminWebTestCase
{
    private AbstractAdminController $controller;

    protected function setUp(): void
    {
        $this->controller = $this->getMockBuilder(AbstractAdminController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFlash'])
            ->getMock();
    }

    public function testAddFlashSuccess(): void
    {
        $message = 'Success message';

        $this->controller->expects($this->once())
            ->method('addFlash')
            ->with(FlashMessageTypeEnum::SUCCESS->value, $message);

        $this->controller->addFlashSuccess($message);
    }

    public function testAddFlashError(): void
    {
        $message = 'Error message';

        $this->controller->expects($this->once())
            ->method('addFlash')
            ->with(FlashMessageTypeEnum::ERROR->value, $message);

        $this->controller->addFlashError($message);
    }
}
