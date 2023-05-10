<?php

declare(strict_types=1);

namespace Tests\Unit\Services\MagicMethods;

use App\Services\MagicMethods\DependencyInterface;
use App\Services\MagicMethods\MagicMethods;
use PHPUnit\Framework\TestCase;

class MagicMethodsTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_construct(): void
    {
        $dependency = $this->createMock(DependencyInterface::class);
        $dependency->expects($connectSpy = $this->once())
            ->method('connect');

        new MagicMethods($dependency);

        $connectSpy->verify();
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_destruct(): void
    {
        $dependency = $this->createMock(DependencyInterface::class);
        $dependency->expects($disconnectSpy = $this->once())
            ->method('disconnect');
        $magicMethods = new MagicMethods($dependency);

        unset($magicMethods);

        $disconnectSpy->verify();
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_overloading_properties(): void
    {
        $dependency = $this->createMock(DependencyInterface::class);
        $dependency->expects($getPropertySpy = $this->once())
            ->method('getProperty');
        $dependency->expects($setPropertySpy = $this->once())
            ->method('setProperty');
        $dependency->expects($issetPropertySpy = $this->once())
            ->method('issetProperty');
        $dependency->expects($unsetPropertySpy = $this->once())
            ->method('unsetProperty');
        $magicMethods = new MagicMethods($dependency);
        $propertyName = 'something';
        $propertyValue = 1;

        $magicMethods->$propertyName;
        $magicMethods->$propertyName = $propertyValue;
        $cacheProperties = $magicMethods->getCacheProperties();
        $isset = isset($magicMethods->$propertyName);
        unset($magicMethods->$propertyName);
        $unsetCacheProperties = $magicMethods->getCacheProperties();

        $this->assertTrue($isset);
        $getPropertySpy->verify();
        $setPropertySpy->verify();
        $issetPropertySpy->verify();
        $unsetPropertySpy->verify();
        $this->assertSame([$propertyName => $propertyValue], $cacheProperties);
        $this->assertSame([], $unsetCacheProperties);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_overloading_methods(): void
    {
        $dependency = $this->createMock(DependencyInterface::class);
        $dependency->expects($delegatedMethodSpy = $this->any())
            ->method('delegatedMethod');
        $magicMethods = new MagicMethods($dependency);

        $magicMethods->delegatedMethod();
        MagicMethods::staticDelegatedMethod();

        $this->assertEquals(2, $delegatedMethodSpy->numberOfInvocations());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_clone(): void
    {
        $dependency = $this->createMock(DependencyInterface::class);
        $magicMethods = new MagicMethods($dependency);
        $magicMethods->something = 1;

        $clonedMagicMethods = clone $magicMethods;

        $this->assertNotSame($magicMethods->getDependency(), $clonedMagicMethods->getDependency());
        $this->assertNotSame($magicMethods->getCacheProperties(), $clonedMagicMethods->getCacheProperties());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_invoke_and_to_string(): void
    {
        $dependency = $this->createStub(DependencyInterface::class);
        $magicMethods = new MagicMethods($dependency);

        $invoke = $magicMethods();
        $toString = (string)$magicMethods;

        $this->assertTrue($invoke);
        $this->assertSame(MagicMethods::class, $toString);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_serialize(): void
    {
        $dependency = $this->createMock(DependencyInterface::class);
        $dependency->expects($serializeSpy = $this->once())
            ->method('serialize');
        $magicMethods = new MagicMethods($dependency);

        serialize($magicMethods);

        $serializeSpy->verify();
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_unserialize(): void
    {
        $dependency = $this->createMock(DependencyInterface::class);
        $dependency->expects($unserializeSpy = $this->once())
            ->method('unserialize');
        $magicMethods = new MagicMethods($dependency);

        $serialize = serialize($magicMethods);
        /** @var MagicMethods $unserialize */
        $unserialize = unserialize($serialize);

        $this->assertSame(['serializeProperty' => true], $unserialize->getCacheProperties());
        $unserializeSpy->verify();
    }
}
