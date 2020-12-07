<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Widget;
use PHPUnit\Framework\TestCase;

/**
 * Class WidgetTest
 * @package App\Tests\Unit\Entity
 */
class WidgetTest extends TestCase
{
    public function testCreateWidget()
    {
        $widget = new Widget();
        $widget->setName('name');
        $widget->setDescription('description');

        $this->assertEquals('name', $widget->getName());
        $this->assertEquals('description', $widget->getDescription());

        $this->assertTrue($widget instanceof Widget);
    }
}
