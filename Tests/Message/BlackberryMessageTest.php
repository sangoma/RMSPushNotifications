<?php

namespace RMS\PushNotifications\Tests\Message;

use RMS\PushNotifications\Device\Types,
    RMS\PushNotifications\Message\BlackberryMessage,
    RMS\PushNotifications\Message\MessageInterface;

class BlackberryMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $msg = new BlackberryMessage();
        $this->assertInstanceOf("RMS\PushNotifications\Message\MessageInterface", $msg);
        $this->assertEquals(Types::OS_BLACKBERRY, $msg->getTargetOS());
    }

    public function testDefaultBody()
    {
        $expected = null;
        $msg = new BlackberryMessage();
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testSettingBody()
    {
        $expected = "Foo";
        $msg = new BlackberryMessage();
        $msg->setMessage("Foo");
        $this->assertEquals($expected, $msg->getMessageBody());
    }
}
