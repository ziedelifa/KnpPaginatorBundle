<?php

namespace Knp\Bundle\PaginatorBundle\Tests\DependencyInjection\Compiler;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAware;
use Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler\PaginatorAwarePass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class PaginatorAwarePassTest extends TestCase
{
    public function testCorrectPassProcess(): void
    {
        $container = new ContainerBuilder();
        $container->register('knp.paginator');
        $container->register('tag.one', PaginatorAware::class)
            ->addTag(PaginatorAwarePass::PAGINATOR_AWARE_TAG, ['paginator' => 'knp.paginator']);

        (new PaginatorAwarePass())->process($container);

        self::assertEquals(
            [['setPaginator', [new Reference('knp.paginator')]]],
            $container->getDefinition('tag.one')->getMethodCalls()
        );
    }

    public function testExceptionWrongInterface(): void
    {
        $container = new ContainerBuilder();
        $container->register('knp.paginator');
        $container->register('tag.one', 'stdClass')
            ->addTag(PaginatorAwarePass::PAGINATOR_AWARE_TAG, ['paginator' => 'knp.paginator']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "tag.one" must implement interface "Knp\\Bundle\\PaginatorBundle\\Definition\\PaginatorAwareInterface".');

        (new PaginatorAwarePass())->process($container);
    }

    public function testExceptionNoPaginator(): void
    {
        $container = new ContainerBuilder();
        $container->register('tag.one', PaginatorAware::class)
            ->addTag(PaginatorAwarePass::PAGINATOR_AWARE_TAG, ['paginator' => 'INVALID']);

        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Paginator service "INVALID" for tag "knp_paginator.injectable" on service "tag.one" could not be found.');

        (new PaginatorAwarePass())->process($container);
    }
}
