<?php
namespace Corley\MaintenanceBundle\Test\Listener;

use Symfony\Component\HttpFoundation\Request;
use Corley\MaintenanceBundle\Listener\SoftLockListener;

class SoftLockListenerTest extends \PHPUnit_Framework_TestCase
{
    private $event;
    private $requestStack;

    public function setUp()
    {
        $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->requestStack = $this->getMock(
            'Symfony\\Component\\HttpFoundation\\RequestStack',
            array('getCurrentRequest')
        );
    }

    public function testNotUnderMaintenance()
    {
        $this->requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->will($this->returnValue(Request::create('/')));

        $listener = new SoftLockListener(__FILE__, __FILE__ . '.lock');
        $listener->setRequestStack($this->requestStack);

        $listener->onKernelRequest($this->event);

        $this->assertNull($this->event->getResponse());
        $this->assertFalse($this->event->isPropagationStopped());
    }

    public function testUnderMaintenance()
    {
        $this->requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->will($this->returnValue(Request::create('/')));

        $listener = new SoftLockListener(__FILE__, __FILE__);
        $listener->setRequestStack($this->requestStack);

        $listener->onKernelRequest($this->event);

        $this->assertNotNull($this->event->getResponse());
        $this->assertTrue($this->event->isPropagationStopped());
    }

    public function testNotUnderMaintenanceWhitePaths()
    {
        $this->requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->will($this->returnValue(Request::create('/_profiler')));

        $listener = new SoftLockListener(__FILE__, __FILE__);
        $listener->setRequestStack($this->requestStack);

        $listener->onKernelRequest($this->event);

        $this->assertNull($this->event->getResponse());
        $this->assertFalse($this->event->isPropagationStopped());
    }
}

