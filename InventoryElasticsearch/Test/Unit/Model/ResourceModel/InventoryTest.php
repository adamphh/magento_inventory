<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryElasticsearch\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InventoryElasticsearch\Model\ResourceModel\Inventory;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Elasticsearch stock inventory
 */
class InventoryTest extends TestCase
{
    /**
     * @var Inventory
     */
    private $model;

    /**
     * @var ResourceConnection
     */
    private $resourceConnectionMock;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->model = (new ObjectManager($this))->getObject(
            Inventory::class,
            [
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * Test for `getStockStatus` using product sku and website code
     *
     * @return void
     */
    public function testGetStockStatus(): void
    {
        $websiteCode = 'base';
        $productId = 1;

        $connectionAdapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $connectionAdapterMock->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($selectMock);
        $selectMock->expects($this->atLeastOnce())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->atLeastOnce())
            ->method('where')
            ->willReturnSelf();
        $connectionAdapterMock->expects($this->exactly(2))
            ->method('fetchPairs')
            ->willReturn([$productId => '1']);

        $this->resourceConnectionMock
            ->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($connectionAdapterMock);

        $this->resourceConnectionMock
            ->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturnSelf();

        $this->model->saveRelation([$productId]);
        $this->assertSame([$productId => '1'], $this->model->getStockStatus($websiteCode));
    }

    /**
     * Test for `getStockId` by websiteCode
     *
     * @return void
     */
    public function testGetStockId(): void
    {
        $tableName = 'inventory_stock_sales_channel';
        $websiteCode = 'base';

        $connectionAdapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $selectMock->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $connectionAdapterMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $connectionAdapterMock->expects($this->once())
            ->method('fetchOne')
            ->willReturn(1);

        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionAdapterMock);

        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->willReturn($tableName);

        $this->assertSame(1, $this->model->getStockId($websiteCode));
    }

    /**
     * Test for `saveRelation` of product's ID & SKU
     *
     * @return void
     */
    public function testSaveRelation(): void
    {
        $tableName = 'catalog_product_entity';
        $productId = 1;
        $productSku = '24-MB01';

        $connectionAdapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $selectMock->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $connectionAdapterMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $connectionAdapterMock->expects($this->once())
            ->method('fetchPairs')
            ->willReturn([$productId => $productSku]);

        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionAdapterMock);

        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->willReturn($tableName);

        $this->assertSame([$productId => $productSku], $this->model->saveRelation([$productId])->getSkuRelation());
    }
}
